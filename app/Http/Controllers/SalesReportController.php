<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method');
        $search = trim((string) $request->input('q', ''));

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $columns = $this->detectColumns();

        $salesBaseQuery = $this->buildSalesBaseQuery(
            $startDateTime,
            $endDateTime,
            $paymentMethod,
            $search,
            $columns
        );

        $itemBaseQuery = $this->buildItemBaseQuery(
            $startDateTime,
            $endDateTime,
            $paymentMethod,
            $search,
            $columns
        );

        $totalOmzet = $columns['total_amount']
            ? (clone $salesBaseQuery)->sum("sales.{$columns['total_amount']}")
            : 0;

        $totalDiskon = $columns['discount_amount']
            ? (clone $salesBaseQuery)->sum("sales.{$columns['discount_amount']}")
            : 0;

        $totalPajak = $columns['tax_amount']
            ? (clone $salesBaseQuery)->sum("sales.{$columns['tax_amount']}")
            : 0;

        $jumlahTransaksi = (clone $salesBaseQuery)->count();

        $itemTerjual = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->value('total_qty');

        $subtotalProduk = (clone $itemBaseQuery)
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as subtotal_produk')
            ->value('subtotal_produk');

        $rataRataTransaksi = $jumlahTransaksi > 0
            ? $totalOmzet / $jumlahTransaksi
            : 0;

        $transactionSelects = [
            'sales.id',
            'sales.created_at',
        ];

        $transactionGroupBy = [
            'sales.id',
            'sales.created_at',
        ];

        if ($columns['invoice']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['invoice']}` as invoice_number");
            $transactionGroupBy[] = "sales.{$columns['invoice']}";
        } else {
            $transactionSelects[] = DB::raw("CONCAT('POS-', `sales`.`id`) as invoice_number");
        }

        if ($columns['customer']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['customer']}` as customer_name");
            $transactionGroupBy[] = "sales.{$columns['customer']}";
        } else {
            $transactionSelects[] = DB::raw("'Umum' as customer_name");
        }

        if ($columns['payment_method']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['payment_method']}` as payment_method");
            $transactionGroupBy[] = "sales.{$columns['payment_method']}";
        } else {
            $transactionSelects[] = DB::raw("'Tidak diketahui' as payment_method");
        }

        if ($columns['status']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['status']}` as status");
            $transactionGroupBy[] = "sales.{$columns['status']}";
        } else {
            $transactionSelects[] = DB::raw("'Selesai' as status");
        }

        if ($columns['total_amount']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['total_amount']}` as total_amount");
            $transactionGroupBy[] = "sales.{$columns['total_amount']}";
        } else {
            $transactionSelects[] = DB::raw("0 as total_amount");
        }

        if ($columns['discount_amount']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['discount_amount']}` as discount_amount");
            $transactionGroupBy[] = "sales.{$columns['discount_amount']}";
        } else {
            $transactionSelects[] = DB::raw("0 as discount_amount");
        }

        if ($columns['tax_amount']) {
            $transactionSelects[] = DB::raw("`sales`.`{$columns['tax_amount']}` as tax_amount");
            $transactionGroupBy[] = "sales.{$columns['tax_amount']}";
        } else {
            $transactionSelects[] = DB::raw("0 as tax_amount");
        }

        $sales = (clone $salesBaseQuery)
            ->leftJoin('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->select($transactionSelects)
            ->selectRaw('COALESCE(SUM(sale_items.quantity), 0) as total_qty')
            ->selectRaw('COALESCE(COUNT(sale_items.id), 0) as item_count')
            ->selectRaw('COALESCE(SUM(sale_items.subtotal_amount), 0) as subtotal_produk')
            ->groupBy($transactionGroupBy)
            ->orderByDesc('sales.created_at')
            ->paginate(10)
            ->withQueryString();

        $saleIds = $sales->getCollection()->pluck('id')->values();

        $itemsBySale = collect();

        if ($saleIds->isNotEmpty()) {
            $itemPriceExpression = $this->itemPriceExpression($columns['item_price']);
            $productNameExpression = $this->productNameExpression($columns['sale_item_product_name']);
            $productSkuExpression = $this->productSkuExpression($columns['product_sku']);

            $itemsBySale = DB::table('sale_items')
                ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
                ->whereIn('sale_items.sale_id', $saleIds)
                ->select(
                    'sale_items.sale_id',
                    'sale_items.quantity',
                    'sale_items.subtotal_amount'
                )
                ->selectRaw("{$itemPriceExpression} as item_price")
                ->selectRaw("{$productNameExpression} as product_name")
                ->selectRaw("{$productSkuExpression} as product_sku")
                ->orderBy('sale_items.id')
                ->get()
                ->groupBy('sale_id');
        }

        $paymentMethods = $columns['payment_method']
            ? DB::table('sales')
                ->whereNotNull($columns['payment_method'])
                ->where($columns['payment_method'], '!=', '')
                ->distinct()
                ->orderBy($columns['payment_method'])
                ->pluck($columns['payment_method'])
            : collect();

        return view('sales-report', [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'paymentMethod' => $paymentMethod,
            'search' => $search,
            'paymentMethods' => $paymentMethods,

            'totalOmzet' => $totalOmzet,
            'totalDiskon' => $totalDiskon,
            'totalPajak' => $totalPajak,
            'jumlahTransaksi' => $jumlahTransaksi,
            'itemTerjual' => $itemTerjual,
            'subtotalProduk' => $subtotalProduk,
            'rataRataTransaksi' => $rataRataTransaksi,

            'sales' => $sales,
            'itemsBySale' => $itemsBySale,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', now()->toDateString());
        $paymentMethod = $request->input('payment_method');
        $search = trim((string) $request->input('q', ''));

        $startDateTime = Carbon::parse($startDate)->startOfDay();
        $endDateTime = Carbon::parse($endDate)->endOfDay();

        $columns = $this->detectColumns();

        $itemPriceExpression = $this->itemPriceExpression($columns['item_price']);
        $productNameExpression = $this->productNameExpression($columns['sale_item_product_name']);
        $productSkuExpression = $this->productSkuExpression($columns['product_sku']);

        $query = $this->buildItemBaseQuery(
            $startDateTime,
            $endDateTime,
            $paymentMethod,
            $search,
            $columns
        )
            ->select(
                'sales.id as sale_id',
                'sales.created_at',
                'sale_items.quantity',
                'sale_items.subtotal_amount'
            )
            ->selectRaw("{$itemPriceExpression} as item_price")
            ->selectRaw("{$productNameExpression} as product_name")
            ->selectRaw("{$productSkuExpression} as product_sku")
            ->selectRaw('COALESCE(products.purchase_price, 0) as purchase_price');

        if ($columns['invoice']) {
            $query->selectRaw("`sales`.`{$columns['invoice']}` as invoice_number");
        } else {
            $query->selectRaw("CONCAT('POS-', `sales`.`id`) as invoice_number");
        }

        if ($columns['customer']) {
            $query->selectRaw("`sales`.`{$columns['customer']}` as customer_name");
        } else {
            $query->selectRaw("'Umum' as customer_name");
        }

        if ($columns['payment_method']) {
            $query->selectRaw("`sales`.`{$columns['payment_method']}` as payment_method");
        } else {
            $query->selectRaw("'Tidak diketahui' as payment_method");
        }

        if ($columns['status']) {
            $query->selectRaw("`sales`.`{$columns['status']}` as status");
        } else {
            $query->selectRaw("'Selesai' as status");
        }

        if ($columns['total_amount']) {
            $query->selectRaw("`sales`.`{$columns['total_amount']}` as total_amount");
        } else {
            $query->selectRaw("0 as total_amount");
        }

        if ($columns['discount_amount']) {
            $query->selectRaw("`sales`.`{$columns['discount_amount']}` as discount_amount");
        } else {
            $query->selectRaw("0 as discount_amount");
        }

        if ($columns['tax_amount']) {
            $query->selectRaw("`sales`.`{$columns['tax_amount']}` as tax_amount");
        } else {
            $query->selectRaw("0 as tax_amount");
        }

        $query->orderByDesc('sales.created_at')
            ->orderBy('sales.id')
            ->orderBy('sale_items.id');

        $filename = 'laporan-penjualan-detail-' . now()->format('Ymd-His') . '.xls';

        return response()->streamDownload(function () use ($query) {
            $escape = function ($value) {
                return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
            };

            echo '<!DOCTYPE html>';
            echo '<html>';
            echo '<head>';
            echo '<meta charset="UTF-8">';
            echo '<style>';
            echo 'table { border-collapse: collapse; width: 100%; font-family: Arial, sans-serif; font-size: 12px; }';
            echo 'th { background-color: #eef2ff; font-weight: bold; text-align: left; }';
            echo 'th, td { border: 1px solid #cbd5e1; padding: 6px; }';
            echo '.text { mso-number-format: "\@"; }';
            echo '.number { mso-number-format: "#,##0"; }';
            echo '.money { mso-number-format: "#,##0"; }';
            echo '</style>';
            echo '</head>';
            echo '<body>';

            echo '<h3>Laporan Penjualan Detail</h3>';

            echo '<table>';
            echo '<thead>';
            echo '<tr>';
            echo '<th>Invoice</th>';
            echo '<th>Tanggal</th>';
            echo '<th>Customer</th>';
            echo '<th>Metode Pembayaran</th>';
            echo '<th>Status</th>';
            echo '<th>Produk</th>';
            echo '<th>SKU</th>';
            echo '<th>Qty</th>';
            echo '<th>Harga Item</th>';
            echo '<th>Subtotal Item</th>';
            echo '<th>Modal Item</th>';
            echo '<th>Laba Kotor Item</th>';
            echo '<th>Diskon Transaksi</th>';
            echo '<th>Pajak Transaksi</th>';
            echo '<th>Total Transaksi</th>';
            echo '</tr>';
            echo '</thead>';
            echo '<tbody>';

            foreach ($query->cursor() as $row) {
                $qty = (float) $row->quantity;
                $hargaItem = (float) $row->item_price;
                $subtotalItem = (float) $row->subtotal_amount;
                $modalItem = $qty * (float) $row->purchase_price;
                $labaKotorItem = $subtotalItem - $modalItem;

                echo '<tr>';
                echo '<td class="text">' . $escape($row->invoice_number) . '</td>';
                echo '<td class="text">' . $escape(Carbon::parse($row->created_at)->format('d/m/Y H:i')) . '</td>';
                echo '<td class="text">' . $escape($row->customer_name ?: 'Umum') . '</td>';
                echo '<td class="text">' . $escape($row->payment_method ?: 'Tidak diketahui') . '</td>';
                echo '<td class="text">' . $escape($row->status ?: 'Selesai') . '</td>';
                echo '<td class="text">' . $escape($row->product_name ?: 'Produk tidak ditemukan') . '</td>';
                echo '<td class="text">' . $escape($row->product_sku ?: '-') . '</td>';
                echo '<td class="number">' . $qty . '</td>';
                echo '<td class="money">' . $hargaItem . '</td>';
                echo '<td class="money">' . $subtotalItem . '</td>';
                echo '<td class="money">' . $modalItem . '</td>';
                echo '<td class="money">' . $labaKotorItem . '</td>';
                echo '<td class="money">' . (float) $row->discount_amount . '</td>';
                echo '<td class="money">' . (float) $row->tax_amount . '</td>';
                echo '<td class="money">' . (float) $row->total_amount . '</td>';
                echo '</tr>';
            }

            echo '</tbody>';
            echo '</table>';
            echo '</body>';
            echo '</html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function buildSalesBaseQuery(
        Carbon $startDateTime,
        Carbon $endDateTime,
        ?string $paymentMethod,
        string $search,
        array $columns
    ) {
        $query = DB::table('sales')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime]);

        if (!empty($paymentMethod) && $columns['payment_method']) {
            $query->where("sales.{$columns['payment_method']}", $paymentMethod);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search, $columns) {
                if ($columns['invoice']) {
                    $query->orWhere("sales.{$columns['invoice']}", 'like', "%{$search}%");
                }

                if ($columns['customer']) {
                    $query->orWhere("sales.{$columns['customer']}", 'like', "%{$search}%");
                }

                $query->orWhereExists(function ($subQuery) use ($search, $columns) {
                    $subQuery->select(DB::raw(1))
                        ->from('sale_items as si')
                        ->leftJoin('products as p', 'p.id', '=', 'si.product_id')
                        ->whereColumn('si.sale_id', 'sales.id')
                        ->where(function ($productQuery) use ($search, $columns) {
                            $productQuery->where('p.name', 'like', "%{$search}%");

                            if ($columns['product_sku']) {
                                $productQuery->orWhere("p.{$columns['product_sku']}", 'like', "%{$search}%");
                            }

                            if ($columns['product_barcode']) {
                                $productQuery->orWhere("p.{$columns['product_barcode']}", 'like', "%{$search}%");
                            }
                        });
                });
            });
        }

        return $query;
    }

    private function buildItemBaseQuery(
        Carbon $startDateTime,
        Carbon $endDateTime,
        ?string $paymentMethod,
        string $search,
        array $columns
    ) {
        $query = DB::table('sale_items')
            ->join('sales', 'sales.id', '=', 'sale_items.sale_id')
            ->leftJoin('products', 'products.id', '=', 'sale_items.product_id')
            ->whereBetween('sales.created_at', [$startDateTime, $endDateTime]);

        if (!empty($paymentMethod) && $columns['payment_method']) {
            $query->where("sales.{$columns['payment_method']}", $paymentMethod);
        }

        if ($search !== '') {
            $query->where(function ($query) use ($search, $columns) {
                if ($columns['invoice']) {
                    $query->orWhere("sales.{$columns['invoice']}", 'like', "%{$search}%");
                }

                if ($columns['customer']) {
                    $query->orWhere("sales.{$columns['customer']}", 'like', "%{$search}%");
                }

                $query->orWhere('products.name', 'like', "%{$search}%");

                if ($columns['product_sku']) {
                    $query->orWhere("products.{$columns['product_sku']}", 'like', "%{$search}%");
                }

                if ($columns['product_barcode']) {
                    $query->orWhere("products.{$columns['product_barcode']}", 'like', "%{$search}%");
                }
            });
        }

        return $query;
    }

    private function detectColumns(): array
    {
        return [
            'invoice' => $this->firstExistingColumn('sales', [
                'invoice_number',
                'invoice_no',
                'invoice',
                'code',
            ]),

            'customer' => $this->firstExistingColumn('sales', [
                'customer_name',
                'customer',
                'buyer_name',
                'name',
            ]),

            'payment_method' => $this->firstExistingColumn('sales', [
                'payment_method',
                'payment_type',
                'payment',
            ]),

            'status' => $this->firstExistingColumn('sales', [
                'status',
                'sale_status',
            ]),

            'total_amount' => $this->firstExistingColumn('sales', [
                'total_amount',
                'grand_total',
                'total',
            ]),

            'discount_amount' => $this->firstExistingColumn('sales', [
                'discount_amount',
                'discount',
            ]),

            'tax_amount' => $this->firstExistingColumn('sales', [
                'tax_amount',
                'tax',
            ]),

            'item_price' => $this->firstExistingColumn('sale_items', [
                'price',
                'unit_price',
                'selling_price',
                'product_price',
            ]),

            'sale_item_product_name' => $this->firstExistingColumn('sale_items', [
                'product_name',
                'name',
            ]),

            'product_sku' => $this->firstExistingColumn('products', [
                'sku',
                'product_code',
                'code',
            ]),

            'product_barcode' => $this->firstExistingColumn('products', [
                'barcode',
                'bar_code',
            ]),
        ];
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

    private function itemPriceExpression(?string $itemPriceColumn): string
    {
        if ($itemPriceColumn) {
            return "COALESCE(sale_items.`{$itemPriceColumn}`, COALESCE(sale_items.subtotal_amount / NULLIF(sale_items.quantity, 0), 0))";
        }

        return "COALESCE(sale_items.subtotal_amount / NULLIF(sale_items.quantity, 0), 0)";
    }

    private function productNameExpression(?string $saleItemProductNameColumn): string
    {
        if ($saleItemProductNameColumn) {
            return "COALESCE(products.name, sale_items.`{$saleItemProductNameColumn}`, 'Produk tidak ditemukan')";
        }

        return "COALESCE(products.name, 'Produk tidak ditemukan')";
    }

    private function productSkuExpression(?string $productSkuColumn): string
    {
        if ($productSkuColumn) {
            return "COALESCE(products.`{$productSkuColumn}`, '-')";
        }

        return "'-'";
    }
}