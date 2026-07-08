<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $invoiceColumn = $this->firstExistingColumn('sales', [
            'invoice_number',
            'invoice_no',
            'invoice',
            'code',
        ]);

        $customerColumn = $this->firstExistingColumn('sales', [
            'customer_name',
            'customer',
            'buyer_name',
            'name',
        ]);

        $paymentMethodColumn = $this->firstExistingColumn('sales', [
            'payment_method',
            'payment_type',
            'payment',
        ]);

        $totalAmountColumn = $this->firstExistingColumn('sales', [
            'total_amount',
            'grand_total',
            'total',
        ]);

        $discountColumn = $this->firstExistingColumn('sales', [
            'discount_amount',
            'discount',
        ]);

        $taxColumn = $this->firstExistingColumn('sales', [
            'tax_amount',
            'tax',
        ]);

        $salesBaseQuery = DB::table('sales')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime]);

        if (!empty($paymentMethod) && $paymentMethodColumn) {
            $salesBaseQuery->where("sales.{$paymentMethodColumn}", $paymentMethod);
        }

        $itemBaseQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime]);

        if (!empty($paymentMethod) && $paymentMethodColumn) {
            $itemBaseQuery->where("sales.{$paymentMethodColumn}", $paymentMethod);
        }

        $totalOmzet = $totalAmountColumn
            ? (clone $salesBaseQuery)->sum("sales.{$totalAmountColumn}")
            : 0;

        $totalDiskon = $discountColumn
            ? (clone $salesBaseQuery)->sum("sales.{$discountColumn}")
            : 0;

        $totalPajak = $taxColumn
            ? (clone $salesBaseQuery)->sum("sales.{$taxColumn}")
            : 0;

        $jumlahTransaksi = (clone $salesBaseQuery)->count();

        $totalPenjualanProduk = (clone $itemBaseQuery)
            ->sum('sale_items.subtotal_amount');

        $totalModal = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(products.purchase_price, 0)), 0) as total_modal')
            ->value('total_modal');

        $itemTerjual = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->value('total_qty');

        $labaKotor = $totalPenjualanProduk - $totalModal;
        $labaBersih = $labaKotor - $totalDiskon + $totalPajak;
        $marginLaba = $totalOmzet > 0 ? ($labaBersih / $totalOmzet) * 100 : 0;
        $rataRataTransaksi = $jumlahTransaksi > 0 ? $totalOmzet / $jumlahTransaksi : 0;

        $transactionSelects = [
            'sales.id',
            'sales.created_at',
        ];

        $transactionGroupBy = [
            'sales.id',
            'sales.created_at',
        ];

        if ($invoiceColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$invoiceColumn}` as invoice_number");
            $transactionGroupBy[] = "sales.{$invoiceColumn}";
        } else {
            $transactionSelects[] = DB::raw("CONCAT('POS-', `sales`.`id`) as invoice_number");
        }

        if ($customerColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$customerColumn}` as customer_name");
            $transactionGroupBy[] = "sales.{$customerColumn}";
        } else {
            $transactionSelects[] = DB::raw("'Umum' as customer_name");
        }

        if ($paymentMethodColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$paymentMethodColumn}` as payment_method");
            $transactionGroupBy[] = "sales.{$paymentMethodColumn}";
        } else {
            $transactionSelects[] = DB::raw("'Tidak diketahui' as payment_method");
        }

        if ($totalAmountColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$totalAmountColumn}` as total_amount");
            $transactionGroupBy[] = "sales.{$totalAmountColumn}";
        } else {
            $transactionSelects[] = DB::raw("0 as total_amount");
        }

        if ($discountColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$discountColumn}` as discount_amount");
            $transactionGroupBy[] = "sales.{$discountColumn}";
        } else {
            $transactionSelects[] = DB::raw("0 as discount_amount");
        }

        if ($taxColumn) {
            $transactionSelects[] = DB::raw("`sales`.`{$taxColumn}` as tax_amount");
            $transactionGroupBy[] = "sales.{$taxColumn}";
        } else {
            $transactionSelects[] = DB::raw("0 as tax_amount");
        }

        $transactionSummaries = (clone $salesBaseQuery)
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->select($transactionSelects)
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as total_penjualan_produk')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(products.purchase_price, 0)), 0) as total_modal')
            ->groupBy($transactionGroupBy)
            ->orderByDesc('sales.created_at')
            ->get()
            ->map(function ($sale) {
                $sale->laba_kotor = $sale->total_penjualan_produk - $sale->total_modal;
                $sale->laba_bersih = $sale->laba_kotor - $sale->discount_amount + $sale->tax_amount;

                return $sale;
            });

        $productProfitSummaries = (clone $itemBaseQuery)
            ->select(
                'products.id',
                DB::raw("COALESCE(products.name, 'Produk tidak ditemukan') as product_name")
            )
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as total_omzet_produk')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(products.purchase_price, 0)), 0) as total_modal_produk')
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('total_omzet_produk')
            ->get()
            ->map(function ($product) {
                $product->laba_kotor_produk = $product->total_omzet_produk - $product->total_modal_produk;
                $product->margin_produk = $product->total_omzet_produk > 0
                    ? ($product->laba_kotor_produk / $product->total_omzet_produk) * 100
                    : 0;

                return $product;
            });

        $paymentMethods = $paymentMethodColumn
            ? DB::table('sales')
                ->whereNotNull($paymentMethodColumn)
                ->where($paymentMethodColumn, '!=', '')
                ->distinct()
                ->orderBy($paymentMethodColumn)
                ->pluck($paymentMethodColumn)
            : collect();

        return view('profit-loss-report', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentMethod' => $paymentMethod,
            'paymentMethods' => $paymentMethods,

            'totalOmzet' => $totalOmzet,
            'totalPenjualanProduk' => $totalPenjualanProduk,
            'totalModal' => $totalModal,
            'labaKotor' => $labaKotor,
            'totalDiskon' => $totalDiskon,
            'totalPajak' => $totalPajak,
            'labaBersih' => $labaBersih,
            'marginLaba' => $marginLaba,
            'jumlahTransaksi' => $jumlahTransaksi,
            'itemTerjual' => $itemTerjual,
            'rataRataTransaksi' => $rataRataTransaksi,

            'transactionSummaries' => $transactionSummaries,
            'productProfitSummaries' => $productProfitSummaries,
        ]);
    }

    private function firstExistingColumn(string $table, array $columns): ?string
    {
        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }
}