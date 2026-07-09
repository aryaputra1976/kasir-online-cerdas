<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OnlineOrderReportController extends Controller
{
    public function index(Request $request)
    {
        $columns = $this->columns();

        $query = $this->filteredQuery($request, $columns);

        $summary = [
            'total_orders' => (clone $query)->count(),

            'new_orders' => $this->countByValues($query, $columns['status'], [
                'new', 'baru', 'pending', 'order_baru', 'Order Baru',
            ]),

            'processing_orders' => $this->countByValues($query, $columns['status'], [
                'processing', 'process', 'processed', 'diproses', 'Diproses',
            ]),

            'completed_orders' => $this->countByValues($query, $columns['status'], [
                'completed', 'complete', 'done', 'selesai', 'Selesai',
            ]),

            'cancelled_orders' => $this->countByValues($query, $columns['status'], [
                'cancelled', 'canceled', 'batal', 'dibatalkan', 'Dibatalkan',
            ]),

            'unpaid_orders' => $this->countByValues($query, $columns['payment_status'], [
                'unpaid', 'belum_dibayar', 'Belum Dibayar',
            ]),

            'waiting_confirmation_orders' => $this->countByValues($query, $columns['payment_status'], [
                'waiting_confirmation',
                'waiting-confirmation',
                'menunggu_konfirmasi',
                'Menunggu Konfirmasi',
                'pending',
            ]),

            'paid_orders' => $this->countByValues($query, $columns['payment_status'], [
                'paid', 'dibayar', 'Dibayar',
            ]),

            'rejected_orders' => $this->countByValues($query, $columns['payment_status'], [
                'rejected', 'ditolak', 'Ditolak',
            ]),

            'online_revenue' => $this->sumColumn($query, $columns['total']),

            'entered_sales' => $this->countSaleStatus($query, $columns['sale_id'], true),
            'not_entered_sales' => $this->countSaleStatus($query, $columns['sale_id'], false),
        ];

        $paymentRecap = $this->paymentRecap($query, $columns);

        $orders = (clone $query)
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        $saleInvoices = $this->saleInvoices($orders->getCollection(), $columns);

        return view('online-order-report', [
            'orders' => $orders,
            'summary' => $summary,
            'paymentRecap' => $paymentRecap,
            'columns' => $columns,
            'saleInvoices' => $saleInvoices,
            'statusOptions' => $this->distinctOptions($columns['status']),
            'paymentStatusOptions' => $this->distinctOptions($columns['payment_status']),
            'paymentMethodOptions' => $this->distinctOptions($columns['payment_method']),
            'filters' => [
                'start_date' => $request->input('start_date'),
                'end_date' => $request->input('end_date'),
                'status' => $request->input('status'),
                'payment_status' => $request->input('payment_status'),
                'payment_method' => $request->input('payment_method'),
                'search' => $request->input('search'),
            ],
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $columns = $this->columns();

        $orders = $this->filteredQuery($request, $columns)
            ->latest('created_at')
            ->get();

        $saleInvoices = $this->saleInvoices($orders, $columns);

        $filename = 'laporan-order-online-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($orders, $columns, $saleInvoices) {
            $handle = fopen('php://output', 'w');

            // UTF-8 BOM + separator agar enak dibuka di Excel Indonesia.
            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=;\r\n");

            fputcsv($handle, [
                'Order No',
                'Tanggal',
                'Customer',
                'HP',
                'Metode Pembayaran',
                'Status Pembayaran',
                'Status Order',
                'Status Stok',
                'Status Penjualan',
                'Subtotal',
                'Diskon',
                'Pajak',
                'Ongkir',
                'Total',
                'Invoice Penjualan',
            ], ';');

            foreach ($orders as $order) {
                $saleId = $this->value($order, $columns['sale_id']);
                $invoice = $saleId ? ($saleInvoices[$saleId] ?? '-') : '-';

                fputcsv($handle, [
                    $this->orderNumber($order, $columns),
                    optional($order->created_at)->format('d/m/Y H:i'),
                    $this->value($order, $columns['customer_name'], '-'),
                    $this->value($order, $columns['customer_phone'], '-'),
                    $this->label($this->value($order, $columns['payment_method'], '-'), 'payment_method'),
                    $this->label($this->value($order, $columns['payment_status'], '-'), 'payment_status'),
                    $this->label($this->value($order, $columns['status'], '-'), 'order_status'),
                    $this->stockStatusLabel($order, $columns),
                    $saleId ? 'Sudah Masuk Penjualan' : 'Belum Masuk Penjualan',
                    (float) $this->value($order, $columns['subtotal'], 0),
                    (float) $this->value($order, $columns['discount'], 0),
                    (float) $this->value($order, $columns['tax'], 0),
                    (float) $this->value($order, $columns['shipping'], 0),
                    (float) $this->value($order, $columns['total'], 0),
                    $invoice,
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function filteredQuery(Request $request, array $columns)
    {
        $query = OnlineOrder::query();

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->input('end_date'));
        }

        if ($request->filled('status') && $columns['status']) {
            $query->where($columns['status'], $request->input('status'));
        }

        if ($request->filled('payment_status') && $columns['payment_status']) {
            $query->where($columns['payment_status'], $request->input('payment_status'));
        }

        if ($request->filled('payment_method') && $columns['payment_method']) {
            $query->where($columns['payment_method'], $request->input('payment_method'));
        }

        if ($request->filled('search')) {
            $search = trim((string) $request->input('search'));

            $query->where(function ($subQuery) use ($search, $columns) {
                foreach ([
                    $columns['order_number'],
                    $columns['customer_name'],
                    $columns['customer_phone'],
                    $columns['customer_address'],
                    $columns['customer_note'],
                ] as $column) {
                    if ($column) {
                        $subQuery->orWhere($column, 'like', '%' . $search . '%');
                    }
                }
            });
        }

        return $query;
    }

    private function paymentRecap($query, array $columns): array
    {
        $methodColumn = $columns['payment_method'];
        $totalColumn = $columns['total'];

        $groups = [
            [
                'key' => 'cod',
                'label' => 'Tunai / COD',
                'values' => ['cod', 'cash_on_delivery', 'cash', 'tunai', 'Tunai', 'COD', 'Bayar di Tempat'],
            ],
            [
                'key' => 'qris',
                'label' => 'QRIS',
                'values' => ['qris', 'QRIS'],
            ],
            [
                'key' => 'transfer',
                'label' => 'Transfer Bank',
                'values' => ['transfer', 'bank_transfer', 'transfer_bank', 'bank', 'Transfer Bank'],
            ],
            [
                'key' => 'edc',
                'label' => 'EDC / Kartu',
                'values' => ['edc', 'card', 'kartu', 'debit', 'credit_card', 'kartu_debit', 'kartu_kredit', 'EDC'],
            ],
        ];

        return collect($groups)->map(function ($group) use ($query, $methodColumn, $totalColumn) {
            if (! $methodColumn) {
                return [
                    'key' => $group['key'],
                    'label' => $group['label'],
                    'orders_count' => 0,
                    'total_revenue' => 0,
                ];
            }

            $recapQuery = (clone $query)->whereIn($methodColumn, $group['values']);

            return [
                'key' => $group['key'],
                'label' => $group['label'],
                'orders_count' => $recapQuery->count(),
                'total_revenue' => $this->sumColumn($recapQuery, $totalColumn),
            ];
        })->toArray();
    }

    private function saleInvoices($orders, array $columns): array
    {
        $saleIdColumn = $columns['sale_id'];

        if (! $saleIdColumn || ! Schema::hasTable('sales')) {
            return [];
        }

        $saleIds = $orders
            ->pluck($saleIdColumn)
            ->filter()
            ->unique()
            ->values();

        if ($saleIds->isEmpty()) {
            return [];
        }

        $invoiceColumn = $this->firstColumn('sales', [
            'invoice_number',
            'invoice_no',
            'sale_number',
            'code',
            'number',
        ]);

        $selectColumns = ['id'];

        if ($invoiceColumn) {
            $selectColumns[] = $invoiceColumn;
        }

        return Sale::query()
            ->whereIn('id', $saleIds)
            ->get($selectColumns)
            ->mapWithKeys(function ($sale) use ($invoiceColumn) {
                return [
                    $sale->id => $invoiceColumn
                        ? ($sale->{$invoiceColumn} ?? ('SALE-' . $sale->id))
                        : ('SALE-' . $sale->id),
                ];
            })
            ->toArray();
    }

    private function countByValues($query, ?string $column, array $values): int
    {
        if (! $column) {
            return 0;
        }

        return (clone $query)->whereIn($column, $values)->count();
    }

    private function countSaleStatus($query, ?string $saleIdColumn, bool $entered): int
    {
        if (! $saleIdColumn) {
            return $entered ? 0 : (clone $query)->count();
        }

        return $entered
            ? (clone $query)->whereNotNull($saleIdColumn)->count()
            : (clone $query)->whereNull($saleIdColumn)->count();
    }

    private function sumColumn($query, ?string $column): float
    {
        if (! $column) {
            return 0;
        }

        return (float) (clone $query)->sum($column);
    }

    private function distinctOptions(?string $column): array
    {
        if (! $column) {
            return [];
        }

        return OnlineOrder::query()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->distinct()
            ->orderBy($column)
            ->pluck($column)
            ->mapWithKeys(fn ($value) => [$value => $this->label($value)])
            ->toArray();
    }

    private function columns(): array
    {
        return [
            'order_number' => $this->firstColumn('online_orders', [
                'order_number',
                'order_no',
                'invoice_number',
                'invoice_no',
                'code',
            ]),

            'customer_name' => $this->firstColumn('online_orders', [
                'customer_name',
                'name',
                'buyer_name',
            ]),

            'customer_phone' => $this->firstColumn('online_orders', [
                'customer_phone',
                'phone',
                'whatsapp',
                'wa',
                'buyer_phone',
            ]),

            'customer_address' => $this->firstColumn('online_orders', [
                'customer_address',
                'address',
                'delivery_address',
            ]),

            'customer_note' => $this->firstColumn('online_orders', [
                'customer_note',
                'note',
                'notes',
            ]),

            'payment_method' => $this->firstColumn('online_orders', [
                'payment_method',
                'payment_type',
                'method',
            ]),

            'payment_status' => $this->firstColumn('online_orders', [
                'payment_status',
                'payment_state',
            ]),

            'status' => $this->firstColumn('online_orders', [
                'status',
                'order_status',
            ]),

            'stock_status' => $this->firstColumn('online_orders', [
                'stock_status',
                'stock_deduction_status',
            ]),

            'stock_deducted_at' => $this->firstColumn('online_orders', [
                'stock_deducted_at',
                'stock_reduced_at',
            ]),

            'stock_deducted_flag' => $this->firstColumn('online_orders', [
                'stock_deducted',
                'is_stock_deducted',
                'is_stock_reduced',
            ]),

            'sale_id' => $this->firstColumn('online_orders', [
                'sale_id',
                'pos_sale_id',
            ]),

            'tracking_token' => $this->firstColumn('online_orders', [
                'tracking_token',
                'token',
            ]),

            'subtotal' => $this->firstColumn('online_orders', [
                'subtotal',
                'sub_total',
            ]),

            'discount' => $this->firstColumn('online_orders', [
                'discount',
                'discount_amount',
            ]),

            'tax' => $this->firstColumn('online_orders', [
                'tax',
                'tax_amount',
                'tax_total',
            ]),

            'shipping' => $this->firstColumn('online_orders', [
                'shipping_cost',
                'delivery_fee',
                'service_fee',
                'ongkir',
            ]),

            'total' => $this->firstColumn('online_orders', [
                'grand_total',
                'total',
                'total_amount',
                'final_total',
            ]),
        ];
    }

    private function firstColumn(string $table, array $columns): ?string
    {
        if (! Schema::hasTable($table)) {
            return null;
        }

        foreach ($columns as $column) {
            if (Schema::hasColumn($table, $column)) {
                return $column;
            }
        }

        return null;
    }

    private function value($model, ?string $column, mixed $default = null): mixed
    {
        if (! $column) {
            return $default;
        }

        $value = $model->{$column} ?? null;

        return $value !== null && $value !== '' ? $value : $default;
    }

    private function orderNumber($order, array $columns): string
    {
        $number = $this->value($order, $columns['order_number']);

        return $number ? (string) $number : 'ORD-' . $order->id;
    }

    private function stockStatusLabel($order, array $columns): string
    {
        if ($columns['stock_status']) {
            return $this->label($this->value($order, $columns['stock_status'], '-'));
        }

        if ($columns['stock_deducted_at'] && $this->value($order, $columns['stock_deducted_at'])) {
            return 'Stok Sudah Dikurangi';
        }

        if ($columns['stock_deducted_flag'] && (bool) $this->value($order, $columns['stock_deducted_flag'], false)) {
            return 'Stok Sudah Dikurangi';
        }

        return 'Belum Dikurangi';
    }

    private function label($value, ?string $type = null): string
    {
        if ($value === null || $value === '') {
            return '-';
        }

        $value = (string) $value;

        $labels = [
            'new' => 'Order Baru',
            'baru' => 'Order Baru',
            'pending' => $type === 'payment_status' ? 'Menunggu Konfirmasi' : 'Order Baru',
            'order_baru' => 'Order Baru',

            'processing' => 'Diproses',
            'process' => 'Diproses',
            'processed' => 'Diproses',
            'diproses' => 'Diproses',

            'completed' => 'Selesai',
            'complete' => 'Selesai',
            'done' => 'Selesai',
            'selesai' => 'Selesai',

            'cancelled' => 'Dibatalkan',
            'canceled' => 'Dibatalkan',
            'batal' => 'Dibatalkan',
            'dibatalkan' => 'Dibatalkan',

            'unpaid' => 'Belum Dibayar',
            'belum_dibayar' => 'Belum Dibayar',
            'waiting_confirmation' => 'Menunggu Konfirmasi',
            'waiting-confirmation' => 'Menunggu Konfirmasi',
            'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
            'paid' => 'Dibayar',
            'dibayar' => 'Dibayar',
            'rejected' => 'Ditolak',
            'ditolak' => 'Ditolak',

            'cod' => 'Tunai / COD',
            'cash_on_delivery' => 'Tunai / COD',
            'cash' => 'Tunai',
            'tunai' => 'Tunai',
            'qris' => 'QRIS',
            'transfer' => 'Transfer Bank',
            'bank_transfer' => 'Transfer Bank',
            'transfer_bank' => 'Transfer Bank',
            'bank' => 'Transfer Bank',
            'edc' => 'EDC / Kartu',
            'card' => 'EDC / Kartu',
            'kartu' => 'EDC / Kartu',
            'debit' => 'Kartu Debit',
            'credit_card' => 'Kartu Kredit',
        ];

        $key = strtolower($value);

        return $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $value));
    }
}