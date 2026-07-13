<?php

use App\Models\Category;
use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\OnlineOrderSaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function bestProductReportUser(): User
{
    return User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);
}

function bestProductCategory(string $name = 'Kategori Laporan'): Category
{
    return Category::create([
        'name' => $name . ' ' . str()->upper(str()->random(4)),
        'slug' => str()->slug($name) . '-' . str()->lower(str()->random(6)),
        'is_active' => true,
        'sort_order' => 1,
    ]);
}

function bestProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Produk Terlaris ' . str()->upper(str()->random(6)),
        'slug' => 'produk-terlaris-' . str()->lower(str()->random(8)),
        'sku' => 'BEST-' . str()->upper(str()->random(8)),
        'barcode' => 'BAR' . str()->random(8),
        'purchase_price' => 400,
        'selling_price' => 1000,
        'stock' => 20,
        'minimum_stock' => 1,
        'unit' => 'pcs',
        'is_active' => true,
    ], $attributes));
}

function bestProductSale(array $attributes = [], array $items = []): Sale
{
    $sale = Sale::create(array_merge([
        'invoice_no' => 'INV-BEST-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Best',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_CASH,
        'paid_amount' => 1000,
        'change_amount' => 0,
        'status' => Sale::STATUS_COMPLETED,
    ], $attributes));

    foreach ($items as $item) {
        SaleItem::create(array_merge([
            'sale_id' => $sale->id,
            'product_id' => null,
            'product_name' => 'Produk Snapshot',
            'sku' => 'SNAP-BEST',
            'unit' => 'pcs',
            'quantity' => 1,
            'unit_price' => 1000,
            'purchase_price' => 400,
            'subtotal_amount' => 1000,
        ], $item));
    }

    return $sale->refresh();
}

it('counts only completed sales using sale date for the best products report', function () {
    $user = bestProductReportUser();
    $product = bestProduct(['name' => 'Produk Completed']);
    $insideDate = now()->subDay();

    bestProductSale([
        'sale_date' => $insideDate,
        'created_at' => now()->subMonths(3),
    ], [
        [
            'product_id' => $product->id,
            'product_name' => 'Produk Completed',
            'sku' => $product->sku,
            'quantity' => 2,
            'unit_price' => 1000,
            'subtotal_amount' => 2000,
        ],
    ]);

    bestProductSale([
        'sale_date' => $insideDate,
        'status' => 'PENDING',
    ], [
        [
            'product_id' => $product->id,
            'product_name' => 'Produk Pending',
            'quantity' => 9,
            'subtotal_amount' => 9000,
        ],
    ]);

    bestProductSale([
        'sale_date' => now()->subDays(10),
    ], [
        [
            'product_id' => $product->id,
            'product_name' => 'Produk Luar Periode',
            'quantity' => 7,
            'subtotal_amount' => 7000,
        ],
    ]);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris?' . http_build_query([
            'date_from' => $insideDate->toDateString(),
            'date_to' => $insideDate->toDateString(),
        ]))
        ->assertOk()
        ->assertSee('Produk Completed')
        ->assertSee('2')
        ->assertSee('Rp 2.000')
        ->assertDontSee('Produk Pending')
        ->assertDontSee('Produk Luar Periode');
});

it('rejects invalid best product report filters', function () {
    $user = bestProductReportUser();

    $this->actingAs($user)
        ->from('/laporan/produk-terlaris')
        ->get('/laporan/produk-terlaris?date_from=2026-01-10&date_to=2026-01-01')
        ->assertRedirect('/laporan/produk-terlaris')
        ->assertSessionHasErrors('date_to');

    $this->actingAs($user)
        ->from('/laporan/produk-terlaris')
        ->get('/laporan/produk-terlaris?category_id=999999')
        ->assertRedirect('/laporan/produk-terlaris')
        ->assertSessionHasErrors('category_id');
});

it('groups rows with the same product id even when snapshots changed', function () {
    $user = bestProductReportUser();
    $product = bestProduct([
        'name' => 'Produk Saat Ini',
        'sku' => 'SKU-SAAT-INI',
    ]);

    bestProductSale([], [
        [
            'product_id' => $product->id,
            'product_name' => 'Nama Lama',
            'sku' => 'SKU-LAMA',
            'quantity' => 1,
            'subtotal_amount' => 1000,
        ],
    ]);

    bestProductSale([], [
        [
            'product_id' => $product->id,
            'product_name' => 'Nama Baru Snapshot',
            'sku' => 'SKU-BARU-SNAPSHOT',
            'quantity' => 2,
            'subtotal_amount' => 2000,
        ],
    ]);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris')
        ->assertOk()
        ->assertSee('Produk Saat Ini')
        ->assertSee('Menampilkan 1 dari 1 produk')
        ->assertSee('3')
        ->assertSee('Rp 3.000');
});

