<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laporan Laba Rugi - Kasir Online Cerdas</title>

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

            .koc-profit-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-profit-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-profit-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-profit-row:hover {
                background-color: #fafaff;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }

            .koc-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .koc-product-profit-item {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px;
                background-color: #ffffff;
            }

            .koc-product-profit-item:hover {
                background-color: #fafaff;
            }

            .koc-mini-label {
                font-size: 12px;
                color: #64748b;
                display: block;
                margin-bottom: 3px;
            }

            @media (max-width: 767.98px) {
                .koc-profit-row {
                    padding: 16px;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
            $percent = fn ($value) => number_format((float) $value, 2, ',', '.') . '%';

            $summaryCards = [
                [
                    'title' => 'Total Omzet',
                    'value' => $rupiah($totalOmzet),
                    'note' => 'Total nilai transaksi',
                    'icon' => 'point_of_sale',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'Total Modal / HPP',
                    'value' => $rupiah($totalModal),
                    'note' => 'Qty terjual x harga modal',
                    'icon' => 'payments',
                    'color' => 'bg-warning bg-opacity-10 text-warning',
                ],
                [
                    'title' => 'Laba Kotor',
                    'value' => $rupiah($labaKotor),
                    'note' => 'Penjualan produk - HPP',
                    'icon' => 'trending_up',
                    'color' => $labaKotor >= 0
                        ? 'bg-success bg-opacity-10 text-success'
                        : 'bg-danger bg-opacity-10 text-danger',
                ],
                [
                    'title' => 'Laba Bersih Sederhana',
                    'value' => $rupiah($labaBersih),
                    'note' => 'Laba kotor - diskon + pajak',
                    'icon' => 'account_balance_wallet',
                    'color' => $labaBersih >= 0
                        ? 'bg-success bg-opacity-10 text-success'
                        : 'bg-danger bg-opacity-10 text-danger',
                ],
            ];
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Laporan Laba Rugi</h3>
                            <p class="text-body mb-0">
                                Ringkasan omzet, HPP, laba kotor, diskon, pajak, dan laba bersih sederhana.
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
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Laporan</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Laba Rugi</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Filter Laporan</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Pilih periode transaksi dan metode pembayaran untuk melihat laba rugi.
                                    </p>
                                </div>
                            </div>

                            <div class="koc-filter-card">
                                <form action="{{ route('profit-loss.report') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-3 col-lg-3 col-md-6">
                                            <label class="form-label fs-13 fw-medium">Tanggal Awal</label>
                                            <input
                                                type="date"
                                                name="start_date"
                                                value="{{ $startDate }}"
                                                class="form-control"
                                            >
                                        </div>

                                        <div class="col-xl-3 col-lg-3 col-md-6">
                                            <label class="form-label fs-13 fw-medium">Tanggal Akhir</label>
                                            <input
                                                type="date"
                                                name="end_date"
                                                value="{{ $endDate }}"
                                                class="form-control"
                                            >
                                        </div>

                                        <div class="col-xl-3 col-lg-3 col-md-6">
                                            <label class="form-label fs-13 fw-medium">Metode Pembayaran</label>
                                            <select name="payment_method" class="form-select form-control">
                                                <option value="">Semua Metode</option>
                                                @foreach ($paymentMethods as $method)
                                                    <option value="{{ $method }}" @selected($paymentMethod === $method)>
                                                        {{ $method }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto mt-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($startDate || $endDate || $paymentMethod)
                                            <div class="col-xl-auto col-lg-auto col-md-auto mt-auto">
                                                <a href="{{ route('profit-loss.report') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        @foreach ($summaryCards as $card)
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white border-0 rounded-3 h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <span class="d-block text-body mb-2">{{ $card['title'] }}</span>
                                                <h3 class="fs-22 fw-semibold mb-1 koc-price">
                                                    {{ $card['value'] }}
                                                </h3>
                                                <p class="fs-13 text-body mb-0">{{ $card['note'] }}</p>
                                            </div>

                                            <div class="koc-summary-icon {{ $card['color'] }}">
                                                <i class="material-symbols-outlined">{{ $card['icon'] }}</i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger me-3">
                                            <i class="material-symbols-outlined fs-22">sell</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Total Diskon</span>
                                            <h4 class="fw-semibold mb-0 koc-price">{{ $rupiah($totalDiskon) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-info bg-opacity-10 text-info me-3">
                                            <i class="material-symbols-outlined">receipt_long</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Total Pajak</span>
                                            <h4 class="fw-semibold mb-0 koc-price">{{ $rupiah($totalPajak) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success me-3">
                                            <i class="material-symbols-outlined">percent</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Margin Laba</span>
                                            <h4 class="fw-semibold mb-0">{{ $percent($marginLaba) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">shopping_cart_checkout</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Rata-rata Transaksi</span>
                                            <h4 class="fw-semibold mb-0 koc-price">{{ $rupiah($rataRataTransaksi) }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-6 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">confirmation_number</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Jumlah Transaksi</span>
                                            <h4 class="fw-semibold mb-0">
                                                {{ number_format($jumlahTransaksi, 0, ',', '.') }}
                                            </h4>
                                            <p class="fs-13 text-body mb-0">Transaksi pada periode terpilih</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-info bg-opacity-10 text-info me-3">
                                            <i class="material-symbols-outlined">inventory</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Item Terjual</span>
                                            <h4 class="fw-semibold mb-0">
                                                {{ number_format($itemTerjual, 0, ',', '.') }}
                                            </h4>
                                            <p class="fs-13 text-body mb-0">Total kuantitas item terjual</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Ringkasan Transaksi</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Perhitungan laba sederhana berdasarkan invoice POS.
                                            </p>
                                        </div>

                                        <a href="{{ route('profit-loss.report') }}" class="btn btn-outline-primary">
                                            Laba Rugi
                                        </a>
                                    </div>

                                    <div class="koc-profit-list">
                                        @forelse ($transactionSummaries as $sale)
                                            <div class="koc-profit-row">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                                    <div class="d-flex align-items-start">
                                                        <div class="koc-profit-icon bg-primary bg-opacity-10 text-primary me-3">
                                                            <i class="material-symbols-outlined fs-22">receipt</i>
                                                        </div>

                                                        <div>
                                                            <h6 class="fw-semibold fs-15 mb-1">
                                                                {{ $sale->invoice_number }}
                                                            </h6>

                                                            <p class="text-body fs-13 mb-0">
                                                                {{ \Carbon\Carbon::parse($sale->created_at)->format('d/m/Y H:i') }}
                                                            </p>

                                                            <div class="koc-meta">
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Customer: {{ $sale->customer_name ?: 'Umum' }}
                                                                </span>

                                                                <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                                    {{ $sale->payment_method ?: 'Tidak diketahui' }}
                                                                </span>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <a
                                                        href="{{ url('/pos/struk/' . $sale->id) }}"
                                                        target="_blank"
                                                        class="btn btn-outline-primary btn-sm"
                                                    >
                                                        Lihat Struk
                                                    </a>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Omzet</span>
                                                        <strong class="koc-price">{{ $rupiah($sale->total_amount) }}</strong>
                                                    </div>

                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Modal / HPP</span>
                                                        <strong class="koc-price">{{ $rupiah($sale->total_modal) }}</strong>
                                                    </div>

                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Laba Kotor</span>
                                                        <strong class="koc-price {{ $sale->laba_kotor >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $rupiah($sale->laba_kotor) }}
                                                        </strong>
                                                    </div>

                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Diskon</span>
                                                        <strong class="koc-price">{{ $rupiah($sale->discount_amount) }}</strong>
                                                    </div>

                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Pajak</span>
                                                        <strong class="koc-price">{{ $rupiah($sale->tax_amount) }}</strong>
                                                    </div>

                                                    <div class="col-lg-4 col-md-6">
                                                        <span class="koc-mini-label">Laba Bersih Sederhana</span>
                                                        <strong class="koc-price {{ $sale->laba_bersih >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $rupiah($sale->laba_bersih) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 bg-white rounded-3 border">
                                                <div class="mb-3">
                                                    <i class="material-symbols-outlined text-body fs-40">receipt_long</i>
                                                </div>

                                                <h6 class="fw-semibold mb-1">Belum ada transaksi</h6>

                                                <p class="text-body mb-3">
                                                    Tidak ada transaksi pada filter laporan yang dipilih.
                                                </p>

                                                <a href="{{ route('profit-loss.report') }}" class="btn btn-primary text-white">
                                                    Reset Filter
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
                                            <h3 class="mb-1">Produk Penyumbang Laba</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Ringkasan laba kotor dan margin per produk.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="koc-profit-list">
                                        @forelse ($productProfitSummaries as $product)
                                            <div class="koc-product-profit-item">
                                                <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $product->product_name }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            Qty terjual:
                                                            {{ number_format($product->total_qty, 0, ',', '.') }}
                                                        </p>
                                                    </div>

                                                    <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                        Margin {{ $percent($product->margin_produk) }}
                                                    </span>
                                                </div>

                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <span class="koc-mini-label">Omzet Produk</span>
                                                        <strong class="koc-price">{{ $rupiah($product->total_omzet_produk) }}</strong>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <span class="koc-mini-label">Modal Produk</span>
                                                        <strong class="koc-price">{{ $rupiah($product->total_modal_produk) }}</strong>
                                                    </div>

                                                    <div class="col-12">
                                                        <span class="koc-mini-label">Laba Kotor Produk</span>
                                                        <strong class="koc-price {{ $product->laba_kotor_produk >= 0 ? 'text-success' : 'text-danger' }}">
                                                            {{ $rupiah($product->laba_kotor_produk) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 bg-white rounded-3 border">
                                                <div class="mb-3">
                                                    <i class="material-symbols-outlined text-body fs-40">inventory_2</i>
                                                </div>

                                                <h6 class="fw-semibold mb-1">Belum ada produk terjual</h6>

                                                <p class="text-body mb-0">
                                                    Produk penyumbang laba akan tampil setelah ada transaksi POS.
                                                </p>
                                            </div>
                                        @endforelse
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

