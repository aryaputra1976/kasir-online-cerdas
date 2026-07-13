<?php

use App\Models\OnlineOrder;
use App\Models\OperationalNotificationRead;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function integrityUser(): User
{
    return User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);
}

function integrityProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Produk Integrity ' . str()->upper(str()->random(6)),
        'slug' => 'produk-integrity-' . str()->lower(str()->random(8)),
        'sku' => 'INT-' . str()->upper(str()->random(8)),
        'barcode' => 'BC' . str()->random(8),
        'purchase_price' => 400,
        'selling_price' => 1000,
        'stock' => 10,
        'minimum_stock' => 2,
        'unit' => 'pcs',
        'is_active' => true,
    ], $attributes));
}

function integritySale(array $attributes = []): Sale
{
    return Sale::create(array_merge([
        'invoice_no' => 'INV-INT-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Integrity',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 1000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ], $attributes));
}

function integrityOrder(array $attributes = []): OnlineOrder
{
    return OnlineOrder::create(array_merge([
        'order_no' => 'ONL-INT-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Online',
        'customer_phone' => '08123456789',
        'customer_email' => 'online@example.test',
        'customer_address' => 'Alamat Online',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
    ], $attributes));
}

it('enforces core unique constraints for invoices and online order identifiers', function () {
    integritySale(['invoice_no' => 'INV-DUPLICATE']);
    expect(fn () => integritySale(['invoice_no' => 'INV-DUPLICATE']))->toThrow(QueryException::class);

    integrityOrder(['order_no' => 'ONL-DUPLICATE', 'tracking_token' => 'token-a']);
    expect(fn () => integrityOrder(['order_no' => 'ONL-DUPLICATE', 'tracking_token' => 'token-b']))->toThrow(QueryException::class);

    integrityOrder(['order_no' => 'ONL-UNIQUE', 'tracking_token' => 'token-duplicate']);
    expect(fn () => integrityOrder(['order_no' => 'ONL-OTHER', 'tracking_token' => 'token-duplicate']))->toThrow(QueryException::class);
});

it('keeps sqlite migrations runnable and creates query indexes', function () {
    expect(DB::getDriverName())->toBe('sqlite')
        ->and(collect(DB::select("PRAGMA index_list('sales')"))->pluck('name'))->toContain('sales_status_sale_date_payment_idx')
        ->and(collect(DB::select("PRAGMA index_list('online_orders')"))->pluck('name'))->toContain('online_orders_status_payment_completed_idx')
        ->and(collect(DB::select("PRAGMA index_list('sale_items')"))->pluck('name'))->toContain('sale_items_product_sale_idx');
});

it('preserves transaction history when product or user records are deleted', function () {
    if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
        $this->markTestSkipped('StockMovement product_id nullOnDelete migration is only applied on MySQL/MariaDB; SQLite keeps the legacy cascade FK for fast tests.');
    }

    $user = integrityUser();
    $product = integrityProduct();
    $sale = integritySale(['created_by' => $user->id]);

    $saleItem = SaleItem::create([
        'sale_id' => $sale->id,
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 1000,
        'purchase_price' => 400,
        'subtotal_amount' => 1000,
    ]);

    $movement = StockMovement::create([
        'product_id' => $product->id,
        'created_by' => $user->id,
        'movement_type' => StockMovement::TYPE_OUT,
        'quantity_change' => -1,
        'stock_before' => 10,
        'stock_after' => 9,
        'movement_date' => now(),
    ]);

    $product->delete();
    $user->delete();

    expect(Sale::find($sale->id))->not->toBeNull()
        ->and(SaleItem::find($saleItem->id))->not->toBeNull()
        ->and(StockMovement::find($movement->id))->not->toBeNull()
        ->and(SaleItem::find($saleItem->id)->product_id)->toBeNull()
        ->and(StockMovement::find($movement->id)->product_id)->toBeNull()
        ->and(Sale::find($sale->id)->created_by)->toBeNull()
        ->and(StockMovement::find($movement->id)->created_by)->toBeNull();
});

it('cascades operational notification read state when a user is deleted', function () {
    $user = integrityUser();

    OperationalNotificationRead::create([
        'user_id' => $user->id,
        'notification_key' => 'new_online_orders',
        'last_read_at' => now(),
    ]);

    $user->delete();

    expect(OperationalNotificationRead::count())->toBe(0);
});

it('detects integrity anomalies without changing data', function () {
    $product = integrityProduct(['stock' => -2]);

    integrityOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => null,
    ]);

    DB::table('stock_movements')->insert([
        'product_id' => $product->id,
        'movement_type' => StockMovement::TYPE_ADJUSTMENT,
        'quantity_change' => 5,
        'stock_before' => 1,
        'stock_after' => 9,
        'movement_date' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $exitCode = Artisan::call('app:audit-data-integrity');
    $output = Artisan::output();

    expect($exitCode)->toBe(1)
        ->and($output)->toContain('stok produk negatif')
        ->and($output)->toContain('mutasi stok chain invalid')
        ->and($output)->toContain('completed paid order tanpa sale')
        ->and(Product::find($product->id)->stock)->toBe(-2);
});

it('documents that db check constraint assertions depend on driver support', function () {
    if (DB::getDriverName() === 'sqlite') {
        $this->markTestSkipped('SQLite migration path skips ALTER TABLE ADD CHECK constraints; MySQL/MariaDB/pgsql/sqlsrv enforce them.');
    }

    expect(fn () => SaleItem::create([
        'sale_id' => integritySale()->id,
        'product_name' => 'Invalid',
        'quantity' => 0,
        'unit_price' => -1,
        'subtotal_amount' => -1,
    ]))->toThrow(QueryException::class);
});
