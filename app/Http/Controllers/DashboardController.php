<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = now()->toDateString();

        $todayOmzet = Sale::query()
            ->whereDate('sale_date', $today)
            ->sum('total_amount');

        $todayTransactions = Sale::query()
            ->whereDate('sale_date', $today)
            ->count();

        $todayItemsSold = SaleItem::query()
            ->whereHas('sale', function ($query) use ($today) {
                $query->whereDate('sale_date', $today);
            })
            ->sum('quantity');

        $newOnlineOrders = 0;

        $lowStockCount = Product::query()
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $totalProducts = Product::count();

        $activeProducts = Product::where('is_active', true)->count();

        $safeStockProducts = Product::query()
            ->whereColumn('stock', '>', 'minimum_stock')
            ->count();

        $emptyStockProducts = Product::query()
            ->where('stock', '<=', 0)
            ->count();

        $latestSales = Sale::query()
            ->with('items')
            ->latest('sale_date')
            ->latest()
            ->limit(5)
            ->get();

        $lowStockProducts = Product::query()
            ->with('category')
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->orderBy('stock')
            ->orderBy('name')
            ->limit(5)
            ->get();

        $latestStockMovements = StockMovement::query()
            ->with(['product.category'])
            ->latest('movement_date')
            ->latest()
            ->limit(5)
            ->get();

        $bestProducts = SaleItem::query()
            ->with('product.category')
            ->select([
                'product_id',
                'product_name',
                'sku',
                'unit',
            ])
            ->selectRaw('SUM(quantity) as total_sold')
            ->selectRaw('SUM(subtotal_amount) as total_amount')
            ->groupBy('product_id', 'product_name', 'sku', 'unit')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $startDate = now()->subDays(6)->startOfDay();
        $endDate = now()->endOfDay();

        $salesByDate = Sale::query()
            ->selectRaw('DATE(sale_date) as sale_day')
            ->selectRaw('SUM(total_amount) as total_amount')
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

        $weeklySales = $weeklySalesRaw->map(function (array $item) use ($maxWeeklySale) {
            $percent = $item['amount'] > 0
                ? max(8, round(($item['amount'] / $maxWeeklySale) * 100))
                : 0;

            return [
                ...$item,
                'percent' => $percent,
            ];
        });

        $paymentSummaryRaw = Sale::query()
            ->select('payment_method')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('COUNT(*) as total_transaction')
            ->whereDate('sale_date', $today)
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $totalPaymentToday = max(1, (float) $paymentSummaryRaw->sum('total_amount'));

        $paymentSummary = $paymentSummaryRaw->map(function ($payment) use ($totalPaymentToday) {
            return [
                'method' => $payment->payment_method,
                'label' => $this->paymentMethodLabel($payment->payment_method),
                'amount' => (float) $payment->total_amount,
                'total_transaction' => (int) $payment->total_transaction,
                'percent' => round(((float) $payment->total_amount / $totalPaymentToday) * 100),
                'class' => $this->paymentProgressClass($payment->payment_method),
            ];
        });

        return view('ecommerce', compact(
            'todayOmzet',
            'todayTransactions',
            'todayItemsSold',
            'newOnlineOrders',
            'lowStockCount',
            'totalProducts',
            'activeProducts',
            'safeStockProducts',
            'emptyStockProducts',
            'latestSales',
            'lowStockProducts',
            'latestStockMovements',
            'bestProducts',
            'weeklySales',
            'paymentSummary'
        ));
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

    private function paymentMethodLabel(?string $method): string
    {
        return match ($method) {
            'CASH' => 'Tunai',
            'QRIS' => 'QRIS',
            'TRANSFER' => 'Transfer',
            'EDC' => 'EDC / Kartu',
            default => $method ?: 'Lainnya',
        };
    }

    private function paymentProgressClass(?string $method): string
    {
        return match ($method) {
            'CASH' => 'bg-success',
            'QRIS' => 'bg-primary',
            'TRANSFER' => 'bg-info',
            'EDC' => 'bg-warning',
            default => 'bg-secondary',
        };
    }
}