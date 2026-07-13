<?php

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\OnlineOrderSaleService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function salesReportUser(): User
{
    return User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);
}

function salesReportProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Produk Laporan ' . str()->upper(str()->random(6)),
        'slug' => 'produk-laporan-' . str()->lower(str()->random(8)),
        'sku' => 'RPT-' . str()->upper(str()->random(8)),
        'barcode' => 'BC' . str()->random(8),
        'purchase_price' => 400,
        'selling_price' => 1000,
        'stock' => 20,
        'minimum_stock' => 1,
        'unit' => 'pcs',
        'is_active' => true,
    ], $attributes));
}

function salesReportSale(array $attributes = [], array $items = []): Sale
{
    $sale = Sale::create(array_merge([
        'invoice_no' => 'INV-RPT-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Laporan',
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
            'sku' => 'SNAP-001',
            'unit' => 'pcs',
            'quantity' => 1,
            'unit_price' => 1000,
            'purchase_price' => 400,
            'subtotal_amount' => 1000,
        ], $item));
    }

    return $sale->refresh();
}

function salesReportCsvRows(TestResponse $response): array
{
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());

    return array_map('str_getcsv', array_filter(explode("\n", trim($content))));
}

it('filters sales reports by sale date and completed status only', function () {
    $user = salesReportUser();
    $insideDate = now()->subDay();
    $outsideDate = now()->subDays(10);

    $insideSale = salesReportSale([
        'invoice_no' => 'INV-IN-PERIOD',
        'sale_date' => $insideDate,
        'created_at' => now()->subMonths(2),
        'total_amount' => 1500,
        'paid_amount' => 1500,
    ], [
        ['quantity' => 1, 'unit_price' => 1500, 'subtotal_amount' => 1500],
    ]);

    salesReportSale([
        'invoice_no' => 'INV-OUT-PERIOD',
        'sale_date' => $outsideDate,
        'total_amount' => 9000,
        'paid_amount' => 9000,
    ], [
        ['quantity' => 1, 'unit_price' => 9000, 'subtotal_amount' => 9000],
    ]);

    salesReportSale([
        'invoice_no' => 'INV-NOT-COMPLETED',
        'sale_date' => $insideDate,
        'status' => 'PENDING',
        'total_amount' => 8000,
        'paid_amount' => 8000,
    ], [
        ['quantity' => 1, 'unit_price' => 8000, 'subtotal_amount' => 8000],
    ]);

    $this->actingAs($user)
        ->get('/laporan/penjualan?' . http_build_query([
            'start_date' => $insideDate->toDateString(),
            'end_date' => $insideDate->toDateString(),
        ]))
        ->assertOk()
        ->assertSee($insideSale->invoice_no)
        ->assertDontSee('INV-OUT-PERIOD')
        ->assertDontSee('INV-NOT-COMPLETED')
        ->assertSee('Rp 1.500');
});

it('searches sales reports using sale item product snapshots', function () {
    $user = salesReportUser();
    $product = salesReportProduct([
        'name' => 'Nama Produk Sekarang',
        'sku' => 'SKU-SEKARANG',
    ]);

    $sale = salesReportSale([
        'invoice_no' => 'INV-SNAPSHOT-SEARCH',
        'sale_date' => now(),
    ], [
        [
            'product_id' => $product->id,
            'product_name' => 'Nama Produk Historis',
            'sku' => 'SKU-HISTORIS',
        ],
    ]);

    $product->delete();

    $this->actingAs($user)
        ->get('/laporan/penjualan?' . http_build_query([
            'start_date' => now()->toDateString(),
            'end_date' => now()->toDateString(),
            'q' => 'Historis',
        ]))
        ->assertOk()
        ->assertSee($sale->invoice_no)
        ->assertSee('Nama Produk Historis');
});

