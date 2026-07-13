<?php

use App\Models\OnlineOrder;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Testing\TestResponse;

uses(RefreshDatabase::class);

function onlineOrderReportUser(): User
{
    return User::factory()->create([
        'role' => User::ROLE_OWNER,
        'is_active' => true,
    ]);
}

function onlineOrderReportOrder(array $attributes = []): OnlineOrder
{
    $timestamps = array_intersect_key($attributes, array_flip(['created_at', 'updated_at']));
    $attributes = array_diff_key($attributes, $timestamps);

    $order = OnlineOrder::create(array_merge([
        'order_no' => 'ONL-RPT-' . str()->upper(str()->random(8)),
        'tracking_token' => str()->random(40),
        'customer_name' => 'Customer Online',
        'customer_phone' => '08123456789',
        'customer_email' => 'customer@example.test',
        'customer_address' => 'Alamat Customer',
        'subtotal_amount' => 1000,
        'discount_amount' => 0,
        'tax_amount' => 0,
        'shipping_amount' => 0,
        'total_amount' => 1000,
        'payment_method' => Sale::PAYMENT_CASH,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
        'note' => 'Catatan order',
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));

    if ($timestamps !== []) {
        $order->forceFill($timestamps)->save();
    }

    return $order->refresh();
}

function onlineOrderReportSale(array $attributes = []): Sale
{
    return Sale::create(array_merge([
        'invoice_no' => 'INV-ONL-RPT-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Online',
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

function onlineOrderReportCsvRows(TestResponse $response): array
{
    $content = preg_replace('/^\xEF\xBB\xBF/', '', $response->streamedContent());

    return array_map(
        fn (string $row) => str_getcsv($row, ';'),
        array_filter(explode("\n", trim($content)))
    );
}

it('calculates online order report metrics using final statuses', function () {
    $user = onlineOrderReportUser();
    $sale = onlineOrderReportSale(['invoice_no' => 'INV-CONVERTED']);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'total_amount' => 1000,
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => $sale->id,
        'stock_deducted_at' => now(),
        'total_amount' => 2000,
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'stock_deducted_at' => now(),
        'total_amount' => 3000,
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_CANCELLED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'total_amount' => 4000,
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_NEW,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'total_amount' => 5000,
    ]);

    $this->actingAs($user)
        ->get(route('reports.online-orders.index'))
        ->assertOk()
        ->assertSee('Tanggal Order Dibuat')
        ->assertViewHas('summary', function (array $summary) {
            return $summary['total_orders'] === 5
                && (float) $summary['total_order_value'] === 15000.0
                && (float) $summary['paid_order_value'] === 5000.0
                && (float) $summary['completed_revenue'] === 5000.0
                && (float) $summary['cancelled_value'] === 4000.0
                && $summary['entered_sales'] === 1
                && $summary['conversion_anomalies'] === 1;
        })
        ->assertViewHas('statusSummary', fn (array $summary) => $summary[OnlineOrder::STATUS_CONFIRMED]['count'] === 1);
});

it('rejects invalid online order report filters', function () {
    $user = onlineOrderReportUser();

    $this->actingAs($user)
        ->from(route('reports.online-orders.index'))
        ->get(route('reports.online-orders.index', ['status' => 'LEGACY']))
        ->assertRedirect(route('reports.online-orders.index'))
        ->assertSessionHasErrors('status');

    $this->actingAs($user)
        ->from(route('reports.online-orders.index'))
        ->get(route('reports.online-orders.index', [
            'start_date' => '2026-07-10',
            'end_date' => '2026-07-09',
        ]))
        ->assertRedirect(route('reports.online-orders.index'))
        ->assertSessionHasErrors('end_date');
});

it('filters by created at and searches sale invoice without per-row sale lookup', function () {
    $user = onlineOrderReportUser();
    $sale = onlineOrderReportSale(['invoice_no' => 'INV-SEARCH-JOIN']);
    $reportDate = '2026-07-10';

    $included = onlineOrderReportOrder([
        'order_no' => 'ONL-INCLUDED',
        'sale_id' => $sale->id,
        'created_at' => $reportDate . ' 10:00:00',
        'updated_at' => $reportDate . ' 10:00:00',
    ]);

    onlineOrderReportOrder([
        'order_no' => 'ONL-OUTSIDE',
        'created_at' => now()->subDays(10),
        'updated_at' => now()->subDays(10),
    ]);

    $saleLookupQueries = 0;
    DB::listen(function ($query) use (&$saleLookupQueries) {
        if (str_contains(strtolower($query->sql), 'from "sales"')
            || str_contains(strtolower($query->sql), 'from `sales`')) {
            $saleLookupQueries++;
        }
    });

    $this->actingAs($user)
        ->get(route('reports.online-orders.index', [
            'start_date' => $reportDate,
            'end_date' => $reportDate,
            'search' => 'INV-SEARCH-JOIN',
        ]))
        ->assertOk()
        ->assertSee($included->order_no)
        ->assertDontSee('ONL-OUTSIDE');

    expect($saleLookupQueries)->toBe(0);
});

it('separates payment recap all values from paid values', function () {
    $user = onlineOrderReportUser();

    onlineOrderReportOrder([
        'payment_method' => Sale::PAYMENT_QRIS,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'status' => OnlineOrder::STATUS_COMPLETED,
        'total_amount' => 7000,
    ]);

    onlineOrderReportOrder([
        'payment_method' => Sale::PAYMENT_QRIS,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
        'total_amount' => 3000,
    ]);

    $this->actingAs($user)
        ->get(route('reports.online-orders.index'))
        ->assertOk()
        ->assertViewHas('paymentRecap', function (array $recap) {
            $qris = collect($recap)->firstWhere('method', Sale::PAYMENT_QRIS);

            return $qris['orders_count'] === 2
                && $qris['paid_count'] === 1
                && (float) $qris['all_value'] === 10000.0
                && (float) $qris['paid_value'] === 7000.0
                && $qris['completed_count'] === 1;
        });
});

it('exports final columns safely without exposing tracking token', function () {
    $user = onlineOrderReportUser();

    onlineOrderReportOrder([
        'order_no' => '=ONL-001',
        'tracking_token' => 'secret-token-that-must-not-be-exported',
        'customer_name' => '+Customer',
        'customer_phone' => '-0812',
        'customer_email' => '@mail.test',
        'customer_address' => '*Alamat',
        'subtotal_amount' => 12000,
        'shipping_amount' => 1500,
        'total_amount' => 13500,
    ]);

    $rows = onlineOrderReportCsvRows(
        $this->actingAs($user)
            ->get(route('reports.online-orders.export'))
            ->assertOk()
    );

    $flatCsv = collect($rows)->flatten()->implode(';');

    expect($rows[1])->toContain('subtotal_amount')
        ->and($rows[1])->toContain('shipping_amount')
        ->and($flatCsv)->not->toContain('tracking_token')
        ->and($flatCsv)->not->toContain('secret-token-that-must-not-be-exported')
        ->and($rows[2][0])->toBe("'=ONL-001")
        ->and($rows[2][2])->toBe("'+Customer")
        ->and($rows[2][3])->toBe("'-0812")
        ->and($rows[2][4])->toBe("'@mail.test")
        ->and($rows[2][5])->toBe("'*Alamat")
        ->and($rows[2][12])->toBe('12000.00')
        ->and($rows[2][15])->toBe('1500.00');
});

it('detects status payment sale and stock consistency anomalies', function () {
    $user = onlineOrderReportUser();
    $sale = onlineOrderReportSale();

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'stock_deducted_at' => now(),
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'stock_deducted_at' => now(),
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => $sale->id,
        'stock_deducted_at' => now(),
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_PROCESSING,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'stock_deducted_at' => null,
    ]);

    onlineOrderReportOrder([
        'status' => OnlineOrder::STATUS_CANCELLED,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'stock_deducted_at' => now(),
    ]);

    $this->actingAs($user)
        ->get(route('reports.online-orders.index'))
        ->assertOk()
        ->assertViewHas('consistencyIndicators', function (array $indicators) {
            $counts = collect($indicators)->pluck('count', 'key');

            return $counts['completed_unpaid'] === 1
                && $counts['conversion_anomaly'] === 1
                && $counts['sale_status_mismatch'] === 1
                && $counts['missing_stock_deduction'] === 1
                && $counts['cancelled_with_stock'] === 1;
        });
});
