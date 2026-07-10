<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $pageTitle }} - Kasir Online Cerdas</title>

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

            .koc-stock-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-stock-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-stock-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-stock-row:hover {
                background-color: #fafaff;
            }

            .koc-stock-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
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
            }

            @media (max-width: 767.98px) {
                .koc-stock-row {
                    padding: 16px;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">{{ $pageTitle }}</h3>
                            <p class="text-body mb-0">
                                Pantau stok produk, stok minimum, stok kosong, dan produk yang perlu segera direstok.
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
                                    <span class="fw-medium">Stok</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">{{ $pageTitle }}</span>
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
                                            <span class="d-block text-body mb-2">Total Produk</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($totalProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary">
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
                                            <span class="d-block text-body mb-2">Stok Aman</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($safeStockProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success">
                                            <i class="material-symbols-outlined">check_circle</i>
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
                                            <span class="d-block text-body mb-2">Stok Menipis</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($lowStockProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="material-symbols-outlined">warning</i>
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
                                            <span class="d-block text-body mb-2">Stok Kosong</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($emptyStockProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="material-symbols-outlined">remove_shopping_cart</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('stocks.index') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-4 col-lg-4 col-md-6">
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

                                        <div class="col-xl-3 col-lg-3 col-md-6">
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
                                            <select name="stock_filter" class="form-select form-control">
                                                <option value="">Semua Stok</option>
                                                <option value="safe" @selected($stockFilter === 'safe')>Stok Aman</option>
                                                <option value="low" @selected($stockFilter === 'low')>Stok Menipis</option>
                                                <option value="empty" @selected($stockFilter === 'empty')>Stok Kosong</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $categoryId || $stockFilter)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <a href="{{ route('products.index') }}" class="btn btn-primary text-white px-4 w-100">
                                                Kelola Produk
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="koc-stock-list">
                                @forelse ($products as $product)
                                    @php
                                        $stockPercent = $product->minimum_stock > 0
                                            ? min(100, round(($product->stock / $product->minimum_stock) * 100))
                                            : 100;

                                        if ($product->stock <= 0) {
                                            $stockBadgeClass = 'bg-danger bg-opacity-10 text-danger';
                                            $stockLabel = 'Stok Kosong';
                                            $progressClass = 'bg-danger';
                                        } elseif ($product->stock <= $product->minimum_stock) {
                                            $stockBadgeClass = 'bg-warning bg-opacity-10 text-warning';
                                            $stockLabel = 'Stok Menipis';
                                            $progressClass = 'bg-warning';
                                        } else {
                                            $stockBadgeClass = 'bg-success bg-opacity-10 text-success';
                                            $stockLabel = 'Stok Aman';
                                            $progressClass = 'bg-success';
                                        }
                                    @endphp

                                    <div class="koc-stock-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-stock-icon bg-primary bg-opacity-10 text-primary me-3">
                                                        <i class="material-symbols-outlined fs-22">inventory_2</i>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $product->name }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                                        </p>

                                                        <div class="koc-stock-meta">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                SKU: {{ $product->sku }}
                                                            </span>

                                                            @if ($product->barcode)
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Barcode: {{ $product->barcode }}
                                                                </span>
                                                            @endif

                                                            @if ($product->is_active)
                                                                <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                                    Aktif
                                                                </span>
                                                            @else
                                                                <span class="badge bg-danger bg-opacity-10 text-danger p-2 fs-12 fw-normal">
                                                                    Nonaktif
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Stok Saat Ini</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($product->stock, 0, ',', '.') }}
                                                    <span class="fs-12 text-body">{{ $product->unit }}</span>
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Stok Minimum</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($product->minimum_stock, 0, ',', '.') }}
                                                    <span class="fs-12 text-body">{{ $product->unit }}</span>
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-2">Status Stok</span>
                                                <span class="badge {{ $stockBadgeClass }} p-2 fs-12 fw-normal">
                                                    {{ $stockLabel }}
                                                </span>
                                            </div>

                                            <div class="col-xl-2 col-lg-1 col-md-12">
                                                <div class="d-flex justify-content-xl-end">
                                                    <a href="{{ route('products.index', ['q' => $product->sku]) }}" class="btn btn-outline-primary btn-sm">
                                                        Detail Produk
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fs-12 text-body">Rasio stok terhadap minimum</span>
                                                <span class="fs-12 fw-medium">{{ $stockPercent }}%</span>
                                            </div>

                                            <div class="koc-progress">
                                                <div class="koc-progress-bar {{ $progressClass }}" style="width: {{ $stockPercent }}%;"></div>
                                            </div>
                                        </div>
                                    </div>
                                    @empty
                                        <div class="text-center py-5 bg-white rounded-3 border">
                                            <div class="mb-3">
                                                <i class="material-symbols-outlined text-body fs-40">inventory_2</i>
                                            </div>

                                            @if (request()->routeIs('stocks.low') || $stockFilter === 'low')
                                                <h6 class="fw-semibold mb-1">Tidak ada produk stok menipis</h6>

                                                <p class="text-body mb-3">
                                                    Semua stok produk saat ini dalam kondisi aman.
                                                </p>

                                                <a href="{{ route('stocks.index') }}" class="btn btn-primary text-white">
                                                    Lihat Stok Barang
                                                </a>
                                            @else
                                                <h6 class="fw-semibold mb-1">Belum ada data stok</h6>

                                                <p class="text-body mb-3">
                                                    Data stok akan muncul setelah produk ditambahkan.
                                                </p>

                                                <a href="{{ route('products.index') }}" class="btn btn-primary text-white">
                                                    Tambah Produk
                                                </a>
                                            @endif
                                        </div>
                                    @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk
                                    </span>

                                    <div>
                                        {{ $products->links('pagination::bootstrap-5') }}
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
