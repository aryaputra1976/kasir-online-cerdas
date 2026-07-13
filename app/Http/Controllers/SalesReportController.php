<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$startDateTime, $endDateTime] = $this->dateRange($filters);

        $salesBaseQuery = $this->buildSalesBaseQuery($startDateTime, $endDateTime, $filters);
        $itemBaseQuery = $this->buildItemBaseQuery($startDateTime, $endDateTime, $filters);

        $totalOmzet = (clone $salesBaseQuery)->sum('sales.total_amount');
        $totalDiskon = (clone $salesBaseQuery)->sum('sales.discount_amount');
        $totalPajak = (clone $salesBaseQuery)->sum('sales.tax_amount');
        $jumlahTransaksi = (clone $salesBaseQuery)->count();

        $itemTerjual = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->value('total_qty');

        $subtotalProduk = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as subtotal_produk')
            ->value('subtotal_produk');

        $totalModal = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)), 0) as total_modal')
            ->value('total_modal');

        $labaKotor = $subtotalProduk - $totalModal;

        $rataRataTransaksi = $jumlahTransaksi > 0
            ? $totalOmzet / $jumlahTransaksi
            : 0;

        $sales = (clone $salesBaseQuery)
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->select([
                'sales.id',
                'sales.invoice_no as invoice_number',
                'sales.sale_date',
                'sales.customer_name',
                'sales.payment_method',
                'sales.status',
                'sales.subtotal_amount',
                'sales.discount_amount',
                'sales.tax_amount',
                'sales.total_amount',
            ])
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->selectRaw('COALESCE(COUNT(sale_items.id), 0) as item_count')
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as subtotal_produk')
            ->selectRaw('COALESCE(SUM(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)), 0) as total_modal')
            ->groupBy([
                'sales.id',
                'sales.invoice_no',
                'sales.sale_date',
                'sales.customer_name',
                'sales.payment_method',
                'sales.status',
                'sales.subtotal_amount',
                'sales.discount_amount',
                'sales.tax_amount',
                'sales.total_amount',
            ])
            ->orderByDesc('sales.sale_date')
            ->orderByDesc('sales.id')
            ->paginate(10)
            ->withQueryString();

        $sales->getCollection()->transform(function ($sale) {
            $sale->laba_kotor = $sale->subtotal_produk - $sale->total_modal;

            return $sale;
        });

        $saleIds = $sales->getCollection()->pluck('id')->values();
        $itemsBySale = collect();

        if ($saleIds->isNotEmpty()) {
            $itemsBySale = DB::table('sale_items')
                ->whereIn('sale_items.sale_id', $saleIds)
                ->select([
                    'sale_items.sale_id',
                    'sale_items.product_name',
                    'sale_items.sku as product_sku',
                    'sale_items.quantity',
                    'sale_items.unit_price as item_price',
                    'sale_items.subtotal_amount',
                    'sale_items.purchase_price',
                ])
                ->selectRaw('(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)) as modal_item')
                ->selectRaw('(sale_items.subtotal_amount - (sale_items.quantity * COALESCE(sale_items.purchase_price, 0))) as laba_kotor_item')
                ->orderBy('sale_items.id')
                ->get()
                ->groupBy('sale_id');
        }

        return view('sales-report', [
            'startDate' => $filters['start_date'],
            'endDate' => $filters['end_date'],
            'paymentMethod' => $filters['payment_method'] ?? null,
            'search' => $filters['q'] ?? '',
            'paymentMethods' => $this->paymentMethodOptions(),

            'totalOmzet' => $totalOmzet,
            'totalDiskon' => $totalDiskon,
            'totalPajak' => $totalPajak,
            'jumlahTransaksi' => $jumlahTransaksi,
            'itemTerjual' => $itemTerjual,
            'subtotalProduk' => $subtotalProduk,
            'totalModal' => $totalModal,
            'labaKotor' => $labaKotor,
            'rataRataTransaksi' => $rataRataTransaksi,

            'sales' => $sales,
            'itemsBySale' => $itemsBySale,
        ]);
    }

    public function export(Request $request)
    {
        $filters = $this->validatedFilters($request);
        [$startDateTime, $endDateTime] = $this->dateRange($filters);

        $query = $this->buildItemBaseQuery($startDateTime, $endDateTime, $filters)
            ->select([
                'sales.id as sale_id',
                'sales.invoice_no as invoice_number',
                'sales.sale_date',
                'sales.customer_name',
                'sales.payment_method',
                'sales.status',
                'sales.subtotal_amount as transaction_subtotal_amount',
                'sales.discount_amount',
                'sales.tax_amount',
                'sales.total_amount',
                'sale_items.product_name',
                'sale_items.sku as product_sku',
                'sale_items.quantity',
                'sale_items.unit_price as item_price',
                'sale_items.subtotal_amount as item_subtotal_amount',
                'sale_items.purchase_price',
            ])
            ->selectRaw('(sale_items.quantity * COALESCE(sale_items.purchase_price, 0)) as modal_item')
            ->selectRaw('(sale_items.subtotal_amount - (sale_items.quantity * COALESCE(sale_items.purchase_price, 0))) as laba_kotor_item')
            ->orderByDesc('sales.sale_date')
            ->orderByDesc('sales.id')
            ->orderBy('sale_items.id');

        $filename = 'laporan-penjualan-detail-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");

            fputcsv($output, [
                'Invoice',
                'Tanggal',
                'Customer',
                'Metode Pembayaran',
                'Status',
                'Produk',
                'SKU',
                'Qty',
                'Harga Item',
                'Subtotal Item',
                'Modal Item',
                'Laba Kotor Item',
                'Subtotal Transaksi',
                'Diskon Transaksi',
                'Pajak Transaksi',
                'Total Transaksi',
            ]);

            $lastSaleId = null;

            foreach ($query->cursor() as $row) {
                $isFirstInvoiceRow = (int) $row->sale_id !== (int) $lastSaleId;
                $lastSaleId = $row->sale_id;

                fputcsv($output, [
                    $isFirstInvoiceRow ? $this->csvText($row->invoice_number) : '',
                    $isFirstInvoiceRow ? Carbon::parse($row->sale_date)->format('d/m/Y H:i') : '',
                    $isFirstInvoiceRow ? $this->csvText($row->customer_name ?: 'Umum') : '',
                    $isFirstInvoiceRow ? $this->paymentMethodLabel($row->payment_method) : '',
                    $isFirstInvoiceRow ? $this->csvText($row->status) : '',
                    $this->csvText($row->product_name ?: 'Produk tidak ditemukan'),
                    $this->csvText($row->product_sku ?: '-'),
                    (int) $row->quantity,
                    (float) $row->item_price,
                    (float) $row->item_subtotal_amount,
                    (float) $row->modal_item,
                    (float) $row->laba_kotor_item,
                    $isFirstInvoiceRow ? (float) $row->transaction_subtotal_amount : '',
                    $isFirstInvoiceRow ? (float) $row->discount_amount : '',
                    $isFirstInvoiceRow ? (float) $row->tax_amount : '',
                    $isFirstInvoiceRow ? (float) $row->total_amount : '',
                ]);
            }

            fclose($output);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        $input = array_merge([
            'start_date' => now()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
            'payment_method' => null,
            'q' => null,
        ], $request->only(['start_date', 'end_date', 'payment_method', 'q']));

        if (is_string($input['q'])) {
            $input['q'] = trim($input['q']);
        }

        return Validator::make($input, [
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'payment_method' => ['nullable', 'string', 'in:' . implode(',', array_keys($this->paymentMethodOptions()))],
            'q' => ['nullable', 'string', 'max:150'],
        ])->validate();
    }

    private function dateRange(array $filters): array
    {
        return [
            Carbon::parse($filters['start_date'])->startOfDay(),
            Carbon::parse($filters['end_date'])->endOfDay(),
        ];
    }

    private function buildSalesBaseQuery(Carbon $startDateTime, Carbon $endDateTime, array $filters): Builder
    {
        $query = DB::table('sales')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sale_date', [$startDateTime, $endDateTime]);

        $this->applyPaymentMethodFilter($query, $filters);
        $this->applySalesSearchFilter($query, (string) ($filters['q'] ?? ''));

        return $query;
    }

    private function buildItemBaseQuery(Carbon $startDateTime, Carbon $endDateTime, array $filters): Builder
    {
        $query = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->where('sales.status', Sale::STATUS_COMPLETED)
            ->whereBetween('sales.sale_date', [$startDateTime, $endDateTime]);

        $this->applyPaymentMethodFilter($query, $filters);
        $this->applyItemSearchFilter($query, (string) ($filters['q'] ?? ''));

        return $query;
    }

    private function applyPaymentMethodFilter(Builder $query, array $filters): void
    {
        if (! empty($filters['payment_method'])) {
            $query->where('sales.payment_method', $filters['payment_method']);
        }
    }

    private function applySalesSearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            $query
                ->where('sales.invoice_no', 'like', "%{$search}%")
                ->orWhere('sales.customer_name', 'like', "%{$search}%")
                ->orWhereExists(function (Builder $subQuery) use ($search) {
                    $subQuery
                        ->select(DB::raw(1))
                        ->from('sale_items')
                        ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
                        ->whereColumn('sale_items.sale_id', 'sales.id')
                        ->where(function (Builder $itemQuery) use ($search) {
                            $itemQuery
                                ->where('sale_items.product_name', 'like', "%{$search}%")
                                ->orWhere('sale_items.sku', 'like', "%{$search}%")
                                ->orWhere('products.name', 'like', "%{$search}%")
                                ->orWhere('products.sku', 'like', "%{$search}%")
                                ->orWhere('products.barcode', 'like', "%{$search}%");
                        });
                });
        });
    }

    private function applyItemSearchFilter(Builder $query, string $search): void
    {
        if ($search === '') {
            return;
        }

        $query->where(function (Builder $query) use ($search) {
            $query
                ->where('sales.invoice_no', 'like', "%{$search}%")
                ->orWhere('sales.customer_name', 'like', "%{$search}%")
                ->orWhere('sale_items.product_name', 'like', "%{$search}%")
                ->orWhere('sale_items.sku', 'like', "%{$search}%")
                ->orWhere('products.name', 'like', "%{$search}%")
                ->orWhere('products.sku', 'like', "%{$search}%")
                ->orWhere('products.barcode', 'like', "%{$search}%");
        });
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

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return $this->paymentMethodOptions()[$paymentMethod] ?? ($paymentMethod ?: 'Tidak diketahui');
    }

    private function csvText(mixed $value): string
    {
        $value = (string) $value;

        if ($value !== '' && in_array($value[0], ['=', '+', '-', '@', '*'], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
