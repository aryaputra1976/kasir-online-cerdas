<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method');

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $salesBaseQuery = DB::table('sales')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sale_date', [$startDateTime, $endDateTime]);

        if (! empty($paymentMethod)) {
            $salesBaseQuery->where('sales.payment_method', $paymentMethod);
        }

        $itemBaseQuery = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sale_date', [$startDateTime, $endDateTime]);

        if (! empty($paymentMethod)) {
            $itemBaseQuery->where('sales.payment_method', $paymentMethod);
        }

        $totalOmzet = (clone $salesBaseQuery)->sum('sales.total_amount');
        $totalDiskon = (clone $salesBaseQuery)->sum('sales.discount_amount');
        $totalPajak = (clone $salesBaseQuery)->sum('sales.tax_amount');
        $jumlahTransaksi = (clone $salesBaseQuery)->count();

        $totalPenjualanProduk = (clone $itemBaseQuery)->sum('sale_items.subtotal_amount');

        $totalModal = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)), 0) as total_modal')
            ->value('total_modal');

        $itemTerjual = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->value('total_qty');

        $labaKotor = $totalPenjualanProduk - $totalModal;
        $labaBersih = $labaKotor - $totalDiskon + $totalPajak;
        $marginLaba = $totalOmzet > 0 ? ($labaBersih / $totalOmzet) * 100 : 0;
        $rataRataTransaksi = $jumlahTransaksi > 0 ? $totalOmzet / $jumlahTransaksi : 0;

        $transactionSummaries = (clone $salesBaseQuery)
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->select([
                'sales.id',
                'sales.invoice_no as invoice_number',
                'sales.sale_date',
                'sales.customer_name',
                'sales.payment_method',
                'sales.total_amount',
                'sales.discount_amount',
                'sales.tax_amount',
            ])
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as total_penjualan_produk')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)), 0) as total_modal')
            ->groupBy([
                'sales.id',
                'sales.invoice_no',
                'sales.sale_date',
                'sales.customer_name',
                'sales.payment_method',
                'sales.total_amount',
                'sales.discount_amount',
                'sales.tax_amount',
            ])
            ->orderByDesc('sales.sale_date')
            ->get()
            ->map(function ($sale) {
                $sale->laba_kotor = $sale->total_penjualan_produk - $sale->total_modal;
                $sale->laba_bersih = $sale->laba_kotor - $sale->discount_amount + $sale->tax_amount;

                return $sale;
            });

        $productProfitSummaries = (clone $itemBaseQuery)
            ->select(
                'sale_items.product_id',
                DB::raw("COALESCE(sale_items.product_name, 'Produk tidak ditemukan') as product_name")
            )
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as total_omzet_produk')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)), 0) as total_modal_produk')
            ->groupBy('sale_items.product_id', 'sale_items.product_name')
            ->orderByDesc('total_omzet_produk')
            ->get()
            ->map(function ($product) {
                $product->laba_kotor_produk = $product->total_omzet_produk - $product->total_modal_produk;
                $product->margin_produk = $product->total_omzet_produk > 0
                    ? ($product->laba_kotor_produk / $product->total_omzet_produk) * 100
                    : 0;

                return $product;
            });

        return view('profit-loss-report', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentMethod' => $paymentMethod,
            'paymentMethods' => $this->paymentMethodOptions(),

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

    private function paymentMethodOptions(): array
    {
        return [
            Sale::PAYMENT_CASH => 'Tunai',
            Sale::PAYMENT_QRIS => 'QRIS',
            Sale::PAYMENT_TRANSFER => 'Transfer Bank',
            Sale::PAYMENT_EDC => 'EDC / Kartu',
        ];
    }
}
