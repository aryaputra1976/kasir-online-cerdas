<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Sale;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BestSellingProductReportController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $filters['q'] = trim((string) ($filters['q'] ?? ''));
        $filters['category_id'] = $filters['category_id'] ?? null;
        $filters['date_from'] = $filters['date_from'] ?? null;
        $filters['date_to'] = $filters['date_to'] ?? null;

        $baseQuery = $this->baseQuery($filters);
        $rankingQuery = $this->rankingQuery($baseQuery);

        $totalItemsSold = (clone $baseQuery)->sum('sale_items.quantity');
        $totalOmzet = (clone $baseQuery)->sum('sale_items.subtotal_amount');
        $totalTransactions = (clone $baseQuery)->distinct('sale_items.sale_id')->count('sale_items.sale_id');
        $totalProductsSold = DB::query()
            ->fromSub($this->identityQuery($baseQuery), 'product_identities')
            ->count();

        $topProduct = (clone $rankingQuery)
            ->orderByDesc('total_sold')
            ->orderByDesc('total_amount')
            ->first();

        $maxProductSold = max(1, (int) ($topProduct?->total_sold ?? 0));

        $bestProducts = (clone $rankingQuery)
            ->orderByDesc('total_sold')
            ->orderByDesc('total_amount')
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('best-products-report', [
            'bestProducts' => $bestProducts,
            'categories' => $categories,
            'search' => $filters['q'],
            'categoryId' => $filters['category_id'],
            'dateFrom' => $filters['date_from'],
            'dateTo' => $filters['date_to'],
            'totalItemsSold' => $totalItemsSold,
            'totalOmzet' => $totalOmzet,
            'totalTransactions' => $totalTransactions,
            'totalProductsSold' => $totalProductsSold,
            'topProduct' => $topProduct,
            'maxProductSold' => $maxProductSold,
            'categoryFilterNote' => 'Filter kategori mengikuti kategori produk saat ini. Produk historis yang produknya sudah dihapus tetap tampil pada laporan umum, tetapi tidak dapat dicocokkan ke kategori.',
        ]);
    }

    private function baseQuery(array $filters): Builder
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->leftJoin('categories', 'categories.id', '=', 'products.category_id')
            ->where('sales.status', Sale::STATUS_COMPLETED);

        if (! empty($filters['date_from'])) {
            $query->whereDate('sales.sale_date', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('sales.sale_date', '<=', $filters['date_to']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('products.category_id', $filters['category_id']);
        }

        if ($filters['q'] !== '') {
            $search = $filters['q'];

            $query->where(function (Builder $query) use ($search) {
                $query
                    ->where('sale_items.product_name', 'like', "%{$search}%")
                    ->orWhere('sale_items.sku', 'like', "%{$search}%")
                    ->orWhere('products.name', 'like', "%{$search}%")
                    ->orWhere('products.sku', 'like', "%{$search}%")
                    ->orWhere('products.barcode', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function identityQuery(Builder $baseQuery): Builder
    {
        return (clone $baseQuery)
            ->select([
                'sale_items.product_id',
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN COALESCE(sale_items.sku, '') ELSE '' END as fallback_sku"),
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN sale_items.product_name ELSE '' END as fallback_product_name"),
            ])
            ->groupBy([
                'sale_items.product_id',
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN COALESCE(sale_items.sku, '') ELSE '' END"),
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN sale_items.product_name ELSE '' END"),
            ]);
    }

    private function rankingQuery(Builder $baseQuery): Builder
    {
        return (clone $baseQuery)
            ->select([
                'sale_items.product_id',
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN COALESCE(sale_items.sku, '') ELSE '' END as fallback_sku"),
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN sale_items.product_name ELSE '' END as fallback_product_name"),
                DB::raw('MAX(products.id) as current_product_id'),
                DB::raw('MAX(products.name) as current_product_name'),
                DB::raw('MAX(products.sku) as current_product_sku'),
                DB::raw('MAX(categories.name) as category_name'),
                DB::raw('MAX(sale_items.product_name) as snapshot_product_name'),
                DB::raw('MAX(sale_items.sku) as snapshot_sku'),
                DB::raw('MAX(sale_items.unit) as unit'),
            ])
            ->selectRaw('SUM(sale_items.quantity) as total_sold')
            ->selectRaw('SUM(sale_items.subtotal_amount) as total_amount')
            ->selectRaw('COUNT(DISTINCT sale_items.sale_id) as total_transactions')
            ->selectRaw('SUM(sale_items.subtotal_amount) / NULLIF(SUM(sale_items.quantity), 0) as average_price')
            ->groupBy([
                'sale_items.product_id',
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN COALESCE(sale_items.sku, '') ELSE '' END"),
                DB::raw("CASE WHEN sale_items.product_id IS NULL THEN sale_items.product_name ELSE '' END"),
            ]);
    }
}
