<?php

namespace App\Services;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class DashboardMetricsService
{
    public function __construct(
        private readonly BestSellingProductQuery $bestSellingProductQuery
    ) {
    }

    public function data(): array
    {
        $today = now();
        $todayStart = $today->copy()->startOfDay();
        $todayEnd = $today->copy()->endOfDay();
        $monthStart = $today->copy()->startOfMonth();
        $monthEnd = $today->copy()->endOfMonth();

        $completedSales = Sale::query()->where('status', Sale::STATUS_COMPLETED);

        $todayOmzet = (clone $completedSales)
            ->whereBetween('sale_date', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $monthOmzet = (clone $completedSales)
            ->whereBetween('sale_date', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $todayTransactions = (clone $completedSales)
            ->whereBetween('sale_date', [$todayStart, $todayEnd])
            ->count();

        $todayItemsSold = SaleItem::query()
            ->whereHas('sale', function ($query) use ($todayStart, $todayEnd) {
                $query
                    ->where('status', Sale::STATUS_COMPLETED)
                    ->whereBetween('sale_date', [$todayStart, $todayEnd]);
            })
            ->sum('quantity');

        $todayOnlineOrders = OnlineOrder::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->count();

        $todayOnlineOrderValue = OnlineOrder::query()
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $todayCompletedOnlineRevenue = OnlineOrder::query()
            ->where('status', OnlineOrder::STATUS_COMPLETED)
            ->where('payment_status', OnlineOrder::PAYMENT_PAID)
            ->whereBetween('completed_at', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $todayCancelledOrderValue = OnlineOrder::query()
            ->where('status', OnlineOrder::STATUS_CANCELLED)
            ->whereBetween('cancelled_at', [$todayStart, $todayEnd])
            ->sum('total_amount');

        $onlineOrdersNotConvertedToSale = OnlineOrder::query()
            ->where('status', OnlineOrder::STATUS_COMPLETED)
            ->where('payment_status', OnlineOrder::PAYMENT_PAID)
            ->whereNull('sale_id')
            ->count();

        $lowStockCount = Product::query()->activeLowStock()->count();
        $totalProducts = Product::count();
        $activeProducts = Product::where('is_active', true)->count();
        $safeStockProducts = Product::query()->activeSafeStock()->count();
        $emptyStockProducts = Product::query()->activeEmptyStock()->count();

        return [
            'todayOmzet' => $todayOmzet,
            'monthOmzet' => $monthOmzet,
            'todayTransactions' => $todayTransactions,
            'todayItemsSold' => $todayItemsSold,
            'todayOnlineOrders' => $todayOnlineOrders,
            'todayOnlineOrderValue' => $todayOnlineOrderValue,
            'todayCompletedOnlineRevenue' => $todayCompletedOnlineRevenue,
            'todayCancelledOrderValue' => $todayCancelledOrderValue,
            'newOnlineOrders' => $this->countOrdersByStatus(OnlineOrder::STATUS_NEW),
            'confirmedOnlineOrders' => $this->countOrdersByStatus(OnlineOrder::STATUS_CONFIRMED),
            'processingOnlineOrders' => $this->countOrdersByStatus(OnlineOrder::STATUS_PROCESSING),
            'waitingPaymentConfirmations' => $this->countOrdersByPaymentStatus(OnlineOrder::PAYMENT_WAITING_CONFIRMATION),
            'paidOnlineOrders' => $this->countOrdersByPaymentStatus(OnlineOrder::PAYMENT_PAID),
            'completedOnlineOrdersToday' => OnlineOrder::query()
                ->whereBetween('completed_at', [$todayStart, $todayEnd])
                ->where('status', OnlineOrder::STATUS_COMPLETED)
                ->count(),
            'onlineOrdersNotConvertedToSale' => $onlineOrdersNotConvertedToSale,
            'lowStockCount' => $lowStockCount,
            'totalProducts' => $totalProducts,
            'activeProducts' => $activeProducts,
            'safeStockProducts' => $safeStockProducts,
            'emptyStockProducts' => $emptyStockProducts,
            'latestSales' => $this->latestSales(),
            'latestOnlineOrders' => $this->latestOnlineOrders(),
            'lowStockProducts' => $this->lowStockProducts(),
            'latestStockMovements' => $this->latestStockMovements(),
            'bestProducts' => $this->bestProducts($monthStart, $monthEnd),
            'weeklySales' => $this->weeklySales(),
            'paymentSummary' => $this->paymentSummary($todayStart, $todayEnd),
            'onlinePaymentSummary' => $this->onlinePaymentSummary($todayStart, $todayEnd),
        ];
    }

    private function countOrdersByStatus(string $status): int
    {
        return OnlineOrder::query()->where('status', $status)->count();
    }

    private function countOrdersByPaymentStatus(string $status): int
    {
        return OnlineOrder::query()->where('payment_status', $status)->count();
    }

    private function latestSales(): Collection
    {
        return Sale::query()
            ->with('items')
            ->where('status', Sale::STATUS_COMPLETED)
            ->orderByDesc('sale_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function latestOnlineOrders(): Collection
    {
        return OnlineOrder::query()
            ->with('items')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function lowStockProducts(): Collection
    {
        return Product::query()
            ->with('category')
            ->activeLowStock()
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get();
    }

    private function latestStockMovements(): Collection
    {
        return StockMovement::query()
            ->with(['product.category'])
            ->orderByDesc('movement_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();
    }

    private function bestProducts(Carbon $monthStart, Carbon $monthEnd): Collection
    {
        $baseQuery = $this->bestSellingProductQuery->base([
            'date_from' => $monthStart->toDateString(),
            'date_to' => $monthEnd->toDateString(),
        ]);

        return $this->bestSellingProductQuery
            ->ranking($baseQuery)
            ->orderByDesc('total_sold')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();
    }

    private function weeklySales(): Collection
    {
        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $salesByDate = Sale::query()
            ->selectRaw('DATE(sale_date) as sale_day')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->groupBy('sale_day')
            ->orderBy('sale_day')
            ->get()
            ->keyBy('sale_day');

        $weeklySalesRaw = collect(range(6, 0))->map(function (int $dayOffset) use ($salesByDate) {
            $date = now()->subDays($dayOffset);
            $dateKey = $date->toDateString();

            return [
                'date' => $dateKey,
                'day' => $this->dayLabel($date),
                'amount' => (float) ($salesByDate[$dateKey]->total_amount ?? 0),
            ];
        });

        $maxWeeklySale = max(1, (float) $weeklySalesRaw->max('amount'));

        return $weeklySalesRaw->map(function (array $item) use ($maxWeeklySale) {
            return [
                ...$item,
                'percent' => $item['amount'] > 0
                    ? max(8, round(($item['amount'] / $maxWeeklySale) * 100))
                    : 0,
            ];
        });
    }

    private function paymentSummary(Carbon $todayStart, Carbon $todayEnd): Collection
    {
        $paymentSummaryRaw = Sale::query()
            ->select('payment_method')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('COUNT(*) as total_transaction')
            ->where('status', Sale::STATUS_COMPLETED)
            ->whereBetween('sale_date', [$todayStart, $todayEnd])
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $totalPaymentToday = max(1, (float) $paymentSummaryRaw->sum('total_amount'));

        return $paymentSummaryRaw->map(function ($payment) use ($totalPaymentToday) {
            return [
                'method' => $payment->payment_method,
                'label' => Sale::paymentMethodLabel($payment->payment_method),
                'amount' => (float) $payment->total_amount,
                'total_transaction' => (int) $payment->total_transaction,
                'percent' => round(((float) $payment->total_amount / $totalPaymentToday) * 100),
                'class' => Sale::paymentProgressClass($payment->payment_method),
            ];
        });
    }

    private function onlinePaymentSummary(Carbon $todayStart, Carbon $todayEnd): Collection
    {
        $summaryRaw = OnlineOrder::query()
            ->select('payment_method')
            ->selectRaw('COUNT(*) as total_order')
            ->selectRaw('SUM(CASE WHEN payment_status = ? THEN total_amount ELSE 0 END) as paid_value', [OnlineOrder::PAYMENT_PAID])
            ->selectRaw('SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as completed_count', [OnlineOrder::STATUS_COMPLETED])
            ->whereBetween('created_at', [$todayStart, $todayEnd])
            ->groupBy('payment_method')
            ->orderByDesc('paid_value')
            ->get();

        $totalPaidValue = max(1, (float) $summaryRaw->sum('paid_value'));

        return $summaryRaw->map(function ($payment) use ($totalPaidValue) {
            return [
                'method' => $payment->payment_method,
                'label' => OnlineOrder::paymentMethodLabel($payment->payment_method),
                'paid_value' => (float) $payment->paid_value,
                'total_order' => (int) $payment->total_order,
                'completed_count' => (int) $payment->completed_count,
                'percent' => round(((float) $payment->paid_value / $totalPaidValue) * 100),
                'class' => Sale::paymentProgressClass($payment->payment_method),
            ];
        });
    }

    private function dayLabel(Carbon $date): string
    {
        return match ($date->dayOfWeekIso) {
            1 => 'Sen',
            2 => 'Sel',
            3 => 'Rab',
            4 => 'Kam',
            5 => 'Jum',
            6 => 'Sab',
            7 => 'Min',
            default => $date->format('d/m'),
        };
    }
}
