<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OnlineOrderReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = $this->validatedFilters($request);
        $query = $this->filteredQuery($filters);

        $orders = (clone $query)
            ->orderByDesc('online_orders.created_at')
            ->orderByDesc('online_orders.id')
            ->paginate(10)
            ->withQueryString();

        $orders->getCollection()->transform(fn (OnlineOrder $order) => $this->decorateOrder($order));

        return view('online-order-report', [
            'orders' => $orders,
            'summary' => $this->summary($query),
            'statusSummary' => $this->statusSummary($query),
            'paymentSummary' => $this->paymentSummary($query),
            'paymentRecap' => $this->paymentRecap($query),
            'consistencyIndicators' => $this->consistencyIndicators($query),
            'filters' => $filters,
            'statusOptions' => OnlineOrder::statusLabels(),
            'paymentStatusOptions' => OnlineOrder::paymentStatusLabels(),
            'paymentMethodOptions' => OnlineOrder::paymentMethodLabels(),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $filters = $this->validatedFilters($request);
        $query = $this->filteredQuery($filters)
            ->orderByDesc('online_orders.created_at')
            ->orderByDesc('online_orders.id');

        $filename = 'laporan-order-online-' . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($query) {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fwrite($handle, "sep=;\r\n");

            fputcsv($handle, [
                'order_no',
                'created_at',
                'customer_name',
                'customer_phone',
                'customer_email',
                'customer_address',
                'note',
                'payment_method',
                'payment_status',
                'status',
                'stock_status',
                'sale_conversion_status',
                'subtotal_amount',
                'discount_amount',
                'tax_amount',
                'shipping_amount',
                'total_amount',
                'sale_invoice_no',
                'paid_at',
                'processed_at',
                'completed_at',
                'cancelled_at',
            ], ';');

            foreach ($query->cursor() as $order) {
                $this->decorateOrder($order);

                fputcsv($handle, [
                    $this->csvText($order->order_no),
                    $this->csvDateTime($order->created_at),
                    $this->csvText($order->customer_name),
                    $this->csvText($order->customer_phone),
                    $this->csvText($order->customer_email),
                    $this->csvText($order->customer_address),
                    $this->csvText($order->note),
                    $order->payment_method_label,
                    $order->payment_status_label,
                    $order->status_label,
                    $order->stock_status_label,
                    $order->sale_conversion_status_label,
                    $order->subtotal_amount,
                    $order->discount_amount,
                    $order->tax_amount,
                    $order->shipping_amount,
                    $order->total_amount,
                    $this->csvText($order->sale_invoice_no ?: ''),
                    $this->csvDateTime($order->paid_at),
                    $this->csvDateTime($order->processed_at),
                    $this->csvDateTime($order->completed_at),
                    $this->csvDateTime($order->cancelled_at),
                ], ';');
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function validatedFilters(Request $request): array
    {
        return $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'status' => ['nullable', Rule::in(OnlineOrder::STATUSES)],
            'payment_status' => ['nullable', Rule::in(OnlineOrder::PAYMENT_STATUSES)],
            'payment_method' => ['nullable', Rule::in(OnlineOrder::PAYMENT_METHODS)],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = OnlineOrder::query()
            ->leftJoin('sales', 'sales.id', '=', 'online_orders.sale_id')
            ->select([
                'online_orders.*',
                'sales.invoice_no as sale_invoice_no',
            ]);

        if (! empty($filters['start_date'])) {
            $query->where('online_orders.created_at', '>=', Carbon::parse($filters['start_date'])->startOfDay());
        }

        if (! empty($filters['end_date'])) {
            $query->where('online_orders.created_at', '<=', Carbon::parse($filters['end_date'])->endOfDay());
        }

        if (! empty($filters['status'])) {
            $query->where('online_orders.status', $filters['status']);
        }

        if (! empty($filters['payment_status'])) {
            $query->where('online_orders.payment_status', $filters['payment_status']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('online_orders.payment_method', $filters['payment_method']);
        }

        if (! empty($filters['search'])) {
            $search = trim((string) $filters['search']);

            $query->where(function (Builder $subQuery) use ($search) {
                $subQuery
                    ->where('online_orders.order_no', 'like', "%{$search}%")
                    ->orWhere('online_orders.customer_name', 'like', "%{$search}%")
                    ->orWhere('online_orders.customer_phone', 'like', "%{$search}%")
                    ->orWhere('online_orders.customer_email', 'like', "%{$search}%")
                    ->orWhere('online_orders.customer_address', 'like', "%{$search}%")
                    ->orWhere('online_orders.note', 'like', "%{$search}%")
                    ->orWhere('sales.invoice_no', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    private function summary(Builder $query): array
    {
        return [
            'total_orders' => (clone $query)->count(),
            'total_order_value' => $this->sumTotal($query),
            'paid_order_value' => $this->sumTotal(
                (clone $query)
                    ->where('online_orders.payment_status', OnlineOrder::PAYMENT_PAID)
                    ->where('online_orders.status', '!=', OnlineOrder::STATUS_CANCELLED)
            ),
            'completed_revenue' => $this->sumTotal(
                (clone $query)
                    ->where('online_orders.status', OnlineOrder::STATUS_COMPLETED)
                    ->where('online_orders.payment_status', OnlineOrder::PAYMENT_PAID)
            ),
            'entered_sales' => (clone $query)->whereNotNull('online_orders.sale_id')->count(),
            'conversion_anomalies' => $this->conversionAnomalyQuery($query)->count(),
            'cancelled_value' => $this->sumTotal(
                (clone $query)->where('online_orders.status', OnlineOrder::STATUS_CANCELLED)
            ),
        ];
    }

    private function statusSummary(Builder $query): array
    {
        return collect(OnlineOrder::statusLabels())
            ->map(fn (string $label, string $status) => [
                'label' => $label,
                'count' => (clone $query)->where('online_orders.status', $status)->count(),
            ])
            ->toArray();
    }

    private function paymentSummary(Builder $query): array
    {
        return collect(OnlineOrder::paymentStatusLabels())
            ->map(fn (string $label, string $status) => [
                'label' => $label,
                'count' => (clone $query)->where('online_orders.payment_status', $status)->count(),
            ])
            ->toArray();
    }

    private function paymentRecap(Builder $query): array
    {
        return collect(OnlineOrder::paymentMethodLabels())
            ->map(function (string $label, string $method) use ($query) {
                $methodQuery = (clone $query)->where('online_orders.payment_method', $method);

                return [
                    'method' => $method,
                    'label' => $label,
                    'orders_count' => (clone $methodQuery)->count(),
                    'paid_count' => (clone $methodQuery)
                        ->where('online_orders.payment_status', OnlineOrder::PAYMENT_PAID)
                        ->count(),
                    'all_value' => $this->sumTotal($methodQuery),
                    'paid_value' => $this->sumTotal(
                        (clone $methodQuery)->where('online_orders.payment_status', OnlineOrder::PAYMENT_PAID)
                    ),
                    'completed_count' => (clone $methodQuery)
                        ->where('online_orders.status', OnlineOrder::STATUS_COMPLETED)
                        ->count(),
                ];
            })
            ->values()
            ->toArray();
    }

    private function consistencyIndicators(Builder $query): array
    {
        return [
            [
                'key' => 'completed_unpaid',
                'label' => 'Selesai tetapi belum PAID',
                'count' => (clone $query)
                    ->where('online_orders.status', OnlineOrder::STATUS_COMPLETED)
                    ->where('online_orders.payment_status', '!=', OnlineOrder::PAYMENT_PAID)
                    ->count(),
            ],
            [
                'key' => 'conversion_anomaly',
                'label' => 'Anomali Belum Masuk Penjualan',
                'count' => $this->conversionAnomalyQuery($query)->count(),
            ],
            [
                'key' => 'sale_status_mismatch',
                'label' => 'Sudah ada Sale tetapi belum selesai',
                'count' => (clone $query)
                    ->whereNotNull('online_orders.sale_id')
                    ->where('online_orders.status', '!=', OnlineOrder::STATUS_COMPLETED)
                    ->count(),
            ],
            [
                'key' => 'missing_stock_deduction',
                'label' => 'Diproses/Selesai tanpa pengurangan stok',
                'count' => (clone $query)
                    ->whereIn('online_orders.status', [
                        OnlineOrder::STATUS_PROCESSING,
                        OnlineOrder::STATUS_COMPLETED,
                    ])
                    ->whereNull('online_orders.stock_deducted_at')
                    ->count(),
            ],
            [
                'key' => 'cancelled_with_stock',
                'label' => 'Dibatalkan tetapi stok sudah dikurangi',
                'count' => (clone $query)
                    ->where('online_orders.status', OnlineOrder::STATUS_CANCELLED)
                    ->whereNotNull('online_orders.stock_deducted_at')
                    ->count(),
            ],
        ];
    }

    private function decorateOrder(OnlineOrder $order): OnlineOrder
    {
        $order->setAttribute('sale_conversion_status_label', $order->sale_conversion_status_label);
        $order->setAttribute('sale_conversion_status_class', $order->sale_conversion_status_class);
        $order->setAttribute('consistency_indicators', $order->consistencyIndicators());

        return $order;
    }

    private function conversionAnomalyQuery(Builder $query): Builder
    {
        return (clone $query)
            ->where('online_orders.status', OnlineOrder::STATUS_COMPLETED)
            ->where('online_orders.payment_status', OnlineOrder::PAYMENT_PAID)
            ->whereNull('online_orders.sale_id');
    }

    private function sumTotal(Builder $query): float
    {
        return (float) (clone $query)->sum('online_orders.total_amount');
    }

    private function csvDateTime(mixed $value): string
    {
        if (! $value) {
            return '';
        }

        return $value instanceof \Carbon\CarbonInterface
            ? $value->format('Y-m-d H:i:s')
            : (string) $value;
    }

    private function csvText(mixed $value): string
    {
        $text = (string) ($value ?? '');

        if ($text !== '' && in_array($text[0], ['=', '+', '-', '@', '*'], true)) {
            return "'" . $text;
        }

        return $text;
    }
}
