<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'stock_status' => ['nullable', Rule::in([
                Product::STOCK_STATUS_SAFE,
                Product::STOCK_STATUS_LOW,
                Product::STOCK_STATUS_EMPTY,
                Product::STOCK_STATUS_INACTIVE,
            ])],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $categoryId = $validated['category_id'] ?? null;
        $stockStatus = $validated['stock_status'] ?? null;

        $filteredQuery = Product::query()
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
            ->withStockStatus($stockStatus);

        $inventoryQuery = Product::query();

        $totalProducts = (clone $inventoryQuery)->count();
        $activeProducts = (clone $inventoryQuery)->where('is_active', true)->count();
        $inactiveProducts = (clone $inventoryQuery)->where('is_active', false)->count();
        $safeStockProducts = (clone $inventoryQuery)->activeSafeStock()->count();
        $lowStockProducts = (clone $inventoryQuery)->activeLowStock()->count();
        $emptyStockProducts = (clone $inventoryQuery)->activeEmptyStock()->count();
        $negativeStockProducts = (clone $inventoryQuery)->where('stock', '<', 0)->count();

        $totalStockQty = (int) (clone $inventoryQuery)
            ->selectRaw('COALESCE(SUM(CASE WHEN stock > 0 THEN stock ELSE 0 END), 0) as total_qty')
            ->value('total_qty');

        $activeStockCostValue = (float) (clone $inventoryQuery)
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM((CASE WHEN stock > 0 THEN stock ELSE 0 END) * purchase_price), 0) as total_value')
            ->value('total_value');

        $activeStockSellingValue = (float) (clone $inventoryQuery)
            ->where('is_active', true)
            ->selectRaw('COALESCE(SUM((CASE WHEN stock > 0 THEN stock ELSE 0 END) * selling_price), 0) as total_value')
            ->value('total_value');

        $inactiveStockCostValue = (float) Product::query()
            ->where('is_active', false)
            ->selectRaw('COALESCE(SUM((CASE WHEN stock > 0 THEN stock ELSE 0 END) * purchase_price), 0) as total_value')
            ->value('total_value');

        $inactiveStockSellingValue = (float) Product::query()
            ->where('is_active', false)
            ->selectRaw('COALESCE(SUM((CASE WHEN stock > 0 THEN stock ELSE 0 END) * selling_price), 0) as total_value')
            ->value('total_value');

        $stockCostValue = $activeStockCostValue;
        $stockSellingValue = $activeStockSellingValue;
        $potentialGrossProfit = $stockSellingValue - $stockCostValue;

        $latestMovementIds = StockMovement::query()
            ->selectRaw('MAX(id) as id')
            ->groupBy('product_id');

        $lastStockMismatchProductIds = StockMovement::query()
            ->joinSub($latestMovementIds, 'latest_movements', function ($join) {
                $join->on('stock_movements.id', '=', 'latest_movements.id');
            })
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->whereColumn('stock_movements.stock_after', '!=', 'products.stock')
            ->pluck('products.id');

        $brokenMovementProductIds = StockMovement::query()
            ->whereRaw('(stock_before + quantity_change) != stock_after')
            ->whereNotNull('product_id')
            ->distinct()
            ->pluck('product_id');

        $productsWithStockWithoutMovements = Product::query()
            ->where('stock', '>', 0)
            ->whereDoesntHave('stockMovements')
            ->count();

        $anomalyProductIds = $lastStockMismatchProductIds
            ->merge($brokenMovementProductIds)
            ->unique()
            ->values();

        $stockAnomalyProducts = Product::query()
            ->with('category')
            ->where(function ($query) use ($anomalyProductIds) {
                $query->where('stock', '<', 0);

                if ($anomalyProductIds->isNotEmpty()) {
                    $query->orWhereIn('id', $anomalyProductIds);
                }
            })
            ->orderBy('name')
            ->limit(10)
            ->get();

        $stockAnomalyCount = Product::query()
            ->where(function ($query) use ($anomalyProductIds) {
                $query->where('stock', '<', 0);

                if ($anomalyProductIds->isNotEmpty()) {
                    $query->orWhereIn('id', $anomalyProductIds);
                }
            })
            ->count();

        $filteredProducts = (clone $filteredQuery)->count();

        $products = (clone $filteredQuery)
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
            'inactiveProducts',
            'safeStockProducts',
            'lowStockProducts',
            'emptyStockProducts',
            'negativeStockProducts',
            'totalStockQty',
            'stockCostValue',
            'stockSellingValue',
            'activeStockCostValue',
            'activeStockSellingValue',
            'inactiveStockCostValue',
            'inactiveStockSellingValue',
            'potentialGrossProfit',
            'filteredProducts',
            'stockAnomalyCount',
            'stockAnomalyProducts',
            'productsWithStockWithoutMovements'
        ));
    }
}
