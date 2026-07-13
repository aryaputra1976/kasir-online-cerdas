<?php

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function stockTestUser(string $role = User::ROLE_OWNER): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

function stockTestProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Produk Stok ' . str()->upper(str()->random(6)),
        'slug' => 'produk-stok-' . str()->lower(str()->random(8)),
        'sku' => 'STK-' . str()->upper(str()->random(8)),
        'purchase_price' => 100,
        'selling_price' => 200,
        'stock' => 10,
        'minimum_stock' => 2,
        'unit' => 'pcs',
        'is_active' => true,
    ], $attributes));
}

it('uses consistent safe low empty and inactive stock statuses', function () {
    $safe = stockTestProduct(['stock' => 5, 'minimum_stock' => 2]);
    $low = stockTestProduct(['stock' => 2, 'minimum_stock' => 2]);
    $empty = stockTestProduct(['stock' => 0, 'minimum_stock' => 2]);
    $inactive = stockTestProduct(['stock' => 1, 'minimum_stock' => 2, 'is_active' => false]);

    expect($safe->fresh()->stock_status)->toBe(Product::STOCK_STATUS_SAFE)
        ->and($low->fresh()->stock_status)->toBe(Product::STOCK_STATUS_LOW)
        ->and($empty->fresh()->stock_status)->toBe(Product::STOCK_STATUS_EMPTY)
        ->and($inactive->fresh()->stock_status)->toBe(Product::STOCK_STATUS_INACTIVE)
        ->and($empty->fresh()->is_low_stock)->toBeFalse()
        ->and($inactive->fresh()->is_low_stock)->toBeFalse();
});

it('validates stock report and stock movement filters', function () {
    $user = stockTestUser();

    $this->actingAs($user)
        ->from('/laporan/stok')
        ->get('/laporan/stok?stock_status=bad')
        ->assertRedirect('/laporan/stok')
        ->assertSessionHasErrors('stock_status');

    $this->actingAs($user)
        ->from('/stok-barang')
        ->get('/stok-barang?category_id=999999')
        ->assertRedirect('/stok-barang')
        ->assertSessionHasErrors('category_id');

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->get('/mutasi-stok?movement_type=BAD')
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('movement_type');

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->get('/mutasi-stok?date_from=2026-01-10&date_to=2026-01-01')
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('date_to');
});

it('keeps negative stock out of valuation and flags it as anomaly', function () {
    $user = stockTestUser();
    stockTestProduct(['stock' => -5, 'purchase_price' => 100, 'selling_price' => 200]);
    stockTestProduct(['stock' => 3, 'purchase_price' => 100, 'selling_price' => 200]);
    stockTestProduct(['stock' => 4, 'purchase_price' => 100, 'selling_price' => 200, 'is_active' => false]);

    $response = $this->actingAs($user)
        ->get('/laporan/stok')
        ->assertOk()
        ->assertSee('Anomali stok negatif');

    expect($response->viewData('stockCostValue'))->toBe(300.0)
        ->and($response->viewData('stockSellingValue'))->toBe(600.0)
        ->and($response->viewData('inactiveStockCostValue'))->toBe(400.0)
        ->and($response->viewData('stockAnomalyCount'))->toBe(1);
});

it('keeps stock report global summaries independent from table filters', function () {
    $user = stockTestUser();
    stockTestProduct(['name' => 'Aman Global', 'stock' => 10, 'minimum_stock' => 2]);
    stockTestProduct(['name' => 'Low Global', 'stock' => 1, 'minimum_stock' => 2]);

    $response = $this->actingAs($user)
        ->get('/laporan/stok?stock_status=low')
        ->assertOk();

    expect($response->viewData('totalProducts'))->toBe(2)
        ->and($response->viewData('filteredProducts'))->toBe(1)
        ->and($response->viewData('safeStockProducts'))->toBe(1)
        ->and($response->viewData('lowStockProducts'))->toBe(1);
});

it('rejects invalid manual stock movements and rolls back failed out movements', function () {
    $user = stockTestUser();
    $product = stockTestProduct(['stock' => 2]);

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->post('/mutasi-stok', [
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_OUT,
            'quantity' => 3,
            'movement_date' => now()->toDateString(),
        ])
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('quantity');

    expect($product->fresh()->stock)->toBe(2)
        ->and(StockMovement::count())->toBe(0);

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->post('/mutasi-stok', [
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity' => -1,
            'movement_date' => now()->toDateString(),
            'note' => 'Invalid adjustment',
        ])
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('quantity');

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->post('/mutasi-stok', [
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_ADJUSTMENT,
            'quantity' => 1,
            'movement_date' => now()->toDateString(),
        ])
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('note');

    $this->actingAs($user)
        ->from('/mutasi-stok')
        ->post('/mutasi-stok', [
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_IN,
            'quantity' => 1,
            'movement_date' => now()->addDay()->toDateString(),
        ])
        ->assertRedirect('/mutasi-stok')
        ->assertSessionHasErrors('movement_date');
});

