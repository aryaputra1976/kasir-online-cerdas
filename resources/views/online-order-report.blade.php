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

                    $read = function ($order, $column, $default = '-') {
                        if (!$column) {
                            return $default;
                        }

                        $value = $order->{$column} ?? null;

                        return $value !== null && $value !== '' ? $value : $default;
                    };

                    $label = function ($value, $type = null) {
                        if ($value === null || $value === '') {
                            return '-';
                        }

                        $value = (string) $value;
                        $key = strtolower($value);

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

                        return $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', $value));
                    };

                    $badgeClass = function ($value, $type = null) {
                        $key = strtolower((string) $value);

                        if (in_array($key, ['paid', 'dibayar', 'completed', 'selesai', 'done', 'complete'])) {
                            return 'bg-success bg-opacity-10 text-success';
                        }

                        if (in_array($key, ['pending', 'waiting_confirmation', 'menunggu_konfirmasi', 'processing', 'diproses'])) {
                            return 'bg-warning bg-opacity-10 text-warning';
                        }

                        if (in_array($key, ['cancelled', 'canceled', 'dibatalkan', 'batal', 'rejected', 'ditolak'])) {
                            return 'bg-danger bg-opacity-10 text-danger';
                        }

                        if (in_array($key, ['unpaid', 'belum_dibayar'])) {
                            return 'bg-secondary bg-opacity-10 text-secondary';
                        }

                        return 'bg-primary bg-opacity-10 text-primary';
                    };

                    $stockStatus = function ($order) use ($columns, $read, $label) {
                        if ($columns['stock_status']) {
                            return $label($read($order, $columns['stock_status']));
                        }

                        if ($columns['stock_deducted_at'] && $read($order, $columns['stock_deducted_at'], null)) {
                            return 'Stok Sudah Dikurangi';
                        }

                        if ($columns['stock_deducted_flag'] && (bool) $read($order, $columns['stock_deducted_flag'], false)) {
                            return 'Stok Sudah Dikurangi';
                        }

                        return 'Belum Dikurangi';
                    };

                    $stockBadgeClass = function ($text) {
                        return $text === 'Stok Sudah Dikurangi'
                            ? 'bg-success bg-opacity-10 text-success'
                            : 'bg-secondary bg-opacity-10 text-secondary';
                    };
                @endphp

                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Laporan Order Online</h3>
                        <p class="text-muted mb-0">
                            Rekap order online, status pembayaran, omzet, dan konversi ke penjualan POS.
                        </p>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ url('/laporan/order-online/export?' . http_build_query(request()->query())) }}"
                           class="btn btn-success">
                            Export CSV
                        </a>

                        <a href="{{ url('/laporan/order-online') }}" class="btn btn-outline-secondary">
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
                                <span class="text-muted">Order Baru</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['new_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Diproses</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['processing_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Selesai</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['completed_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Dibatalkan</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['cancelled_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Belum Dibayar</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['unpaid_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Menunggu Konfirmasi</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['waiting_confirmation_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Dibayar</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['paid_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Ditolak</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['rejected_orders'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>

                    <div class="col-xxl-3 col-md-4 col-sm-6">
                        <div class="card border-0 rounded-3 bg-white mb-4">
                            <div class="card-body">
                                <span class="text-muted">Omzet Order Online</span>
                                <h3 class="mb-0 mt-2">{{ $rupiah($summary['online_revenue']) }}</h3>
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
                                <span class="text-muted">Belum Masuk Penjualan</span>
                                <h3 class="mb-0 mt-2">{{ number_format($summary['not_entered_sales'], 0, ',', '.') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 rounded-3 bg-white mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ url('/laporan/order-online') }}">
                            <div class="row g-3">
                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Tanggal Awal</label>
                                    <input type="date"
                                           name="start_date"
                                           value="{{ $filters['start_date'] }}"
                                           class="form-control">
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Tanggal Akhir</label>
                                    <input type="date"
                                           name="end_date"
                                           value="{{ $filters['end_date'] }}"
                                           class="form-control">
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Status Order</label>
                                    <select name="status" class="form-select">
                                        <option value="">Semua</option>
                                        @foreach($statusOptions as $value => $text)
                                            <option value="{{ $value }}" @selected($filters['status'] === $value)>
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
                                            <option value="{{ $value }}" @selected($filters['payment_status'] === $value)>
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
                                            <option value="{{ $value }}" @selected($filters['payment_method'] === $value)>
                                                {{ $text }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-2 col-md-4">
                                    <label class="form-label">Cari</label>
                                    <input type="text"
                                           name="search"
                                           value="{{ $filters['search'] }}"
                                           class="form-control"
                                           placeholder="Order/customer/HP">
                                </div>

                                <div class="col-12">
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
                                            <h4 class="mb-1 mt-2">{{ $rupiah($recap['total_revenue']) }}</h4>
                                            <p class="mb-0 text-muted">
                                                {{ number_format($recap['orders_count'], 0, ',', '.') }} order
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
                                <h5 class="mb-1">Daftar Order Online</h5>
                                <p class="text-muted mb-0">
                                    Menampilkan order sesuai filter laporan.
                                </p>
                            </div>
                        </div>

                        @forelse($orders as $order)
                            @php
                                $orderNumber = $read($order, $columns['order_number'], 'ORD-' . $order->id);
                                $paymentStatus = $read($order, $columns['payment_status'], '-');
                                $orderStatus = $read($order, $columns['status'], '-');
                                $paymentMethod = $read($order, $columns['payment_method'], '-');
                                $stockText = $stockStatus($order);
                                $saleId = $read($order, $columns['sale_id'], null);
                                $saleInvoice = $saleId ? ($saleInvoices[$saleId] ?? '-') : '-';
                                $trackingToken = $read($order, $columns['tracking_token'], null);
                            @endphp

                            <div class="border rounded-3 p-3 mb-3">
                                <div class="d-flex flex-wrap justify-content-between gap-3">
                                    <div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                                            <h5 class="mb-0">{{ $orderNumber }}</h5>

                                            <span class="badge {{ $badgeClass($paymentStatus, 'payment_status') }}">
                                                {{ $label($paymentStatus, 'payment_status') }}
                                            </span>

                                            <span class="badge {{ $badgeClass($orderStatus, 'order_status') }}">
                                                {{ $label($orderStatus, 'order_status') }}
                                            </span>
                                        </div>

                                        <p class="mb-1 text-muted">
                                            {{ optional($order->created_at)->format('d/m/Y H:i') }}
                                        </p>

                                        <p class="mb-1">
                                            <strong>{{ $read($order, $columns['customer_name'], '-') }}</strong>
                                            <span class="text-muted">
                                                — {{ $read($order, $columns['customer_phone'], '-') }}
                                            </span>
                                        </p>

                                        <div class="d-flex flex-wrap gap-2 mt-2">
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $label($paymentMethod, 'payment_method') }}
                                            </span>

                                            <span class="badge {{ $stockBadgeClass($stockText) }}">
                                                {{ $stockText }}
                                            </span>

                                            @if($saleId)
                                                <span class="badge bg-success bg-opacity-10 text-success">
                                                    Sudah Masuk Penjualan
                                                </span>
                                            @else
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                    Belum Masuk Penjualan
                                                </span>
                                            @endif
                                        </div>

                                        @if($saleId)
                                            <p class="mb-0 mt-2 text-muted">
                                                Invoice Penjualan: {{ $saleInvoice }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="text-lg-end">
                                        <p class="text-muted mb-1">Total</p>
                                        <h4 class="mb-3">
                                            {{ $rupiah($read($order, $columns['total'], 0)) }}
                                        </h4>

                                        <div class="d-flex flex-wrap justify-content-lg-end gap-2">
                                            <a href="{{ url('/order-online/' . $order->id) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                Detail
                                            </a>

                                            @if($trackingToken)
                                                <a href="{{ url('/tracking/' . $trackingToken) }}"
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    Tracking
                                                </a>
                                            @endif
                                        </div>
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