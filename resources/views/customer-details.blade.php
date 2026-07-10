<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Detail Pelanggan - Kasir Online Cerdas</title>

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

            .koc-sale-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px 18px;
                background-color: #ffffff;
            }

            .koc-sale-row:hover {
                background-color: #fafaff;
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
                            <h3 class="mb-1">Detail Pelanggan</h3>
                            <p class="text-body mb-0">
                                Profil pelanggan dan riwayat transaksi berdasarkan nama pelanggan.
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary text-white">
                                Edit Pelanggan
                            </a>

                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">person</i>
                                        </div>

                                        <div>
                                            <h4 class="fs-18 fw-semibold mb-1">{{ $customer->name }}</h4>
                                            <span class="badge {{ $customer->status_badge_class }} p-2 fs-12 fw-normal">
                                                {{ $customer->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Kode Pelanggan</span>
                                        <strong>{{ $customer->customer_code }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Nomor HP</span>
                                        <strong>{{ $customer->phone ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Email</span>
                                        <strong>{{ $customer->email ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Kota</span>
                                        <strong>{{ $customer->city ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Alamat</span>
                                        <strong>{{ $customer->address ?: '-' }}</strong>
                                    </div>

                                    <div>
                                        <span class="text-body d-block fs-13">Catatan</span>
                                        <strong>{{ $customer->note ?: '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="row g-4">
                                <div class="col-md-4">
                                    <div class="card bg-white border-0 rounded-3 h-100">
                                        <div class="card-body p-4">
                                            <span class="text-body d-block mb-2">Total Transaksi</span>
                                            <h3 class="fs-22 fw-semibold mb-1">
                                                {{ number_format($transactionCount, 0, ',', '.') }}
                                            </h3>
                                            <p class="fs-13 text-body mb-0">Berdasarkan nama pelanggan</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-white border-0 rounded-3 h-100">
                                        <div class="card-body p-4">
                                            <span class="text-body d-block mb-2">Total Omzet</span>
                                            <h3 class="fs-22 fw-semibold mb-1 koc-price">
                                                {{ $rupiah($totalOmzet) }}
                                            </h3>
                                            <p class="fs-13 text-body mb-0">Akumulasi transaksi pelanggan</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="card bg-white border-0 rounded-3 h-100">
                                        <div class="card-body p-4">
                                            <span class="text-body d-block mb-2">Transaksi Terakhir</span>
                                            <h3 class="fs-18 fw-semibold mb-1">
                                                {{ $lastSale?->invoice_no ?: '-' }}
                                            </h3>
                                            <p class="fs-13 text-body mb-0">
                                                {{ $lastSale?->sale_date?->format('d/m/Y H:i') ?: '-' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-white border-0 rounded-3 mt-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Riwayat Transaksi</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Transaksi POS dengan nama customer yang sama.
                                            </p>
                                        </div>

                                        <a href="{{ route('sales.report', ['q' => $customer->name]) }}" class="btn btn-outline-primary btn-sm">
                                            Lihat di Laporan
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($sales as $sale)
                                            <div class="koc-sale-row">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $sale->invoice_no }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-2">
                                                            {{ $sale->sale_date?->format('d/m/Y H:i') }}
                                                        </p>

                                                        <div class="d-flex flex-wrap gap-2">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $sale->payment_method ?: 'Tidak diketahui' }}
                                                            </span>

                                                            <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                                {{ $sale->status ?: 'Selesai' }}
                                                            </span>

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ number_format($sale->items->sum('quantity'), 0, ',', '.') }} item
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="text-lg-end">
                                                        <span class="text-body d-block fs-13 mb-1">Total</span>
                                                        <h5 class="fw-semibold mb-2 koc-price">
                                                            {{ $rupiah($sale->total_amount) }}
                                                        </h5>

                                                        <a href="{{ route('pos.receipt', $sale) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                                            Lihat Struk
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">receipt_long</i>
                                                <h6 class="fw-semibold mb-1">Belum ada transaksi</h6>
                                                <p class="text-body mb-0 fs-13">
                                                    Riwayat transaksi akan muncul jika nama pelanggan digunakan pada transaksi POS.
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-4">
                                        {{ $sales->links('pagination::bootstrap-5') }}
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