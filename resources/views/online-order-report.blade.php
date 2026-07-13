<!doctype html>
<html lang="id">
<head>
    @include('partials.styles')
    <title>Laporan Order Online - Kasir Online Cerdas</title>
</head>

<body>
    @include('partials.sidebar')

    <div class="container-fluid">
        <div class="main-content d-flex flex-column">
            @include('partials.header')

            <div class="main-content-container overflow-hidden">
                @php
                    $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
                @endphp

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Laporan Order Online</h3>
                        <p class="text-muted mb-0">
                            Rekap order online berdasarkan Tanggal Order Dibuat, pembayaran, stok, dan konversi penjualan.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('reports.online-orders.export', request()->query()) }}"
                           class="btn btn-success">
                            Export CSV
                        </a>

                        <a href="{{ route('reports.online-orders.index') }}" class="btn btn-outline-secondary">
                            Reset
                        </a>
                    </div>
                </div>

                <div class="row">
                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Total Order Online</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['total_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Nilai Seluruh Order</span>
                                <h3 class="mb-0 mt-2">{{ $rupiah($summary['total_order_value']) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Nilai Sudah Dibayar</span>
                                <h3 class="mb-0 mt-2">{{ $rupiah($summary['paid_order_value']) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Omzet Order Selesai</span>
                                <h3 class="mb-0 mt-2">{{ $rupiah($summary['completed_revenue']) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Nilai Order Dibatalkan</span>
                                <h3 class="mb-0 mt-2">{{ $rupiah($summary['cancelled_value']) }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Sudah Masuk Penjualan</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['entered_sales'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Anomali Belum Masuk Penjualan</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['conversion_anomalies'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    @foreach($statusSummary as $status)
                        <div class="col-xxl-2 col-md-4 col-sm-6">
                            <div class="card border-0 rounded-3 bg-white mb-4">
                                <div class="card-body">
                                    <span class="text-muted">{{ $status['label'] }}</span>
                                    <h4 class="mb-0 mt-2">{{ number_format($status['count'], 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="row">
                    @foreach($paymentSummary as $payment)
                        <div class="col-xxl-3 col-md-4 col-sm-6">
                            <div class="card border-0 rounded-3 bg-white mb-4">
                                <div class="card-body">
                                    <span class="text-muted">{{ $payment['label'] }}</span>
                                    <h4 class="mb-0 mt-2">{{ number_format($payment['count'], 0, ',', '.') }}</h4>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card border-0 rounded-3 bg-white mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('reports.online-orders.index') }}">
                            <div class="row g-3">
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Tanggal Order Dibuat Awal</label>
                                    <input type="date"
                                           name="start_date"
                                           value="{{ $filters['start_date'] ?? '' }}"
                                           class="form-control">
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Tanggal Order Dibuat Akhir</label>
                                    <input type="date"
                                           name="end_date"
                                           value="{{ $filters['end_date'] ?? '' }}"
                                           class="form-control">
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Status Order</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua</option>
                                        @foreach($statusOptions as $value => $text)
                                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>
                                                {{ $text }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Status Pembayaran</label>
                                    <select name="payment_status" class="form-select">
                                        <option value="">Semua</option>
                                        @foreach($paymentStatusOptions as $value => $text)
                                            <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>
                                                {{ $text }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Metode Pembayaran</label>
                                    <select name="payment_method" class="form-select">
                                        <option value="">Semua</option>
                                        @foreach($paymentMethodOptions as $value => $text)
                                            <option value="{{ $value }}" @selected(($filters['payment_method'] ?? '') === $value)>
                                                {{ $text }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Cari</label>
                                    <input type="text"
                                           name="search"
                                           value="{{ $filters['search'] ?? '' }}"
                                           class="form-control"
                                           placeholder="Order/customer/invoice">
                                </div>

                                <div class="col-12">
                                    <p class="text-muted fs-13 mb-2">
                                        Filter periode memakai Tanggal Order Dibuat.
                                    </p>

                                    <button type="submit" class="btn btn-primary">
                                        Terapkan Filter
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="row">
                    @foreach($paymentRecap as $recap)
                        <div class="col-xl-3 col-sm-6">
                            <div class="card border-0 rounded-3 bg-white mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                        <div>
                                            <span class="text-muted">{{ $recap['label'] }}</span>
                                            <h4 class="mb-1 mt-2">{{ $rupiah($recap['all_value']) }}</h4>
                                            <p class="mb-1 text-muted">
                                                {{ number_format($recap['orders_count'], 0, ',', '.') }} seluruh order
                                            </p>
                                            <p class="mb-1 text-muted">
                                                {{ number_format($recap['paid_count'], 0, ',', '.') }} dibayar -
                                                {{ $rupiah($recap['paid_value']) }}
                                            </p>
                                            <p class="mb-0 text-muted">
                                                {{ number_format($recap['completed_count'], 0, ',', '.') }} selesai
                                            </p>
                                        </div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            Rekap
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card border-0 rounded-3 bg-white mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Indikator Konsistensi</h5>
                                <p class="text-muted mb-0">
                                    Deteksi anomali saja, tidak mengubah data otomatis.
                                </p>
                            </div>
                        </div>

                        <div class="row">
                            @foreach($consistencyIndicators as $indicator)
                                <div class="col-xl col-md-4 col-sm-6">
                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="text-muted">{{ $indicator['label'] }}</span>
                                        <h4 class="mb-0 mt-2">{{ number_format($indicator['count'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-3 bg-white mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div>
                                <h5 class="mb-1">Daftar Order Online</h5>
                                <p class="text-muted mb-0">
                                    Menampilkan order sesuai filter laporan, terbaru berdasarkan Tanggal Order Dibuat.
                                </p>
                            </div>
                        </div>

                        @forelse($orders as $order)
                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h5 class="mb-0">{{ $order->order_no }}</h5>

                                            <span class="badge {{ $order->payment_status_class }}">
                                                {{ $order->payment_status_label }}
                                            </span>

                                            <span class="badge {{ $order->status_class }}">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>

                                        <p class="mb-1 text-muted">
                                            {{ optional($order->created_at)->format('d/m/Y H:i') }}
                                        </p>

                                        <p class="mb-1">
                                            <strong>{{ $order->customer_name ?: '-' }}</strong>
                                            <span class="text-muted">
                                                - {{ $order->customer_phone ?: '-' }}
                                            </span>
                                        </p>

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $order->payment_method_label }}
                                            </span>

                                            <span class="badge {{ $order->stock_status_class }}">
                                                {{ $order->stock_status_label }}
                                            </span>

                                            <span class="badge {{ $order->sale_conversion_status_class }}">
                                                {{ $order->sale_conversion_status_label }}
                                            </span>
                                        </div>

                                        @if($order->sale_invoice_no)
                                            <p class="mb-0 mt-2 text-muted">
                                                Invoice Penjualan: {{ $order->sale_invoice_no }}
                                            </p>
                                        @endif

                                        @if(! empty($order->consistency_indicators))
                                            <div class="d-flex flex-wrap gap-2 mt-2">
                                                @foreach($order->consistency_indicators as $indicator)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                                        {{ $indicator }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        @endif
                                    </div>

                                    <div class="text-lg-end">
                                        <p class="text-muted mb-1">Total</p>
                                        <h4 class="mb-3">
                                            {{ $rupiah($order->total_amount) }}
                                        </h4>

                                        <a href="{{ route('online-orders.show', $order) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <h5 class="mb-1">Belum ada order online</h5>
                                <p class="text-muted mb-0">
                                    Data tidak ditemukan untuk filter yang dipilih.
                                </p>
                            </div>
                        @endforelse

                        <div class="mt-4">
                            {{ $orders->links() }}
                        </div>
                    </div>
                </div>
            </div>

            @include('partials.footer')
        </div>
    </div>

    @include('partials.theme_settings')
    @include('partials.scripts')
</body>
</html>
