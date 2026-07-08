<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SaleItem;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BestSellingProductReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $categoryId = $request->integer('category_id');
        $dateFrom = $request->string('date_from')->toString();
        $dateTo = $request->string('date_to')->toString();

        $baseQuery = SaleItem::query()
            ->with(['sale', 'product.category'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('product_name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhereHas('product', function ($productQuery) use ($search) {
                            $productQuery
                                ->where('name', 'like', "%{$search}%")
                                ->orWhere('sku', 'like', "%{$search}%")
                                ->orWhere('barcode', 'like', "%{$search}%");
                        });
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->whereHas('product', function ($productQuery) use ($categoryId) {
                    $productQuery->where('category_id', $categoryId);
                });
            })
            ->when($dateFrom, function ($query) use ($dateFrom) {
                $query->whereHas('sale', function ($saleQuery) use ($dateFrom) {
                    $saleQuery->whereDate('sale_date', '>=', $dateFrom);
                });
            })
            ->when($dateTo, function ($query) use ($dateTo) {
                $query->whereHas('sale', function ($saleQuery) use ($dateTo) {
                    $saleQuery->whereDate('sale_date', '<=', $dateTo);
                });
            });

        $totalItemsSold = (clone $baseQuery)->sum('quantity');
        $totalOmzet = (clone $baseQuery)->sum('subtotal_amount');
        $totalTransactions = (clone $baseQuery)->distinct('sale_id')->count('sale_id');
        $totalProductsSold = (clone $baseQuery)->distinct('product_id')->count('product_id');

        $topProduct = (clone $baseQuery)
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
            ->orderByDesc('total_amount')
            ->first();

        $maxProductSold = max(1, (int) ($topProduct?->total_sold ?? 0));

        $bestProducts = (clone $baseQuery)
            ->select([
                'product_id',
                'product_name',
                'sku',
                'unit',
            ])
            ->selectRaw('SUM(quantity) as total_sold')
            ->selectRaw('SUM(subtotal_amount) as total_amount')
            ->selectRaw('COUNT(DISTINCT sale_id) as total_transactions')
            ->selectRaw('AVG(unit_price) as average_price')
            ->groupBy('product_id', 'product_name', 'sku', 'unit')
            ->orderByDesc('total_sold')
            ->orderByDesc('total_amount')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('best-products-report', compact(
            'bestProducts',
            'categories',
            'search',
            'categoryId',
            'dateFrom',
            'dateTo',
            'totalItemsSold',
            'totalOmzet',
            'totalTransactions',
            'totalProductsSold',
            'topProduct',
            'maxProductSold'
        ));
    }
}