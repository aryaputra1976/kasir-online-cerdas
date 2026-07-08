<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $paymentMethod = $request->string('payment_method')->toString();
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $baseQuery = Sale::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('invoice_no', 'like', "%{$search}%")
                        ->orWhere('customer_name', 'like', "%{$search}%")
                        ->orWhere('note', 'like', "%{$search}%")
                        ->orWhereHas('items', function ($itemQuery) use ($search) {
                            $itemQuery
                                ->where('product_name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%");
                        });
                });
            })
            ->when($paymentMethod, function ($query) use ($paymentMethod) {
                $query->where('payment_method', $paymentMethod);
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereDate('sale_date', '>=', $dateFrom);
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereDate('sale_date', '<=', $dateTo);
            });

        $totalTransactions = (clone $baseQuery)->count();
        $totalOmzet = (clone $baseQuery)->sum('total_amount');
        $totalDiscount = (clone $baseQuery)->sum('discount_amount');
        $totalItemsSold = (clone $baseQuery)
            ->join('sale_items', 'sales.id', '=', 'sale_items.sale_id')
            ->sum('sale_items.quantity');

        $sales = (clone $baseQuery)
            ->with(['items'])
            ->withCount('items')
            ->latest('sale_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('sales-report', compact(
            'sales',
            'search',
            'paymentMethod',
            'dateFrom',
            'dateTo',
            'totalTransactions',
            'totalOmzet',
            'totalDiscount',
            'totalItemsSold'
        ));
    }
}