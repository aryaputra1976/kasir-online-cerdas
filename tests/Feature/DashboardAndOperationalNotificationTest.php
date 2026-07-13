<?php

use App\Models\OnlineOrder;
use App\Models\OperationalNotificationRead;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\User;
use App\Services\DashboardMetricsService;
use App\Services\OperationalNotificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function dashboardUser(string $role = User::ROLE_OWNER): User
{
    return User::factory()->create([
        'role' => $role,
        'is_active' => true,
    ]);
}

function dashboardSale(array $attributes = [], array $items = []): Sale
{
    $sale = Sale::create(array_merge([
        'invoice_no' => 'INV-DSH-' . str()->upper(str()->random(8)),
        'sale_date' => now(),
        'customer_name' => 'Customer Dashboard',
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
            'product_name' => 'Produk Dashboard',
            'sku' => 'DSH-001',
            'unit' => 'pcs',
            'quantity' => 1,
            'unit_price' => 1000,
            'purchase_price' => 400,
            'subtotal_amount' => 1000,
        ], $item));
    }

    return $sale->refresh();
}

function dashboardOrder(array $attributes = []): OnlineOrder
{
    return OnlineOrder::create(array_merge([
        'order_no' => 'ONL-DSH-' . str()->upper(str()->random(8)),
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
        'payment_method' => Sale::PAYMENT_QRIS,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'status' => OnlineOrder::STATUS_NEW,
        'created_at' => now(),
        'updated_at' => now(),
    ], $attributes));
}

function dashboardProduct(array $attributes = []): Product
{
    return Product::create(array_merge([
        'name' => 'Produk Dashboard ' . str()->upper(str()->random(6)),
        'slug' => 'produk-dashboard-' . str()->lower(str()->random(8)),
        'sku' => 'DSH-' . str()->upper(str()->random(8)),
        'barcode' => 'BC' . str()->random(8),
        'purchase_price' => 400,
        'selling_price' => 1000,
        'stock' => 20,
        'minimum_stock' => 2,
        'unit' => 'pcs',
        'is_active' => true,
    ], $attributes));
}

it('calculates dashboard sale metrics from completed sales using sale date', function () {
    Carbon::setTestNow('2026-07-13 10:00:00');
    $user = dashboardUser();

    dashboardSale([
        'invoice_no' => 'INV-COMPLETE-TODAY',
        'sale_date' => '2026-07-13 09:00:00',
        'total_amount' => 5000,
        'payment_method' => Sale::PAYMENT_QRIS,
    ], [
        ['quantity' => 2, 'subtotal_amount' => 5000],
    ]);

    dashboardSale([
        'invoice_no' => 'INV-PENDING-TODAY',
        'sale_date' => '2026-07-13 08:00:00',
        'status' => 'PENDING',
        'total_amount' => 9000,
    ], [
        ['quantity' => 9, 'subtotal_amount' => 9000],
    ]);

    dashboardSale([
        'invoice_no' => 'INV-COMPLETE-MONTH',
        'sale_date' => '2026-07-01 12:00:00',
        'total_amount' => 3000,
    ]);

    dashboardSale([
        'invoice_no' => 'INV-CREATED-TODAY-OLD-SALE',
        'sale_date' => '2026-06-30 12:00:00',
        'created_at' => '2026-07-13 12:00:00',
        'total_amount' => 7000,
    ]);

    $response = $this->actingAs($user)->get(route('dashboard'))->assertOk();

    $response
        ->assertViewHas('todayOmzet', fn ($value) => (float) $value === 5000.0)
        ->assertViewHas('monthOmzet', fn ($value) => (float) $value === 8000.0)
        ->assertViewHas('todayTransactions', 1)
        ->assertViewHas('todayItemsSold', 2)
        ->assertViewHas('paymentSummary', fn ($summary) => $summary->count() === 1 && $summary->first()['method'] === Sale::PAYMENT_QRIS)
        ->assertViewHas('latestSales', fn ($sales) => $sales->pluck('invoice_no')->contains('INV-COMPLETE-TODAY') && ! $sales->pluck('invoice_no')->contains('INV-PENDING-TODAY'))
        ->assertViewHas('weeklySales', fn ($weekly) => (float) $weekly->last()['amount'] === 5000.0);

    Carbon::setTestNow();
});

