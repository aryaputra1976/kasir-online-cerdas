<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StockReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();
        $categoryId = $request->integer('category_id');
        $stockStatus = $request->string('stock_status')->toString();

        $baseQuery = Product::query()
            ->with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($stockStatus === 'safe', function ($query) {
                $query
                    ->where('is_active', true)
                    ->whereColumn('stock', '>', 'minimum_stock');
            })
            ->when($stockStatus === 'low', function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->whereColumn('stock', '<=', 'minimum_stock');
            })
            ->when($stockStatus === 'empty', function ($query) {
                $query
                    ->where('is_active', true)
                    ->where('stock', '<=', 0);
            })
            ->when($stockStatus === 'inactive', function ($query) {
                $query->where('is_active', false);
            });

        $totalProducts = (clone $baseQuery)->count();

        $activeProducts = (clone $baseQuery)
            ->where('is_active', true)
            ->count();

        $safeStockProducts = (clone $baseQuery)
            ->where('is_active', true)
            ->whereColumn('stock', '>', 'minimum_stock')
            ->count();

        $lowStockProducts = (clone $baseQuery)
            ->where('is_active', true)
            ->where('stock', '>', 0)
            ->whereColumn('stock', '<=', 'minimum_stock')
            ->count();

        $emptyStockProducts = (clone $baseQuery)
            ->where('is_active', true)
            ->where('stock', '<=', 0)
            ->count();

        $totalStockQty = (int) (clone $baseQuery)->sum('stock');

        $stockCostValue = (float) (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(stock * purchase_price), 0) as total_value')
            ->value('total_value');

        $stockSellingValue = (float) (clone $baseQuery)
            ->selectRaw('COALESCE(SUM(stock * selling_price), 0) as total_value')
            ->value('total_value');

        $potentialGrossProfit = $stockSellingValue - $stockCostValue;

        $products = (clone $baseQuery)
            ->orderByRaw("
                CASE
                    WHEN is_active = 0 THEN 4
                    WHEN stock <= 0 THEN 1
                    WHEN stock <= minimum_stock THEN 2
                    ELSE 3
                END
            ")
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('stock-report', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'stockStatus',
            'totalProducts',
            'activeProducts',
            'safeStockProducts',
            'lowStockProducts',
            'emptyStockProducts',
            'totalStockQty',
            'stockCostValue',
            'stockSellingValue',
            'potentialGrossProfit'
        ));
    }
}