<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laporan Produk Terlaris - Kasir Online Cerdas</title>

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

            .koc-product-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-product-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-product-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-product-row:hover {
                background-color: #fafaff;
            }

            .koc-product-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }

            .koc-progress {
                height: 8px;
                border-radius: 999px;
                background-color: #edf0f7;
                overflow: hidden;
            }

            .koc-progress-bar {
                height: 100%;
                border-radius: 999px;
                background: linear-gradient(90deg, #605dff 0%, #8b5cf6 100%);
            }

            @media (max-width: 767.98px) {
                .koc-product-row {
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
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Laporan Produk Terlaris</h3>
                            <p class="text-body mb-0">
                                Analisis produk dengan penjualan terbanyak berdasarkan transaksi POS.
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
                                    <span class="fw-medium">Produk Terlaris</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Total Omzet Produk</span>
                                            <h3 class="fs-22 fw-semibold mb-0 koc-price">
                                                {{ $rupiah($totalOmzet) }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success">
                                            <i class="material-symbols-outlined">payments</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Item Terjual</span>
                                            <h3 class="fs-22 fw-semibold mb-0">
                                                {{ number_format($totalItemsSold, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="material-symbols-outlined">shopping_bag</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Produk Terjual</span>
                                            <h3 class="fs-22 fw-semibold mb-0">
                                                {{ number_format($totalProductsSold, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-info bg-opacity-10 text-info">
                                            <i class="material-symbols-outlined">inventory_2</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Total Transaksi</span>
                                            <h3 class="fs-22 fw-semibold mb-0">
                                                {{ number_format($totalTransactions, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="material-symbols-outlined">receipt_long</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($topProduct)
                        <div class="card bg-white border-0 rounded-3 mb-4">
                            <div class="card-body p-4">
                                <div class="row align-items-center g-3">
                                    <div class="col-lg-7">
                                        <div class="d-flex align-items-start">
                                            <div class="koc-product-icon bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="material-symbols-outlined fs-22">workspace_premium</i>
                                            </div>

                                            <div>
                                                <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-12 fw-normal mb-2">
                                                    Produk Paling Laris
                                                </span>

                                                <h4 class="fw-semibold mb-1">
                                                    {{ $topProduct->product_name }}
                                                </h4>

                                                <p class="text-body mb-0">
                                                    Terjual {{ number_format($topProduct->total_sold, 0, ',', '.') }} {{ $topProduct->unit }}
                                                    dengan omzet {{ $rupiah($topProduct->total_amount) }}.
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <div class="d-flex justify-content-lg-end gap-2 flex-wrap">
                                            <a
                                                href="{{ route('reports.sales', ['q' => $topProduct->product_name]) }}"
                                                class="btn btn-outline-primary"
                                            >
                                                Lihat Transaksi
                                            </a>

                                            <a
                                                href="{{ route('products.index', ['q' => $topProduct->sku]) }}"
                                                class="btn btn-primary text-white"
                                            >
                                                Detail Produk
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('reports.best-products') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Cari produk, SKU, barcode..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <select name="category_id" class="form-select form-control">
                                                <option value="">Semua Kategori</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $categoryId || $dateFrom || $dateTo)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('reports.best-products') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <a href="{{ route('reports.sales') }}" class="btn btn-primary text-white px-4 w-100">
                                                Laporan Penjualan
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="koc-product-list">
                                @forelse ($bestProducts as $index => $product)
                                    @php
                                        $rank = $bestProducts->firstItem() + $index;
                                        $percent = $product->total_sold > 0
                                            ? max(5, round(((int) $product->total_sold / $maxProductSold) * 100))
                                            : 0;
                                    @endphp

                                    <div class="koc-product-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-product-icon bg-primary bg-opacity-10 text-primary me-3">
                                                        <strong>#{{ $rank }}</strong>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $product->product_name }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $product->product?->category?->name ?? 'Tanpa Kategori' }}
                                                        </p>

                                                        <div class="koc-product-meta">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                SKU: {{ $product->sku ?: '-' }}
                                                            </span>

                                                            <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                                {{ number_format($product->total_transactions, 0, ',', '.') }} transaksi
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Terjual</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($product->total_sold, 0, ',', '.') }}
                                                    <span class="fs-12 text-body">{{ $product->unit }}</span>
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Rata-rata Harga</span>
                                                <h6 class="fw-semibold fs-15 mb-0 koc-price">
                                                    {{ $rupiah($product->average_price) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Omzet</span>
                                                <h6 class="fw-semibold fs-15 mb-0 koc-price">
                                                    {{ $rupiah($product->total_amount) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-1 col-md-12">
                                                <div class="d-flex justify-content-xl-end gap-2 flex-wrap">
                                                    <a
                                                        href="{{ route('reports.sales', ['q' => $product->product_name]) }}"
                                                        class="btn btn-outline-primary btn-sm"
                                                    >
                                                        Transaksi
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fs-12 text-body">Perbandingan terhadap produk terlaris</span>
                                                <span class="fs-12 fw-medium">{{ $percent }}%</span>
                                            </div>

                                            <div class="koc-progress">
                                                <div class="koc-progress-bar" style="width: {{ $percent }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <div class="mb-3">
                                            <i class="material-symbols-outlined text-body fs-40">shopping_bag</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Belum ada produk terjual</h6>

                                        <p class="text-body mb-3">
                                            Produk terlaris akan muncul setelah transaksi POS disimpan.
                                        </p>

                                        <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                            Buka Kasir POS
                                        </a>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $bestProducts->count() }} dari {{ $bestProducts->total() }} produk
                                    </span>

                                    <div>
                                        {{ $bestProducts->links('pagination::bootstrap-5') }}
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