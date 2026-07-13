<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Services\BestSellingProductQuery;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BestSellingProductReportController extends Controller
{
    public function index(Request $request, BestSellingProductQuery $bestSellingProductQuery): View
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

        $baseQuery = $bestSellingProductQuery->base($filters);
        $rankingQuery = $bestSellingProductQuery->ranking($baseQuery);

        $totalItemsSold = (clone $baseQuery)->sum('sale_items.quantity');
        $totalOmzet = (clone $baseQuery)->sum('sale_items.subtotal_amount');
        $totalTransactions = (clone $baseQuery)->distinct('sale_items.sale_id')->count('sale_items.sale_id');
        $totalProductsSold = DB::query()
            ->fromSub($bestSellingProductQuery->identity($baseQuery), 'product_identities')
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

}