it('keeps historical sale items without product id visible and counted', function () {
    $user = bestProductReportUser();

    bestProductSale([], [
        [
            'product_id' => null,
            'product_name' => 'Produk Historis Tanpa Relasi',
            'sku' => 'HIST-NULL',
            'quantity' => 4,
            'subtotal_amount' => 4000,
        ],
    ]);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris')
        ->assertOk()
        ->assertSee('Produk Historis Tanpa Relasi')
        ->assertSee('HIST-NULL')
        ->assertSee('Menampilkan 1 dari 1 produk')
        ->assertSee('4');
});

it('uses weighted average price instead of averaging item unit prices', function () {
    $user = bestProductReportUser();
    $product = bestProduct(['name' => 'Produk Weighted']);

    bestProductSale([], [
        [
            'product_id' => $product->id,
            'product_name' => 'Produk Weighted',
            'quantity' => 1,
            'unit_price' => 1000,
            'subtotal_amount' => 1000,
        ],
        [
            'product_id' => $product->id,
            'product_name' => 'Produk Weighted',
            'quantity' => 3,
            'unit_price' => 500,
            'subtotal_amount' => 1500,
        ],
    ]);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris')
        ->assertOk()
        ->assertSee('Rp 625');
});

it('searches snapshot product names and skus even when products are deleted', function () {
    $user = bestProductReportUser();
    $product = bestProduct();

    bestProductSale([], [
        [
            'product_id' => $product->id,
            'product_name' => 'Snapshot Kopi Susu',
            'sku' => 'SNAP-KOPI',
            'quantity' => 1,
            'subtotal_amount' => 1000,
        ],
    ]);

    $product->delete();

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris?q=SNAP-KOPI')
        ->assertOk()
        ->assertSee('Snapshot Kopi Susu')
        ->assertSee('SNAP-KOPI');
});

it('counts pos and converted online order sales together', function () {
    $user = bestProductReportUser();
    $product = bestProduct([
        'name' => 'Produk POS Online',
        'sku' => 'POS-ONL',
        'purchase_price' => 500,
        'selling_price' => 1000,
    ]);

    bestProductSale([
        'invoice_no' => 'POS-REPORT-001',
    ], [
        [
            'product_id' => $product->id,
            'product_name' => 'Produk POS Online',
            'sku' => 'POS-ONL',
            'quantity' => 1,
            'subtotal_amount' => 1000,
        ],
    ]);

    $order = OnlineOrder::create([
        'order_no' => 'ORD-BEST-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Online',
        'customer_phone' => '08123456789',
        'customer_address' => 'Alamat',
        'subtotal_amount' => 2000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 2000,
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'status' => OnlineOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => 'Produk POS Online',
        'sku' => 'POS-ONL',
        'unit' => 'pcs',
        'quantity' => 2,
        'unit_price' => 1000,
        'subtotal_amount' => 2000,
    ]);

    app(OnlineOrderSaleService::class)->convertCompletedOrder($order);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris')
        ->assertOk()
        ->assertSee('Produk POS Online')
        ->assertSee('3')
        ->assertSee('Rp 3.000');
});

it('applies the same filters to pagination and summary values', function () {
    $user = bestProductReportUser();
    $matchedCategory = bestProductCategory('Kategori Cocok');
    $otherCategory = bestProductCategory('Kategori Lain');

    for ($index = 1; $index <= 11; $index++) {
        $product = bestProduct([
            'category_id' => $matchedCategory->id,
            'name' => "Produk Filter {$index}",
            'slug' => "produk-filter-{$index}-" . str()->lower(str()->random(6)),
            'sku' => "FILTER-{$index}",
        ]);

        bestProductSale([], [
            [
                'product_id' => $product->id,
                'product_name' => "Produk Filter {$index}",
                'sku' => "FILTER-{$index}",
                'quantity' => 1,
                'subtotal_amount' => 1000,
            ],
        ]);
    }

    $otherProduct = bestProduct([
        'category_id' => $otherCategory->id,
        'name' => 'Produk Di Luar Filter',
    ]);

    bestProductSale([], [
        [
            'product_id' => $otherProduct->id,
            'product_name' => 'Produk Di Luar Filter',
            'quantity' => 9,
            'subtotal_amount' => 9000,
        ],
    ]);

    bestProductSale([], [
        [
            'product_id' => null,
            'product_name' => 'Produk Historis Umum',
            'sku' => 'HIST-UMUM',
            'quantity' => 5,
            'subtotal_amount' => 5000,
        ],
    ]);

    $this->actingAs($user)
        ->get('/laporan/produk-terlaris?' . http_build_query([
            'category_id' => $matchedCategory->id,
        ]))
        ->assertOk()
        ->assertSee('Menampilkan 10 dari 11 produk')
        ->assertSee('11')
        ->assertSee('Rp 11.000')
        ->assertDontSee('Produk Di Luar Filter')
        ->assertDontSee('Produk Historis Umum');
});