it('exports csv without duplicating transaction totals and neutralizes formula injection', function () {
    $user = salesReportUser();

    salesReportSale([
        'invoice_no' => '=2+2',
        'sale_date' => now(),
        'customer_name' => '@customer',
        'subtotal_amount' => 3000,
        'discount_amount' => 100,
        'tax_amount' => 50,
        'total_amount' => 2950,
        'paid_amount' => 2950,
    ], [
        [
            'product_name' => '*Produk A',
            'sku' => '+SKU-A',
            'quantity' => 1,
            'unit_price' => 1000,
            'purchase_price' => 400,
            'subtotal_amount' => 1000,
        ],
        [
            'product_name' => 'Produk B',
            'sku' => 'SKU-B',
            'quantity' => 2,
            'unit_price' => 1000,
            'purchase_price' => 500,
            'subtotal_amount' => 2000,
        ],
    ]);

    $rows = salesReportCsvRows(
        $this->actingAs($user)
            ->get('/laporan/penjualan/export?' . http_build_query([
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
    );

    expect($rows[1][0])->toBe("'=2+2")
        ->and($rows[1][2])->toBe("'@customer")
        ->and($rows[1][5])->toBe("'*Produk A")
        ->and($rows[1][6])->toBe("'+SKU-A")
        ->and($rows[1][12])->toBe('3000')
        ->and($rows[1][13])->toBe('100')
        ->and($rows[1][14])->toBe('50')
        ->and($rows[1][15])->toBe('2950')
        ->and($rows[2][12])->toBe('')
        ->and($rows[2][13])->toBe('')
        ->and($rows[2][14])->toBe('')
        ->and($rows[2][15])->toBe('');
});

it('stores purchase price snapshots for pos sale items', function () {
    $user = salesReportUser();
    $product = salesReportProduct([
        'purchase_price' => 450,
        'selling_price' => 1000,
        'stock' => 5,
    ]);

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

    expect(SaleItem::first()->purchase_price)->toBe('450.00');
});

it('stores purchase price snapshots when online orders are converted to sales', function () {
    $product = salesReportProduct([
        'purchase_price' => 700,
        'selling_price' => 1500,
    ]);

    $order = OnlineOrder::create([
        'order_no' => 'ORD-RPT-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Online',
        'customer_phone' => '08123456789',
        'customer_address' => 'Alamat',
        'subtotal_amount' => 1500,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1500,
        'payment_method' => Sale::PAYMENT_TRANSFER,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'status' => OnlineOrder::STATUS_COMPLETED,
        'completed_at' => now(),
    ]);

    $order->items()->create([
        'product_id' => $product->id,
        'product_name' => $product->name,
        'sku' => $product->sku,
        'unit' => $product->unit,
        'quantity' => 1,
        'unit_price' => 1500,
        'subtotal_amount' => 1500,
    ]);

    app(OnlineOrderSaleService::class)->convertCompletedOrder($order);

    expect(SaleItem::first()->purchase_price)->toBe('700.00');
});

it('keeps historical gross profit based on sale item purchase price snapshot', function () {
    $user = salesReportUser();
    $product = salesReportProduct([
        'purchase_price' => 300,
        'selling_price' => 1000,
    ]);

    salesReportSale([
        'invoice_no' => 'INV-HISTORICAL-PROFIT',
        'sale_date' => now(),
        'total_amount' => 1000,
        'paid_amount' => 1000,
    ], [
        [
            'product_id' => $product->id,
            'product_name' => $product->name,
            'sku' => $product->sku,
            'quantity' => 1,
            'unit_price' => 1000,
            'purchase_price' => 300,
            'subtotal_amount' => 1000,
        ],
    ]);

    $product->update(['purchase_price' => 900]);

    $rows = salesReportCsvRows(
        $this->actingAs($user)
            ->get('/laporan/penjualan/export?' . http_build_query([
                'start_date' => now()->toDateString(),
                'end_date' => now()->toDateString(),
            ]))
            ->assertOk()
    );

    expect($rows[1][10])->toBe('300')
        ->and($rows[1][11])->toBe('700');
});

it('rejects invalid sales report filters', function () {
    $user = salesReportUser();

    $this->actingAs($user)
        ->from('/laporan/penjualan')
        ->get('/laporan/penjualan?start_date=2026-01-10&end_date=2026-01-01')
        ->assertRedirect('/laporan/penjualan')
        ->assertSessionHasErrors('end_date');

    $this->actingAs($user)
        ->from('/laporan/penjualan')
        ->get('/laporan/penjualan?start_date=2026-01-01&end_date=2026-01-10&payment_method=BITCOIN')
        ->assertRedirect('/laporan/penjualan')
        ->assertSessionHasErrors('payment_method');
});
