<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laporan Penjualan Detail - Kasir Online Cerdas</title>

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

            .koc-sale-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-sale-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-sale-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-sale-row:hover {
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

            .koc-item-row {
                border: 1px solid #eef0f7;
                border-radius: 12px;
                padding: 12px 14px;
                background-color: #fbfcff;
            }

            .koc-mini-label {
                font-size: 12px;
                color: #64748b;
                display: block;
                margin-bottom: 3px;
            }

            @media (max-width: 767.98px) {
                .koc-sale-row {
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

            $summaryCards = [
                [
                    'title' => 'Total Omzet',
                    'value' => $rupiah($totalOmzet),
                    'note' => 'Total nilai transaksi',
                    'icon' => 'point_of_sale',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'Subtotal Produk',
                    'value' => $rupiah($subtotalProduk),
                    'note' => 'Akumulasi subtotal item',
                    'icon' => 'shopping_bag',
                    'color' => 'bg-info bg-opacity-10 text-info',
                ],
                [
                    'title' => 'Total Diskon',
                    'value' => $rupiah($totalDiskon),
                    'note' => 'Akumulasi diskon transaksi',
                    'icon' => 'sell',
                    'color' => 'bg-danger bg-opacity-10 text-danger',
                ],
                [
                    'title' => 'Total Pajak',
                    'value' => $rupiah($totalPajak),
                    'note' => 'Akumulasi pajak transaksi',
                    'icon' => 'receipt_long',
                    'color' => 'bg-warning bg-opacity-10 text-warning',
                ],
            ];
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Laporan Penjualan Detail</h3>
                            <p class="text-body mb-0">
                                Detail transaksi POS, item terjual, pembayaran, dan export laporan penjualan.
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
                                    <span class="fw-medium">Penjualan</span>
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
                                        Filter berdasarkan periode, metode pembayaran, invoice, customer, atau produk.
                                    </p>
                                </div>

                                <a
                                    href="{{ route('reports.sales.export', request()->query()) }}"
                                    class="btn btn-success text-white"
                                >
                                    <i class="ri-file-excel-2-line me-1"></i>
                                    Export Excel
                                </a>
                            </div>

                            <div class="koc-filter-card">
                                <form action="{{ route('reports.sales') }}" method="get">
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

                                        <div class="col-xl-3 col-lg-3 col-md-6">
                                            <label class="form-label fs-13 fw-medium">Pencarian</label>
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Invoice, customer, produk..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary w-100">
                                                Reset
                                            </a>
                                        </div>
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
                        <div class="col-xl-4 col-md-6">
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

                        <div class="col-xl-4 col-md-6">
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

                        <div class="col-xl-4 col-md-12">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success me-3">
                                            <i class="material-symbols-outlined">shopping_cart_checkout</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Rata-rata Transaksi</span>
                                            <h4 class="fw-semibold mb-0 koc-price">
                                                {{ $rupiah($rataRataTransaksi) }}
                                            </h4>
                                            <p class="fs-13 text-body mb-0">Omzet / jumlah transaksi</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Daftar Transaksi Penjualan</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Menampilkan transaksi POS beserta detail item yang terjual.
                                    </p>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('reports.profit-loss') }}" class="btn btn-outline-primary">
                                        Laba Rugi
                                    </a>

                                    <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                        Kasir POS
                                    </a>
                                </div>
                            </div>

                            <div class="koc-sale-list">
                                @forelse ($sales as $sale)
                                    @php
                                        $saleItems = $itemsBySale->get($sale->id, collect());
                                    @endphp

                                    <div class="koc-sale-row">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-3">
                                            <div class="d-flex align-items-start">
                                                <div class="koc-sale-icon bg-primary bg-opacity-10 text-primary me-3">
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

                                                        <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                            {{ $sale->status ?: 'Selesai' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex gap-2 flex-wrap">
                                                <a
                                                    href="{{ url('/pos/struk/' . $sale->id) }}"
                                                    target="_blank"
                                                    class="btn btn-outline-primary btn-sm"
                                                >
                                                    Lihat Struk
                                                </a>
                                            </div>
                                        </div>

                                        <div class="row g-3 mb-3">
                                            <div class="col-lg-3 col-md-6">
                                                <span class="koc-mini-label">Total Transaksi</span>
                                                <strong class="koc-price">{{ $rupiah($sale->total_amount) }}</strong>
                                            </div>

                                            <div class="col-lg-3 col-md-6">
                                                <span class="koc-mini-label">Subtotal Produk</span>
                                                <strong class="koc-price">{{ $rupiah($sale->subtotal_produk) }}</strong>
                                            </div>

                                            <div class="col-lg-2 col-md-6">
                                                <span class="koc-mini-label">Diskon</span>
                                                <strong class="koc-price">{{ $rupiah($sale->discount_amount) }}</strong>
                                            </div>

                                            <div class="col-lg-2 col-md-6">
                                                <span class="koc-mini-label">Pajak</span>
                                                <strong class="koc-price">{{ $rupiah($sale->tax_amount) }}</strong>
                                            </div>

                                            <div class="col-lg-2 col-md-6">
                                                <span class="koc-mini-label">Item</span>
                                                <strong>{{ number_format($sale->total_qty, 0, ',', '.') }}</strong>
                                            </div>
                                        </div>

                                        <div class="row g-2">
                                            @forelse ($saleItems as $item)
                                                <div class="col-12">
                                                    <div class="koc-item-row">
                                                        <div class="row g-3 align-items-center">
                                                            <div class="col-lg-5 col-md-12">
                                                                <h6 class="fw-semibold fs-14 mb-1">
                                                                    {{ $item->product_name }}
                                                                </h6>
                                                                <p class="text-body fs-13 mb-0">
                                                                    SKU: {{ $item->product_sku ?: '-' }}
                                                                </p>
                                                            </div>

                                                            <div class="col-lg-2 col-md-4">
                                                                <span class="koc-mini-label">Qty</span>
                                                                <strong>{{ number_format($item->quantity, 0, ',', '.') }}</strong>
                                                            </div>

                                                            <div class="col-lg-2 col-md-4">
                                                                <span class="koc-mini-label">Harga</span>
                                                                <strong class="koc-price">{{ $rupiah($item->item_price) }}</strong>
                                                            </div>

                                                            <div class="col-lg-3 col-md-4">
                                                                <span class="koc-mini-label">Subtotal</span>
                                                                <strong class="koc-price">{{ $rupiah($item->subtotal_amount) }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-12">
                                                    <div class="koc-item-row">
                                                        <p class="text-body mb-0 fs-13">
                                                            Detail item tidak ditemukan.
                                                        </p>
                                                    </div>
                                                </div>
                                            @endforelse
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <div class="mb-3">
                                            <i class="material-symbols-outlined text-body fs-40">receipt_long</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Data penjualan tidak ditemukan</h6>

                                        <p class="text-body mb-3">
                                            Ubah filter pencarian atau lakukan transaksi POS terlebih dahulu.
                                        </p>

                                        <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                            Buka Kasir POS
                                        </a>
                                    </div>
                                @endforelse

                                @if ($sales->hasPages())
                                    <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                        <span class="fs-13 fw-medium">
                                            Menampilkan {{ $sales->count() }} dari {{ $sales->total() }} transaksi
                                        </span>

                                        <div>
                                            {{ $sales->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                @else
                                    <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                        <span class="fs-13 fw-medium">
                                            Menampilkan {{ $sales->count() }} dari {{ $sales->total() }} transaksi
                                        </span>
                                    </div>
                                @endif
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