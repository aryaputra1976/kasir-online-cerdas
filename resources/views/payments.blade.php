<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Konfirmasi Pembayaran - Kasir Online Cerdas</title>
        @include('partials.styles')

        <style>
            .koc-summary-icon {
                width: 48px;
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-payment-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-payment-row:hover {
                background-color: #fafaff;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Konfirmasi Pembayaran</h3>
                            <p class="text-body mb-0">
                                Periksa bukti pembayaran customer dan lakukan konfirmasi manual.
                            </p>
                        </div>

                        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                            <ol class="breadcrumb align-items-center mb-0 lh-1">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                                        <i class="ri-home-4-line fs-18 text-primary me-1"></i>
                                        <span class="text-secondary fw-medium hover">Dashboard</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Transaksi</li>
                                <li class="breadcrumb-item active">Pembayaran</li>
                            </ol>
                        </nav>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="material-symbols-outlined">hourglass_top</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Menunggu</span>
                                        <h4 class="mb-0">{{ number_format($summary['waiting'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="koc-summary-icon bg-success bg-opacity-10 text-success me-3">
                                        <i class="material-symbols-outlined">check_circle</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Dibayar</span>
                                        <h4 class="mb-0">{{ number_format($summary['paid'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger me-3">
                                        <i class="material-symbols-outlined">cancel</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Ditolak</span>
                                        <h4 class="mb-0">{{ number_format($summary['rejected'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="koc-summary-icon bg-secondary bg-opacity-10 text-secondary me-3">
                                        <i class="material-symbols-outlined">payments</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Belum Dibayar</span>
                                        <h4 class="mb-0">{{ number_format($summary['unpaid'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h3 class="mb-3">Filter Pembayaran</h3>

                            <div class="koc-filter-card">
                                <form action="{{ route('payments.index') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-4 col-md-6">
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Cari order, nama, nomor HP..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <select name="payment_status" class="form-select form-control">
                                                <option value="">Semua Status</option>
                                                <option value="WAITING_CONFIRMATION" @selected($paymentStatus === 'WAITING_CONFIRMATION')>Menunggu Konfirmasi</option>
                                                <option value="PAID" @selected($paymentStatus === 'PAID')>Dibayar</option>
                                                <option value="REJECTED" @selected($paymentStatus === 'REJECTED')>Ditolak</option>
                                                <option value="UNPAID" @selected($paymentStatus === 'UNPAID')>Belum Dibayar</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <select name="payment_method" class="form-select form-control">
                                                <option value="">Semua Metode</option>
                                                <option value="CASH" @selected($paymentMethod === 'CASH')>Tunai / COD</option>
                                                <option value="QRIS" @selected($paymentMethod === 'QRIS')>QRIS</option>
                                                <option value="TRANSFER" @selected($paymentMethod === 'TRANSFER')>Transfer</option>
                                                <option value="EDC" @selected($paymentMethod === 'EDC')>EDC / Kartu</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        <div class="col-xl-auto col-md-auto">
                                            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary w-100">
                                                Reset
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Daftar Pembayaran</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Bukti pembayaran customer yang perlu diperiksa.
                                    </p>
                                </div>

                                <a href="{{ route('online-orders.index') }}" class="btn btn-outline-primary">
                                    Order Online
                                </a>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @forelse ($orders as $order)
                                    <div class="koc-payment-row">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                            <div>
                                                <h5 class="mb-1">{{ $order->order_no }}</h5>
                                                <p class="text-body mb-1">
                                                    {{ $order->customer_name }}
                                                    @if ($order->customer_phone)
                                                        · {{ $order->customer_phone }}
                                                    @endif
                                                </p>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                                        {{ $order->payment_status_label }}
                                                    </span>

                                                    <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                        {{ $order->payment_method_label }}
                                                    </span>

                                                    @if ($order->payment_method === \App\Models\Sale::PAYMENT_CASH)
                                                        <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                            COD / Tanpa Bukti
                                                        </span>
                                                    @elseif ($order->payment_proof_path)
                                                        <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                            Bukti Ada
                                                        </span>
                                                    @else
                                                        <span class="badge bg-secondary bg-opacity-10 text-secondary p-2 fs-12 fw-normal">
                                                            Belum Ada Bukti
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                <h5 class="koc-price mb-2">{{ $rupiah($order->total_amount) }}</h5>
                                                <a href="{{ route('payments.show', $order) }}" class="btn btn-primary text-white btn-sm">
                                                    Periksa
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <i class="material-symbols-outlined text-body fs-40 mb-2">payments</i>
                                        <h6 class="fw-semibold mb-1">Data pembayaran tidak ditemukan</h6>
                                        <p class="text-body mb-0">Tidak ada pembayaran pada filter ini.</p>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $orders->count() }} dari {{ $orders->total() }} pembayaran
                                    </span>

                                    <div>
                                        {{ $orders->links('pagination::bootstrap-5') }}
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