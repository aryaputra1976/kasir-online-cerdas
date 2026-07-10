<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Dashboard - Kasir Online Cerdas</title>

        @include('partials.styles')

        <style>
            .dashboard-stat-icon {
                width: 48px;
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .sales-chart-bar {
                width: 100%;
                min-height: 190px;
                display: flex;
                align-items: end;
                justify-content: space-between;
                gap: 12px;
                padding-top: 20px;
            }

            .sales-chart-item {
                width: 100%;
                text-align: center;
            }

            .sales-chart-track {
                height: 150px;
                width: 100%;
                max-width: 34px;
                margin: 0 auto 10px;
                background-color: #f1f5f9;
                border-radius: 999px;
                display: flex;
                align-items: end;
                overflow: hidden;
            }

            .sales-chart-fill {
                width: 100%;
                border-radius: 999px;
                background: linear-gradient(180deg, #605dff 0%, #8b5cf6 100%);
            }

            .koc-list-card {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px;
                background-color: #ffffff;
            }

            .koc-list-card:hover {
                background-color: #fafaff;
            }

            .koc-icon-box {
                width: 42px;
                height: 42px;
                min-width: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }

            .koc-payment-progress {
                height: 8px;
                border-radius: 999px;
                background-color: #edf0f7;
                overflow: hidden;
            }

            .koc-payment-progress-bar {
                height: 100%;
                border-radius: 999px;
            }

            @media (max-width: 767.98px) {
                .sales-chart-bar {
                    gap: 8px;
                }

                .sales-chart-track {
                    max-width: 28px;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

$summaryCards = [
    [
        'title' => 'Omzet Hari Ini',
        'value' => $rupiah($todayOmzet),
        'note' => 'Berdasarkan transaksi POS hari ini',
        'icon' => 'payments',
        'color' => 'bg-success bg-opacity-10 text-success',
        'badge' => $todayTransactions . ' transaksi',
        'badgeClass' => 'bg-success bg-opacity-10 text-success',
    ],
    [
        'title' => 'Omzet Bulan Ini',
        'value' => $rupiah($monthOmzet),
        'note' => 'Akumulasi transaksi POS bulan berjalan',
        'icon' => 'calendar_month',
        'color' => 'bg-primary bg-opacity-10 text-primary',
        'badge' => now()->translatedFormat('F Y'),
        'badgeClass' => 'bg-primary bg-opacity-10 text-primary',
    ],
    [
        'title' => 'Transaksi Hari Ini',
        'value' => number_format($todayTransactions, 0, ',', '.'),
        'note' => number_format($todayItemsSold, 0, ',', '.') . ' item terjual hari ini',
        'icon' => 'point_of_sale',
        'color' => 'bg-info bg-opacity-10 text-info',
        'badge' => 'POS',
        'badgeClass' => 'bg-info bg-opacity-10 text-info',
    ],
    [
        'title' => 'Order Online Hari Ini',
        'value' => number_format($todayOnlineOrders, 0, ',', '.'),
        'note' => 'Omzet online hari ini: ' . $rupiah($todayOnlineOmzet),
        'icon' => 'shopping_cart',
        'color' => 'bg-warning bg-opacity-10 text-warning',
        'badge' => 'Online',
        'badgeClass' => 'bg-warning bg-opacity-10 text-warning',
    ],
    [
        'title' => 'Order Online Baru',
        'value' => number_format($newOnlineOrders, 0, ',', '.'),
        'note' => 'Order yang perlu segera diproses',
        'icon' => 'new_releases',
        'color' => 'bg-danger bg-opacity-10 text-danger',
        'badge' => $newOnlineOrders > 0 ? 'Perlu diproses' : 'Aman',
        'badgeClass' => $newOnlineOrders > 0
            ? 'bg-danger bg-opacity-10 text-danger'
            : 'bg-success bg-opacity-10 text-success',
    ],
    [
        'title' => 'Menunggu Konfirmasi',
        'value' => number_format($waitingPaymentConfirmations, 0, ',', '.'),
        'note' => 'Pembayaran online menunggu validasi',
        'icon' => 'fact_check',
        'color' => 'bg-purple bg-opacity-10 text-primary',
        'badge' => 'Pembayaran',
        'badgeClass' => 'bg-primary bg-opacity-10 text-primary',
    ],
    [
        'title' => 'Belum Masuk Penjualan',
        'value' => number_format($onlineOrdersNotConvertedToSale, 0, ',', '.'),
        'note' => 'Order selesai yang belum menjadi transaksi POS',
        'icon' => 'sync_problem',
        'color' => 'bg-warning bg-opacity-10 text-warning',
        'badge' => $onlineOrdersNotConvertedToSale > 0 ? 'Cek order' : 'Aman',
        'badgeClass' => $onlineOrdersNotConvertedToSale > 0
            ? 'bg-warning bg-opacity-10 text-warning'
            : 'bg-success bg-opacity-10 text-success',
    ],
    [
        'title' => 'Produk Stok Menipis',
        'value' => number_format($lowStockCount, 0, ',', '.'),
        'note' => 'Produk dengan stok kurang/sama dari minimum',
        'icon' => 'inventory_2',
        'color' => 'bg-danger bg-opacity-10 text-danger',
        'badge' => $lowStockCount > 0 ? 'Perlu cek' : 'Aman',
        'badgeClass' => $lowStockCount > 0
            ? 'bg-danger bg-opacity-10 text-danger'
            : 'bg-success bg-opacity-10 text-success',
    ],
];

$orderStatusLabel = function (?string $status) {
    return match (strtoupper((string) $status)) {
        'NEW' => 'Baru',
        'PROCESSING' => 'Diproses',
        'COMPLETED' => 'Selesai',
        'CANCELLED' => 'Dibatalkan',
        default => $status ? ucwords(str_replace(['_', '-'], ' ', strtolower($status))) : '-',
    };
};

$orderStatusClass = function (?string $status) {
    return match (strtoupper((string) $status)) {
        'NEW' => 'bg-primary bg-opacity-10 text-primary',
        'PROCESSING' => 'bg-warning bg-opacity-10 text-warning',
        'COMPLETED' => 'bg-success bg-opacity-10 text-success',
        'CANCELLED' => 'bg-danger bg-opacity-10 text-danger',
        default => 'bg-light text-body border',
    };
};

$paymentStatusLabel = function (?string $status) {
    return match (strtoupper((string) $status)) {
        'UNPAID' => 'Belum Dibayar',
        'WAITING_CONFIRMATION' => 'Menunggu Konfirmasi',
        'PAID' => 'Dibayar',
        'REJECTED' => 'Ditolak',
        default => $status ? ucwords(str_replace(['_', '-'], ' ', strtolower($status))) : '-',
    };
};

$paymentStatusClass = function (?string $status) {
    return match (strtoupper((string) $status)) {
        'PAID' => 'bg-success bg-opacity-10 text-success',
        'WAITING_CONFIRMATION' => 'bg-warning bg-opacity-10 text-warning',
        'REJECTED' => 'bg-danger bg-opacity-10 text-danger',
        'UNPAID' => 'bg-secondary bg-opacity-10 text-secondary',
        default => 'bg-light text-body border',
    };
};

$paymentMethodLabel = function (?string $method) {
    return match (strtoupper((string) $method)) {
        'CASH' => 'Tunai / Cash',
        'COD' => 'Tunai / COD',
        'QRIS' => 'QRIS',
        'TRANSFER', 'BANK_TRANSFER', 'TRANSFER_BANK' => 'Transfer Bank',
        'EDC', 'CARD', 'DEBIT', 'CREDIT_CARD' => 'EDC / Kartu',
        default => $method ? ucwords(str_replace(['_', '-'], ' ', strtolower($method))) : 'Lainnya',
    };
};
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Dashboard</h3>
                            <p class="text-body mb-0">Ringkasan aktivitas toko hari ini berdasarkan data asli database</p>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                <i class="material-symbols-outlined align-middle fs-18 me-1">point_of_sale</i>
                                Buka Kasir POS
                            </a>

                            <a href="{{ route('sales.report') }}" class="btn btn-outline-primary">
                                <i class="material-symbols-outlined align-middle fs-18 me-1">receipt_long</i>
                                Laporan Penjualan
                            </a>
                            <a href="{{ route('reports.online-orders.index') }}" class="btn btn-outline-primary">
                                <i class="material-symbols-outlined align-middle fs-18 me-1">shopping_cart_checkout</i>
                                Laporan Order Online
                            </a>                            
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        @foreach ($summaryCards as $card)
                            <div class="col-xxl-3 col-xl-6 col-sm-6">
                                <div class="card bg-white border-0 rounded-3 h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-start gap-3">
                                            <div>
                                                <span class="d-block text-body mb-2">{{ $card['title'] }}</span>
                                                <h3 class="fs-24 fw-semibold mb-1 koc-price">{{ $card['value'] }}</h3>
                                                <p class="fs-13 mb-0 text-body">{{ $card['note'] }}</p>
                                            </div>

                                            <div class="dashboard-stat-icon {{ $card['color'] }}">
                                                <i class="material-symbols-outlined">{{ $card['icon'] }}</i>
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-between align-items-center mt-4">
                                            <span class="badge {{ $card['badgeClass'] }} p-2 fs-12 fw-normal">
                                                {{ $card['badge'] }}
                                            </span>
                                            <span class="fs-12 text-body">Hari ini</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-8">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Grafik Penjualan</h3>
                                            <p class="text-body mb-0 fs-13">Penjualan 7 hari terakhir dari tabel transaksi</p>
                                        </div>

                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-12 fw-normal">
                                            7 Hari Terakhir
                                        </span>
                                    </div>

                                    <div class="sales-chart-bar">
                                        @foreach ($weeklySales as $sale)
                                            <div class="sales-chart-item">
                                                <div class="sales-chart-track" title="{{ $rupiah($sale['amount']) }}">
                                                    <div class="sales-chart-fill" style="height: {{ $sale['percent'] }}%;"></div>
                                                </div>
                                                <span class="d-block fs-12 fw-medium">{{ $sale['day'] }}</span>
                                                <span class="d-block fs-11 text-body">{{ $rupiah($sale['amount']) }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Ringkasan Pembayaran</h3>
                                            <p class="text-body mb-0 fs-13">Komposisi pembayaran hari ini</p>
                                        </div>
                                    </div>

                                    @forelse ($paymentSummary as $payment)
                                        <div class="mb-4">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <div>
                                                    <span class="fw-medium">{{ $payment['label'] }}</span>
                                                    <span class="d-block fs-12 text-body">
                                                        {{ $payment['total_transaction'] }} transaksi
                                                    </span>
                                                </div>
                                                <div class="text-end">
                                                    <span class="fs-13 fw-semibold">{{ $rupiah($payment['amount']) }}</span>
                                                    <span class="d-block fs-12 text-body">{{ $payment['percent'] }}%</span>
                                                </div>
                                            </div>

                                            <div class="koc-payment-progress">
                                                <div class="koc-payment-progress-bar {{ $payment['class'] }}" style="width: {{ $payment['percent'] }}%;"></div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-5 border rounded-3">
                                            <i class="material-symbols-outlined text-body fs-40 mb-2">payments</i>
                                            <h6 class="fw-semibold mb-1">Belum ada pembayaran hari ini</h6>
                                            <p class="text-body mb-0 fs-13">Transaksi POS hari ini akan muncul di sini.</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <div class="card bg-white border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Order Online Terbaru</h3>
                        <p class="text-body mb-0 fs-13">Order terbaru dari halaman menu publik</p>
                    </div>

                    <a href="{{ route('online-orders.index') }}" class="btn btn-outline-primary btn-sm">
                        Lihat Semua
                    </a>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse ($latestOnlineOrders as $order)
                        <div class="koc-list-card">
                            <div class="row align-items-center g-3">
                                <div class="col-lg-5">
                                    <div class="d-flex align-items-start">
                                        <div class="koc-icon-box bg-warning bg-opacity-10 text-warning me-3">
                                            <i class="material-symbols-outlined fs-20">shopping_cart</i>
                                        </div>

                                        <div>
                                            <h6 class="fw-semibold fs-14 mb-1">{{ $order->order_no }}</h6>
                                            <span class="fs-12 text-body">
                                                {{ $order->customer_name ?: 'Customer Online' }}
                                            </span>

                                            <div class="mt-2 d-flex flex-wrap gap-1">
                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                    {{ $order->created_at->format('d/m/Y H:i') }}
                                                </span>

                                                <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                    {{ $paymentMethodLabel($order->payment_method) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-2 col-6">
                                    <span class="d-block fs-12 text-body mb-1">Item</span>
                                    <strong>{{ number_format($order->items->sum('quantity'), 0, ',', '.') }}</strong>
                                </div>

                                <div class="col-lg-2 col-6">
                                    <span class="d-block fs-12 text-body mb-1">Total</span>
                                    <strong class="koc-price">{{ $rupiah($order->total_amount) }}</strong>
                                </div>

                                <div class="col-lg-3">
                                    <div class="d-flex flex-wrap justify-content-lg-end gap-1">
                                        <span class="badge {{ $paymentStatusClass($order->payment_status) }} p-2 fs-12 fw-normal">
                                            {{ $paymentStatusLabel($order->payment_status) }}
                                        </span>

                                        <span class="badge {{ $orderStatusClass($order->status) }} p-2 fs-12 fw-normal">
                                            {{ $orderStatusLabel($order->status) }}
                                        </span>

                                        <a href="{{ url('/order-online/' . $order->id) }}" class="btn btn-outline-primary btn-sm mt-2">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border rounded-3">
                            <i class="material-symbols-outlined text-body fs-40 mb-2">shopping_cart</i>
                            <h6 class="fw-semibold mb-1">Belum ada order online</h6>
                            <p class="text-body mb-3 fs-13">Order dari halaman menu publik akan muncul di sini.</p>
                            <a href="{{ url('/menu') }}" class="btn btn-primary text-white" target="_blank">
                                Buka Menu Publik
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card bg-white border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Pembayaran Online Hari Ini</h3>
                        <p class="text-body mb-0 fs-13">Rekap metode pembayaran dari order online hari ini</p>
                    </div>

                    <a href="{{ route('reports.online-orders.index') }}" class="btn btn-outline-primary btn-sm">
                        Laporan
                    </a>
                </div>

                @forelse ($onlinePaymentSummary as $payment)
                    <div class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <span class="fw-medium">{{ $payment['label'] }}</span>
                                <span class="d-block fs-12 text-body">
                                    {{ $payment['total_order'] }} order
                                </span>
                            </div>

                            <div class="text-end">
                                <span class="fs-13 fw-semibold">{{ $rupiah($payment['amount']) }}</span>
                                <span class="d-block fs-12 text-body">{{ $payment['percent'] }}%</span>
                            </div>
                        </div>

                        <div class="koc-payment-progress">
                            <div class="koc-payment-progress-bar {{ $payment['class'] }}" style="width: {{ $payment['percent'] }}%;"></div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 border rounded-3">
                        <i class="material-symbols-outlined text-body fs-40 mb-2">payments</i>
                        <h6 class="fw-semibold mb-1">Belum ada pembayaran online hari ini</h6>
                        <p class="text-body mb-0 fs-13">Order online hari ini akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
                    
                    <div class="row g-4 mb-4">
                        <div class="col-xl-7">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Transaksi Terbaru</h3>
                                            <p class="text-body mb-0 fs-13">Data transaksi terakhir dari POS</p>
                                        </div>

                                        <a href="{{ route('sales.report') }}" class="btn btn-outline-primary btn-sm">
                                            Lihat Semua
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($latestSales as $sale)
                                            <div class="koc-list-card">
                                                <div class="row align-items-center g-3">
                                                    <div class="col-lg-5">
                                                        <div class="d-flex align-items-start">
                                                            <div class="koc-icon-box bg-primary bg-opacity-10 text-primary me-3">
                                                                <i class="material-symbols-outlined fs-20">receipt_long</i>
                                                            </div>

                                                            <div>
                                                                <h6 class="fw-semibold fs-14 mb-1">{{ $sale->invoice_no }}</h6>
                                                                <span class="fs-12 text-body">
                                                                    {{ $sale->customer_name ?: 'Customer Umum' }}
                                                                </span>
                                                                <div class="mt-2">
                                                                    <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                        {{ $sale->sale_date->format('d/m/Y H:i') }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-2 col-6">
                                                        <span class="d-block fs-12 text-body mb-1">Item</span>
                                                        <strong>{{ number_format($sale->items->sum('quantity'), 0, ',', '.') }}</strong>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <span class="d-block fs-12 text-body mb-1">Total</span>
                                                        <strong class="koc-price">{{ $rupiah($sale->total_amount) }}</strong>
                                                    </div>

                                                    <div class="col-lg-2">
                                                        <div class="d-flex justify-content-lg-end">
                                                            <a href="{{ route('pos.receipt', $sale) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                                                Struk
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">receipt_long</i>
                                                <h6 class="fw-semibold mb-1">Belum ada transaksi</h6>
                                                <p class="text-body mb-3 fs-13">Transaksi dari Kasir POS akan muncul di sini.</p>
                                                <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                                    Buka Kasir POS
                                                </a>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Stok Menipis</h3>
                                            <p class="text-body mb-0 fs-13">Produk dengan stok kurang/sama dari minimum</p>
                                        </div>

                                        <a href="{{ route('stocks.low') }}" class="btn btn-outline-danger btn-sm">
                                            Cek Stok
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($lowStockProducts as $product)
                                            <div class="koc-list-card">
                                                <div class="d-flex justify-content-between align-items-start gap-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="koc-icon-box bg-danger bg-opacity-10 text-danger me-3">
                                                            <i class="material-symbols-outlined fs-20">warning</i>
                                                        </div>

                                                        <div>
                                                            <h6 class="fw-semibold fs-14 mb-1">{{ $product->name }}</h6>
                                                            <span class="fs-12 text-body">
                                                                {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                                            </span>
                                                            <div class="mt-2">
                                                                <span class="badge bg-danger bg-opacity-10 text-danger p-2 fs-12 fw-normal">
                                                                    Sisa {{ $product->stock }} {{ $product->unit }}
                                                                </span>
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Min. {{ $product->minimum_stock }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-success fs-40 mb-2">check_circle</i>
                                                <h6 class="fw-semibold mb-1">Semua stok aman</h6>
                                                <p class="text-body mb-0 fs-13">Tidak ada produk yang sedang stok menipis.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-7">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Produk Terlaris</h3>
                                            <p class="text-body mb-0 fs-13">Berdasarkan item terjual dari transaksi POS</p>
                                        </div>

                                        <a href="{{ route('best-products.report') }}" class="btn btn-outline-primary btn-sm">
                                            Laporan
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($bestProducts as $product)
                                            <div class="koc-list-card">
                                                <div class="row align-items-center g-3">
                                                    <div class="col-lg-6">
                                                        <div class="d-flex align-items-start">
                                                            <div class="koc-icon-box bg-primary bg-opacity-10 text-primary me-3">
                                                                <i class="material-symbols-outlined fs-20">local_cafe</i>
                                                            </div>

                                                            <div>
                                                                <h6 class="fw-semibold fs-14 mb-1">{{ $product->product_name }}</h6>
                                                                <span class="fs-12 text-body">
                                                                    {{ $product->product?->category?->name ?? 'Tanpa Kategori' }}
                                                                </span>
                                                                <div class="mt-2">
                                                                    <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                        SKU: {{ $product->sku ?: '-' }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <span class="d-block fs-12 text-body mb-1">Terjual</span>
                                                        <strong>
                                                            {{ number_format($product->total_sold, 0, ',', '.') }}
                                                            {{ $product->unit }}
                                                        </strong>
                                                    </div>

                                                    <div class="col-lg-3 col-6">
                                                        <span class="d-block fs-12 text-body mb-1">Omzet</span>
                                                        <strong class="koc-price">{{ $rupiah($product->total_amount) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">shopping_bag</i>
                                                <h6 class="fw-semibold mb-1">Belum ada produk terjual</h6>
                                                <p class="text-body mb-0 fs-13">Produk terlaris akan muncul setelah ada transaksi POS.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Mutasi Stok Terbaru</h3>
                                            <p class="text-body mb-0 fs-13">Riwayat stok masuk/keluar terakhir</p>
                                        </div>

                                        <a href="{{ route('stocks.movements') }}" class="btn btn-outline-primary btn-sm">
                                            Lihat Mutasi
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($latestStockMovements as $movement)
                                            @php
                                                $movementClass = match ($movement->movement_type) {
                                                    'IN' => 'bg-success bg-opacity-10 text-success',
                                                    'OUT' => 'bg-warning bg-opacity-10 text-warning',
                                                    'ADJUSTMENT' => 'bg-info bg-opacity-10 text-info',
                                                    default => 'bg-light text-body',
                                                };

                                                $movementIcon = match ($movement->movement_type) {
                                                    'IN' => 'add_box',
                                                    'OUT' => 'indeterminate_check_box',
                                                    'ADJUSTMENT' => 'tune',
                                                    default => 'sync_alt',
                                                };
                                            @endphp

                                            <div class="koc-list-card">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-icon-box {{ $movementClass }} me-3">
                                                        <i class="material-symbols-outlined fs-20">{{ $movementIcon }}</i>
                                                    </div>

                                                    <div class="w-100">
                                                        <div class="d-flex justify-content-between gap-3">
                                                            <div>
                                                                <h6 class="fw-semibold fs-14 mb-1">
                                                                    {{ $movement->product?->name ?? 'Produk terhapus' }}
                                                                </h6>
                                                                <span class="fs-12 text-body">
                                                                    {{ $movement->movement_date->format('d/m/Y') }}
                                                                </span>
                                                            </div>

                                                            <strong class="{{ $movement->quantity_change >= 0 ? 'text-success' : 'text-danger' }}">
                                                                {{ $movement->quantity_change_label }}
                                                            </strong>
                                                        </div>

                                                        <div class="mt-2">
                                                            <span class="badge {{ $movementClass }} p-2 fs-12 fw-normal">
                                                                {{ $movement->movement_type_label }}
                                                            </span>

                                                            @if ($movement->reference_no)
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Ref: {{ $movement->reference_no }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">sync_alt</i>
                                                <h6 class="fw-semibold mb-1">Belum ada mutasi stok</h6>
                                                <p class="text-body mb-0 fs-13">Mutasi stok akan muncul setelah stok masuk/keluar dicatat.</p>
                                            </div>
                                        @endforelse
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-12">
                            <div class="card bg-white border-0 rounded-3">
                                <div class="card-body p-4">
                                    <div class="row g-4 align-items-center">
                                        <div class="col-lg-3 col-md-6">
                                            <span class="d-block text-body mb-1">Total Produk</span>
                                            <h5 class="mb-0">{{ number_format($totalProducts, 0, ',', '.') }}</h5>
                                        </div>

                                        <div class="col-lg-3 col-md-6">
                                            <span class="d-block text-body mb-1">Produk Aktif</span>
                                            <h5 class="mb-0">{{ number_format($activeProducts, 0, ',', '.') }}</h5>
                                        </div>

                                        <div class="col-lg-3 col-md-6">
                                            <span class="d-block text-body mb-1">Stok Aman</span>
                                            <h5 class="mb-0 text-success">{{ number_format($safeStockProducts, 0, ',', '.') }}</h5>
                                        </div>

                                        <div class="col-lg-3 col-md-6">
                                            <span class="d-block text-body mb-1">Stok Kosong</span>
                                            <h5 class="mb-0 text-danger">{{ number_format($emptyStockProducts, 0, ',', '.') }}</h5>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-grow-1"></div>

                @include('partials.footer')
            </div>
        </div>

        @include('partials.theme_settings')
        @include('partials.scripts')
    </body>
</html>

