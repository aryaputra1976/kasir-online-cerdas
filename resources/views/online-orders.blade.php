<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Order Online - Kasir Online Cerdas</title>
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

            .koc-order-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-order-row:hover {
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
            $currentUser = auth()->user();
            $canManagePayment = $currentUser?->hasAnyRole([
                \App\Models\User::ROLE_OWNER,
                \App\Models\User::ROLE_ADMIN,
            ]) ?? false;
            $homeRoute = $canManagePayment ? route('dashboard') : route('pos.index');
            $homeLabel = $canManagePayment ? 'Dashboard' : 'Kasir POS';
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Order Online</h3>
                            <p class="text-body mb-0">
                                Daftar order online dan status pembayaran customer.
                            </p>
                        </div>

                        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                            <ol class="breadcrumb align-items-center mb-0 lh-1">
                                <li class="breadcrumb-item">
                                    <a href="{{ $homeRoute }}" class="d-flex align-items-center text-decoration-none">
                                        <i class="ri-home-4-line fs-18 text-primary me-1"></i>
                                        <span class="text-secondary fw-medium hover">{{ $homeLabel }}</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active">Transaksi</li>
                                <li class="breadcrumb-item active">Order Online</li>
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
                                    <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                                        <i class="material-symbols-outlined">shopping_cart</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Total Order</span>
                                        <h4 class="mb-0">{{ number_format($summary['total'], 0, ',', '.') }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4 d-flex align-items-center">
                                    <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning me-3">
                                        <i class="material-symbols-outlined">hourglass_top</i>
                                    </div>
                                    <div>
                                        <span class="text-body d-block">Menunggu Konfirmasi</span>
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
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <h3 class="mb-3">Filter Order</h3>

                            <div class="koc-filter-card">
                                <form action="{{ route('online-orders.index') }}" method="get">
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
                                                <option value="">Semua Pembayaran</option>
                                                <option value="UNPAID" @selected($paymentStatus === 'UNPAID')>Belum Dibayar</option>
                                                <option value="WAITING_CONFIRMATION" @selected($paymentStatus === 'WAITING_CONFIRMATION')>Menunggu Konfirmasi</option>
                                                <option value="PAID" @selected($paymentStatus === 'PAID')>Dibayar</option>
                                                <option value="REJECTED" @selected($paymentStatus === 'REJECTED')>Ditolak</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-3 col-md-6">
                                            <select name="status" class="form-select form-control">
                                                <option value="">Semua Status Order</option>
                                                <option value="NEW" @selected($status === 'NEW')>Order Baru</option>
                                                <option value="CONFIRMED" @selected($status === 'CONFIRMED')>Dikonfirmasi</option>
                                                <option value="PROCESSING" @selected($status === 'PROCESSING')>Diproses</option>
                                                <option value="COMPLETED" @selected($status === 'COMPLETED')>Selesai</option>
                                                <option value="CANCELLED" @selected($status === 'CANCELLED')>Dibatalkan</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        <div class="col-xl-auto col-md-auto">
                                            <a href="{{ route('online-orders.index') }}" class="btn btn-outline-secondary w-100">
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
                                    <h3 class="mb-1">Daftar Order</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Order online yang masuk akan tampil di sini.
                                    </p>
                                </div>

                                @if ($canManagePayment)
                                    <a href="{{ route('payments.index') }}" class="btn btn-primary text-white">
                                        Konfirmasi Pembayaran
                                    </a>
                                @endif
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @forelse ($orders as $order)
                                    <div class="koc-order-row">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                            <div>
                                                <h5 class="mb-1">{{ $order->order_no }}</h5>
                                                <p class="text-body mb-1">
                                                    {{ $order->customer_name }}
                                                    @if ($order->customer_phone)
                                                        Â· {{ $order->customer_phone }}
                                                    @endif
                                                </p>

                                                <div class="d-flex flex-wrap gap-2">
                                                    <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                                        {{ $order->payment_status_label }}
                                                    </span>

                                                    <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                        {{ $order->payment_method_label }}
                                                    </span>

                                                    <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                        {{ $order->status_label }}
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="text-end">
                                                <h5 class="koc-price mb-2">{{ $rupiah($order->total_amount) }}</h5>
                                                <div class="d-flex gap-2 flex-wrap justify-content-end">
                                                    <a href="{{ route('online-orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                                        Detail
                                                    </a>
                                                    @if ($canManagePayment)
                                                        <a href="{{ route('payments.show', $order) }}" class="btn btn-outline-success btn-sm">
                                                            Pembayaran
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3 fs-13 text-body">
                                            Link tracking:
                                            <a href="{{ route('public.tracking', $order->tracking_token) }}" target="_blank">
                                                {{ route('public.tracking', $order->tracking_token) }}
                                            </a>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <i class="material-symbols-outlined text-body fs-40 mb-2">shopping_cart</i>
                                        <h6 class="fw-semibold mb-1">Order online belum ada</h6>
                                        <p class="text-body mb-0">Data order akan tampil setelah customer membuat order.</p>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $orders->count() }} dari {{ $orders->total() }} order
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