it('separates online order value from completed online revenue and counts confirmed orders', function () {
    Carbon::setTestNow('2026-07-13 10:00:00');
    $user = dashboardUser();

    dashboardOrder([
        'status' => OnlineOrder::STATUS_CONFIRMED,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'total_amount' => 1000,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'completed_at' => '2026-07-13 09:00:00',
        'total_amount' => 2000,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
        'completed_at' => '2026-07-13 09:30:00',
        'total_amount' => 3000,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_CANCELLED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'cancelled_at' => '2026-07-13 09:45:00',
        'total_amount' => 4000,
    ]);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Dikonfirmasi')
        ->assertViewHas('todayOnlineOrders', 4)
        ->assertViewHas('todayOnlineOrderValue', fn ($value) => (float) $value === 10000.0)
        ->assertViewHas('todayCompletedOnlineRevenue', fn ($value) => (float) $value === 2000.0)
        ->assertViewHas('todayCancelledOrderValue', fn ($value) => (float) $value === 4000.0)
        ->assertViewHas('confirmedOnlineOrders', 1)
        ->assertViewHas('onlinePaymentSummary', function ($summary) {
            $qris = $summary->firstWhere('method', Sale::PAYMENT_QRIS);

            return $qris['total_order'] === 4
                && (float) $qris['paid_value'] === 6000.0
                && $qris['completed_count'] === 2;
        });

    Carbon::setTestNow();
});

it('keeps dashboard best products grouped by product id even when snapshots changed', function () {
    Carbon::setTestNow('2026-07-13 10:00:00');
    $product = dashboardProduct(['name' => 'Kopi Saat Ini', 'sku' => 'KOPI-NOW']);

    dashboardSale(['sale_date' => '2026-07-13 09:00:00'], [
        [
            'product_id' => $product->id,
            'product_name' => 'Kopi Snapshot Lama',
            'sku' => 'KOPI-OLD',
            'quantity' => 1,
            'subtotal_amount' => 1000,
        ],
    ]);

    dashboardSale(['sale_date' => '2026-07-13 09:30:00'], [
        [
            'product_id' => $product->id,
            'product_name' => 'Kopi Snapshot Baru',
            'sku' => 'KOPI-NEW',
            'quantity' => 2,
            'subtotal_amount' => 2000,
        ],
    ]);

    $data = app(DashboardMetricsService::class)->data();

    expect($data['bestProducts'])->toHaveCount(1)
        ->and((int) $data['bestProducts']->first()->total_sold)->toBe(3)
        ->and((float) $data['bestProducts']->first()->total_amount)->toBe(3000.0);

    Carbon::setTestNow();
});

it('manages operational notification unread state separately from action required items', function () {
    Carbon::setTestNow('2026-07-13 10:00:00');
    $owner = dashboardUser(User::ROLE_OWNER);

    $order = dashboardOrder([
        'status' => OnlineOrder::STATUS_NEW,
        'updated_at' => '2026-07-13 09:00:00',
    ]);

    $service = app(OperationalNotificationService::class);

    $data = $service->dataFor($owner);
    expect($data['unread_count'])->toBe(1)
        ->and($data['action_required_count'])->toBe(1)
        ->and($data['notifications']->firstWhere('key', OperationalNotificationService::NEW_ONLINE_ORDERS)['unread'])->toBeTrue();

    $this->actingAs($owner)
        ->post(route('operational-notifications.open', OperationalNotificationService::NEW_ONLINE_ORDERS))
        ->assertRedirect(route('online-orders.index'));

    $afterRead = $service->dataFor($owner->refresh());
    expect($afterRead['unread_count'])->toBe(0)
        ->and($afterRead['action_required_count'])->toBe(1)
        ->and($afterRead['notifications']->firstWhere('key', OperationalNotificationService::NEW_ONLINE_ORDERS))->not->toBeNull();

    Carbon::setTestNow('2026-07-13 10:10:00');
    $order->forceFill(['updated_at' => '2026-07-13 10:10:00'])->save();

    expect($service->dataFor($owner->refresh())['unread_count'])->toBe(1);

    Carbon::setTestNow();
});

