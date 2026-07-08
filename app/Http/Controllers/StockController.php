<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
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
        $search = $request->string('q')->toString();
        $categoryId = $request->integer('category_id');
        $stockFilter = $request->string('stock_filter')->toString();

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
            ->when($stockFilter === 'safe', function ($query) {
                $query->whereColumn('stock', '>', 'minimum_stock');
            })
            ->when($stockFilter === 'low', function ($query) {
                $query->whereColumn('stock', '<=', 'minimum_stock')
                    ->where('stock', '>', 0);
            })
            ->when($stockFilter === 'empty', function ($query) {
                $query->where('stock', '<=', 0);
            })
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

        $safeStockProducts = Product::whereColumn('stock', '>', 'minimum_stock')->count();

        $lowStockProducts = Product::whereColumn('stock', '<=', 'minimum_stock')
            ->where('stock', '>', 0)
            ->count();

        $emptyStockProducts = Product::where('stock', '<=', 0)->count();

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