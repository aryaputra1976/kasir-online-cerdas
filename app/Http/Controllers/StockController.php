<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StockController extends Controller
{
    public function index(Request $request): View
    {
        return $this->renderStockPage($request);
    }

    public function low(Request $request): View
    {
        $request->merge([
            'stock_filter' => 'low',
        ]);

        return $this->renderStockPage($request, 'Produk Stok Menipis');
    }

    private function renderStockPage(Request $request, string $pageTitle = 'Stok Barang'): View
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', Rule::exists('categories', 'id')],
            'stock_filter' => ['nullable', Rule::in([
                Product::STOCK_STATUS_SAFE,
                Product::STOCK_STATUS_LOW,
                Product::STOCK_STATUS_EMPTY,
                Product::STOCK_STATUS_INACTIVE,
            ])],
        ]);

        $search = trim((string) ($validated['q'] ?? ''));
        $categoryId = $validated['category_id'] ?? null;
        $stockFilter = $validated['stock_filter'] ?? null;

        $products = Product::query()
            ->with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function ($subQuery) use ($search) {
                    $subQuery
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%")
                        ->orWhere('barcode', 'like', "%{$search}%");
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->withStockStatus($stockFilter)
            ->orderBy('stock')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $totalProducts = Product::count();

        $safeStockProducts = Product::query()->activeSafeStock()->count();
        $lowStockProducts = Product::query()->activeLowStock()->count();
        $emptyStockProducts = Product::query()->activeEmptyStock()->count();

        return view('stocks', compact(
            'products',
            'categories',
            'search',
            'categoryId',
            'stockFilter',
            'pageTitle',
            'totalProducts',
            'safeStockProducts',
            'lowStockProducts',
            'emptyStockProducts'
        ));
    }
}