it('marks all operational notifications read per user only', function () {
    Carbon::setTestNow('2026-07-13 10:00:00');
    $owner = dashboardUser(User::ROLE_OWNER);
    $otherOwner = dashboardUser(User::ROLE_OWNER);

    dashboardOrder(['status' => OnlineOrder::STATUS_NEW]);
    dashboardOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => null,
    ]);

    $this->actingAs($owner)
        ->post(route('operational-notifications.mark-all'))
        ->assertRedirect();

    $service = app(OperationalNotificationService::class);

    expect($service->dataFor($owner->refresh())['unread_count'])->toBe(0)
        ->and($service->dataFor($owner)['action_required_count'])->toBe(2)
        ->and($service->dataFor($otherOwner)['unread_count'])->toBe(2)
        ->and(OperationalNotificationRead::where('user_id', $owner->id)->count())->toBeGreaterThanOrEqual(2);

    Carbon::setTestNow();
});

it('filters operational notifications by role and action definitions', function () {
    $owner = dashboardUser(User::ROLE_OWNER);
    $admin = dashboardUser(User::ROLE_ADMIN);
    $kasir = dashboardUser(User::ROLE_KASIR);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_NEW,
        'payment_status' => OnlineOrder::PAYMENT_UNPAID,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_NEW,
        'payment_status' => OnlineOrder::PAYMENT_WAITING_CONFIRMATION,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_COMPLETED,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => null,
    ]);

    dashboardOrder([
        'status' => OnlineOrder::STATUS_NEW,
        'payment_status' => OnlineOrder::PAYMENT_PAID,
        'sale_id' => null,
    ]);

    dashboardProduct(['stock' => 1, 'minimum_stock' => 2]);
    dashboardProduct(['stock' => 0, 'minimum_stock' => 2]);

    $service = app(OperationalNotificationService::class);
    $ownerKeys = $service->dataFor($owner)['notifications']->pluck('key');
    $adminKeys = $service->dataFor($admin)['notifications']->pluck('key');
    $kasirKeys = $service->dataFor($kasir)['notifications']->pluck('key');

    expect($ownerKeys)->toContain(OperationalNotificationService::WAITING_PAYMENT_CONFIRMATIONS)
        ->and($adminKeys)->toContain(OperationalNotificationService::COMPLETED_NOT_CONVERTED)
        ->and($kasirKeys->all())->toBe([OperationalNotificationService::NEW_ONLINE_ORDERS])
        ->and($service->dataFor($owner)['notifications']->firstWhere('key', OperationalNotificationService::COMPLETED_NOT_CONVERTED)['count'])->toBe(1)
        ->and($service->dataFor($owner)['notifications']->firstWhere('key', OperationalNotificationService::LOW_STOCK)['count'])->toBe(1)
        ->and($service->dataFor($owner)['notifications']->firstWhere('key', OperationalNotificationService::EMPTY_STOCK)['count'])->toBe(1);
});

it('keeps header blade free from direct model queries', function () {
    $header = file_get_contents(resource_path('views/partials/header.blade.php'));

    expect($header)->not->toContain('::query(')
        ->and($header)->not->toContain('\\App\\Models\\OnlineOrder::')
        ->and($header)->not->toContain('\\App\\Models\\Product::');
});