it('stores manual stock movement audit trail and consistent chain values', function () {
    $user = stockTestUser();
    $product = stockTestProduct(['stock' => 2]);

    $this->actingAs($user)
        ->post('/mutasi-stok', [
            'product_id' => $product->id,
            'movement_type' => StockMovement::TYPE_IN,
            'quantity' => 3,
            'movement_date' => now()->toDateString(),
            'note' => 'Restok manual',
        ])
        ->assertRedirect('/mutasi-stok');

    $movement = StockMovement::firstOrFail();

    expect($movement->created_by)->toBe($user->id)
        ->and($movement->source_type)->toBe(StockMovement::SOURCE_MANUAL)
        ->and($movement->stock_before + $movement->quantity_change)->toBe($movement->stock_after)
        ->and($product->fresh()->stock)->toBe(5);
});

it('detects mismatch between last stock movement and product stock', function () {
    $user = stockTestUser();
    $product = stockTestProduct(['stock' => 5]);

    StockMovement::create([
        'product_id' => $product->id,
        'movement_type' => StockMovement::TYPE_IN,
        'quantity_change' => 4,
        'stock_before' => 0,
        'stock_after' => 4,
        'movement_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->get('/laporan/stok')
        ->assertOk();

    expect($response->viewData('stockAnomalyCount'))->toBe(1);
});

it('makes stock movement summaries follow the active filter', function () {
    $user = stockTestUser();
    $product = stockTestProduct();

    StockMovement::create([
        'product_id' => $product->id,
        'movement_type' => StockMovement::TYPE_IN,
        'quantity_change' => 5,
        'stock_before' => 0,
        'stock_after' => 5,
        'movement_date' => now()->toDateString(),
    ]);

    StockMovement::create([
        'product_id' => $product->id,
        'movement_type' => StockMovement::TYPE_OUT,
        'quantity_change' => -1,
        'stock_before' => 5,
        'stock_after' => 4,
        'movement_date' => now()->toDateString(),
    ]);

    $response = $this->actingAs($user)
        ->get('/mutasi-stok?movement_type=IN')
        ->assertOk();

    expect($response->viewData('totalMovements'))->toBe(1)
        ->and($response->viewData('stockInMovements'))->toBe(1)
        ->and($response->viewData('stockOutMovements'))->toBe(0);
});

it('stores pos stock movement source metadata', function () {
    $user = stockTestUser(User::ROLE_KASIR);
    $product = stockTestProduct(['stock' => 5, 'selling_price' => 1000]);

    $cart = [
        (string) $product->id => [
            'product_id' => $product->id,
            'name' => $product->name,
            'sku' => $product->sku,
            'unit' => $product->unit,
            'selling_price' => $product->selling_price,
            'quantity' => 1,
            'stock' => $product->stock,
        ],
    ];

    $this->actingAs($user)
        ->withSession(['pos.cart' => $cart])
        ->post('/pos/checkout', [
            'payment_method' => Sale::PAYMENT_CASH,
            'paid_amount' => 1000,
        ])
        ->assertRedirect();

    $sale = Sale::firstOrFail();
    $movement = StockMovement::firstOrFail();

    expect($movement->created_by)->toBe($user->id)
        ->and($movement->source_type)->toBe(StockMovement::SOURCE_POS)
        ->and($movement->source_id)->toBe($sale->id);
});

it('stores online order stock movement source metadata', function () {
    $user = stockTestUser(User::ROLE_KASIR);
    $product = stockTestProduct(['stock' => 5, 'selling_price' => 1000]);

    $order = OnlineOrder::create([
        'order_no' => 'ORD-STOCK-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Online',
        'customer_phone' => '08123456789',
        'customer_address' => 'Alamat',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'cod_confirmed_at' => now(),
        'cod_confirmed_by' => $user->id,
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 1000,
        'subtotal_amount' => 1000,
    ]);

    $this->actingAs($user)
        ->from("/order-online/{$order->id}")
        ->patch("/order-online/{$order->id}/process")
        ->assertRedirect("/order-online/{$order->id}");

    $movement = StockMovement::firstOrFail();

    expect($movement->created_by)->toBe($user->id)
        ->and($movement->source_type)->toBe(StockMovement::SOURCE_ONLINE_ORDER)
        ->and($movement->source_id)->toBe($order->id);
});
