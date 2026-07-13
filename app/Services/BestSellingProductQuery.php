<?php

namespace App\Services;

use App\Models\Sale;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class BestSellingProductQuery
{
    public function base(array $filters = []): Builder
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

        if (($filters['q'] ?? '') !== '') {
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

    public function identity(Builder $baseQuery): Builder
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

    public function ranking(Builder $baseQuery): Builder
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
