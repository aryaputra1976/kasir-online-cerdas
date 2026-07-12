<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View|RedirectResponse
    {
        $user = request()->user();

        if ($user?->hasRole(User::ROLE_KASIR)) {
            return redirect()
                ->route('pos.index')
                ->with('info', 'Akun kasir diarahkan ke halaman POS.');
        }

        $today = now()->toDateString();
        $monthStart = now()->startOfMonth();
        $monthEnd = now()->endOfMonth();

        $todayOmzet = Sale::query()
            ->whereDate('sale_date', $today)
            ->sum('total_amount');

        $monthOmzet = Sale::query()
            ->whereBetween('sale_date', [$monthStart, $monthEnd])
            ->sum('total_amount');

        $todayTransactions = Sale::query()
            ->whereDate('sale_date', $today)
            ->count();

        $todayItemsSold = SaleItem::query()
            ->whereHas('sale', function ($query) use ($today) {
                $query->whereDate('sale_date', $today);
            })
            ->sum('quantity');

        $todayOnlineOrders = OnlineOrder::query()
            ->whereDate('created_at', $today)
            ->count();

        $todayOnlineOmzet = OnlineOrder::query()
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'CANCELLED')
            ->sum('total_amount');

        $newOnlineOrders = OnlineOrder::query()
            ->where('status', 'NEW')
            ->count();

        $processingOnlineOrders = OnlineOrder::query()
            ->where('status', 'PROCESSING')
            ->count();

        $waitingPaymentConfirmations = OnlineOrder::query()
            ->where('payment_status', 'WAITING_CONFIRMATION')
            ->count();

        $paidOnlineOrders = OnlineOrder::query()
            ->where('payment_status', 'PAID')
            ->count();

        $completedOnlineOrdersToday = OnlineOrder::query()
            ->whereDate('completed_at', $today)
            ->where('status', 'COMPLETED')
            ->count();

        $onlineOrdersNotConvertedToSale = OnlineOrder::query()
            ->where('status', 'COMPLETED')
            ->where('payment_status', 'PAID')
            ->whereNull('sale_id')
            ->count();

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

        $latestOnlineOrders = OnlineOrder::query()
            ->with('items')
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
            ->whereHas('sale', function ($query) use ($monthStart, $monthEnd) {
                $query->whereBetween('sale_date', [$monthStart, $monthEnd]);
            })
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

        $onlinePaymentSummaryRaw = OnlineOrder::query()
            ->select('payment_method')
            ->selectRaw('SUM(total_amount) as total_amount')
            ->selectRaw('COUNT(*) as total_order')
            ->whereDate('created_at', $today)
            ->where('status', '!=', 'CANCELLED')
            ->groupBy('payment_method')
            ->orderByDesc('total_amount')
            ->get();

        $totalOnlinePaymentToday = max(1, (float) $onlinePaymentSummaryRaw->sum('total_amount'));

        $onlinePaymentSummary = $onlinePaymentSummaryRaw->map(function ($payment) use ($totalOnlinePaymentToday) {
            return [
                'method' => $payment->payment_method,
                'label' => $this->paymentMethodLabel($payment->payment_method),
                'amount' => (float) $payment->total_amount,
                'total_order' => (int) $payment->total_order,
                'percent' => round(((float) $payment->total_amount / $totalOnlinePaymentToday) * 100),
                'class' => $this->paymentProgressClass($payment->payment_method),
            ];
        });

        return view('ecommerce', compact(
            'todayOmzet',
            'monthOmzet',
            'todayTransactions',
            'todayItemsSold',
            'todayOnlineOrders',
            'todayOnlineOmzet',
            'newOnlineOrders',
            'processingOnlineOrders',
            'waitingPaymentConfirmations',
            'paidOnlineOrders',
            'completedOnlineOrdersToday',
            'onlineOrdersNotConvertedToSale',
            'lowStockCount',
            'totalProducts',
            'activeProducts',
            'safeStockProducts',
            'emptyStockProducts',
            'latestSales',
            'latestOnlineOrders',
            'lowStockProducts',
            'latestStockMovements',
            'bestProducts',
            'weeklySales',
            'paymentSummary',
            'onlinePaymentSummary'
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
        return match (strtoupper((string) $method)) {
            'CASH' => 'Tunai / Cash',
            'COD' => 'Tunai / COD',
            'QRIS' => 'QRIS',
            'TRANSFER', 'BANK_TRANSFER', 'TRANSFER_BANK' => 'Transfer Bank',
            'EDC', 'CARD', 'DEBIT', 'CREDIT_CARD' => 'EDC / Kartu',
            default => $method ? ucwords(str_replace(['_', '-'], ' ', strtolower($method))) : 'Lainnya',
        };
    }

    private function paymentProgressClass(?string $method): string
    {
        return match (strtoupper((string) $method)) {
            'CASH', 'COD' => 'bg-success',
            'QRIS' => 'bg-primary',
            'TRANSFER', 'BANK_TRANSFER', 'TRANSFER_BANK' => 'bg-info',
            'EDC', 'CARD', 'DEBIT', 'CREDIT_CARD' => 'bg-warning',
            default => 'bg-secondary',
        };
    }
}
