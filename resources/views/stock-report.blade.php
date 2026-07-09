<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laporan Stok - Kasir Online Cerdas</title>

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

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

            $summaryCards = [
                [
                    'title' => 'Total Produk',
                    'value' => number_format($totalProducts, 0, ',', '.'),
                    'note' => number_format($activeProducts, 0, ',', '.') . ' produk aktif',
                    'icon' => 'inventory_2',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'Total Stok',
                    'value' => number_format($totalStockQty, 0, ',', '.'),
                    'note' => 'Akumulasi semua stok produk',
                    'icon' => 'warehouse',
                    'color' => 'bg-info bg-opacity-10 text-info',
                ],
                [
                    'title' => 'Nilai Modal Stok',
                    'value' => $rupiah($stockCostValue),
                    'note' => 'Stok x harga modal',
                    'icon' => 'payments',
                    'color' => 'bg-warning bg-opacity-10 text-warning',
                ],
                [
                    'title' => 'Potensi Laba Kotor',
                    'value' => $rupiah($potentialGrossProfit),
                    'note' => 'Estimasi nilai jual - modal',
                    'icon' => 'trending_up',
                    'color' => $potentialGrossProfit >= 0
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
                            <h3 class="mb-1">Laporan Stok</h3>
                            <p class="text-body mb-0">
                                Ringkasan stok produk, nilai persediaan, dan status stok barang.
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
                                    <span class="fw-medium">Stok</span>
                                </li>
                            </ol>
                        </nav>
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
                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success me-3">
                                            <i class="material-symbols-outlined">check_circle</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Stok Aman</span>
                                            <h4 class="fw-semibold mb-0">{{ number_format($safeStockProducts, 0, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning me-3">
                                            <i class="material-symbols-outlined">warning</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Stok Menipis</span>
                                            <h4 class="fw-semibold mb-0">{{ number_format($lowStockProducts, 0, ',', '.') }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center">
                                        <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger me-3">
                                            <i class="material-symbols-outlined">remove_shopping_cart</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Stok Kosong</span>
                                            <h4 class="fw-semibold mb-0">{{ number_format($emptyStockProducts, 0, ',', '.') }}</h4>
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
                                            <i class="material-symbols-outlined">sell</i>
                                        </div>

                                        <div>
                                            <span class="d-block text-body mb-1">Nilai Jual Stok</span>
                                            <h4 class="fw-semibold mb-0 koc-price">{{ $rupiah($stockSellingValue) }}</h4>
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
                                    <h3 class="mb-1">Daftar Stok Produk</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Pantau stok barang berdasarkan status aman, menipis, kosong, dan nonaktif.
                                    </p>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <a href="{{ route('stocks.index') }}" class="btn btn-outline-primary">
                                        Stok Barang
                                    </a>

                                    <a href="{{ route('stocks.movements') }}" class="btn btn-primary text-white">
                                        Mutasi Stok
                                    </a>
                                </div>
                            </div>

                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('stock.report') }}" method="get">
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
                                            <select name="stock_status" class="form-select form-control">
                                                <option value="">Semua Status</option>
                                                <option value="safe" @selected($stockStatus === 'safe')>Stok Aman</option>
                                                <option value="low" @selected($stockStatus === 'low')>Stok Menipis</option>
                                                <option value="empty" @selected($stockStatus === 'empty')>Stok Kosong</option>
                                                <option value="inactive" @selected($stockStatus === 'inactive')>Produk Nonaktif</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $categoryId || $stockStatus)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('stock.report') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                </form>
                            </div>

                            <div class="koc-stock-list">
                                @forelse ($products as $product)
                                    @php
                                        if (! $product->is_active) {
                                            $statusLabel = 'Nonaktif';
                                            $statusClass = 'bg-secondary bg-opacity-10 text-secondary';
                                            $iconClass = 'bg-secondary bg-opacity-10 text-secondary';
                                            $icon = 'visibility_off';
                                            $progressClass = 'bg-secondary';
                                        } elseif ($product->stock <= 0) {
                                            $statusLabel = 'Stok Kosong';
                                            $statusClass = 'bg-danger bg-opacity-10 text-danger';
                                            $iconClass = 'bg-danger bg-opacity-10 text-danger';
                                            $icon = 'remove_shopping_cart';
                                            $progressClass = 'bg-danger';
                                        } elseif ($product->stock <= $product->minimum_stock) {
                                            $statusLabel = 'Stok Menipis';
                                            $statusClass = 'bg-warning bg-opacity-10 text-warning';
                                            $iconClass = 'bg-warning bg-opacity-10 text-warning';
                                            $icon = 'warning';
                                            $progressClass = 'bg-warning';
                                        } else {
                                            $statusLabel = 'Stok Aman';
                                            $statusClass = 'bg-success bg-opacity-10 text-success';
                                            $iconClass = 'bg-success bg-opacity-10 text-success';
                                            $icon = 'check_circle';
                                            $progressClass = 'bg-success';
                                        }

                                        $minimumStock = max(1, (int) $product->minimum_stock);
                                        $currentStock = max(0, (int) $product->stock);
                                        $stockPercent = $currentStock > 0
                                            ? min(100, max(8, round(($currentStock / $minimumStock) * 100)))
                                            : 0;

                                        $costValue = (float) $product->stock * (float) $product->purchase_price;
                                        $sellingValue = (float) $product->stock * (float) $product->selling_price;
                                        $grossProfitValue = $sellingValue - $costValue;
                                    @endphp

                                    <div class="koc-stock-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-stock-icon {{ $iconClass }} me-3">
                                                        <i class="material-symbols-outlined fs-22">{{ $icon }}</i>
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

                                                            <span class="badge {{ $statusClass }} p-2 fs-12 fw-normal">
                                                                {{ $statusLabel }}
                                                            </span>
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
                                                <span class="fs-12 text-body">
                                                    Min. {{ number_format($product->minimum_stock, 0, ',', '.') }}
                                                </span>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Harga Modal</span>
                                                <h6 class="fw-semibold fs-15 mb-0 koc-price">
                                                    {{ $rupiah($product->purchase_price) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Harga Jual</span>
                                                <h6 class="fw-semibold fs-15 mb-0 koc-price">
                                                    {{ $rupiah($product->selling_price) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-1 col-md-12">
                                                <div class="d-flex justify-content-xl-end gap-2 flex-wrap">
                                                    <a
                                                        href="{{ route('products.index', ['q' => $product->sku]) }}"
                                                        class="btn btn-outline-primary btn-sm"
                                                    >
                                                        Produk
                                                    </a>

                                                    <a
                                                        href="{{ route('stocks.movements', ['q' => $product->sku]) }}"
                                                        class="btn btn-outline-secondary btn-sm"
                                                    >
                                                        Mutasi
                                                    </a>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-3 mt-2">
                                            <div class="col-lg-4 col-md-6">
                                                <span class="d-block fs-12 text-body mb-1">Nilai Modal Stok</span>
                                                <strong class="koc-price">{{ $rupiah($costValue) }}</strong>
                                            </div>

                                            <div class="col-lg-4 col-md-6">
                                                <span class="d-block fs-12 text-body mb-1">Nilai Jual Stok</span>
                                                <strong class="koc-price">{{ $rupiah($sellingValue) }}</strong>
                                            </div>

                                            <div class="col-lg-4 col-md-6">
                                                <span class="d-block fs-12 text-body mb-1">Potensi Laba Kotor</span>
                                                <strong class="koc-price {{ $grossProfitValue >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $rupiah($grossProfitValue) }}
                                                </strong>
                                            </div>
                                        </div>

                                        <div class="mt-3">
                                            <div class="d-flex justify-content-between mb-1">
                                                <span class="fs-12 text-body">
                                                    Perbandingan stok terhadap minimum
                                                </span>
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

                                        <h6 class="fw-semibold mb-1">Data stok tidak ditemukan</h6>

                                        <p class="text-body mb-3">
                                            Ubah filter pencarian atau tambahkan produk baru.
                                        </p>

                                        <a href="{{ route('products.index') }}" class="btn btn-primary text-white">
                                            Kelola Produk
                                        </a>
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
