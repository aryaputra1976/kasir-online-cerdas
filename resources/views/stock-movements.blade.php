<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Mutasi Stok - Kasir Online Cerdas</title>

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

            .koc-movement-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-movement-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-movement-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-movement-row:hover {
                background-color: #fafaff;
            }

            .koc-movement-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .koc-offcanvas {
                width: 520px !important;
                max-width: 100%;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            @media (max-width: 767.98px) {
                .koc-movement-row {
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
                            <h3 class="mb-1">Mutasi Stok</h3>
                            <p class="text-body mb-0">
                                Catat stok masuk, stok keluar, dan penyesuaian stok produk.
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
                                    <span class="fw-medium">Mutasi Stok</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4" role="alert">
                            <i class="material-symbols-outlined align-middle fs-18 me-1">check_circle</i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4" role="alert">
                            <div class="fw-semibold mb-1">
                                <i class="material-symbols-outlined align-middle fs-18 me-1">error</i>
                                Data belum bisa disimpan.
                            </div>

                            <ul class="mb-0 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Total Mutasi</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($totalMovements, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="material-symbols-outlined">sync_alt</i>
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
                                            <span class="d-block text-body mb-2">Stok Masuk</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($stockInMovements, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success">
                                            <i class="material-symbols-outlined">add_box</i>
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
                                            <span class="d-block text-body mb-2">Stok Keluar</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($stockOutMovements, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="material-symbols-outlined">indeterminate_check_box</i>
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
                                            <span class="d-block text-body mb-2">Penyesuaian</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($adjustmentMovements, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-info bg-opacity-10 text-info">
                                            <i class="material-symbols-outlined">tune</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('stocks.movements') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Cari produk, SKU, referensi..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <select name="category_id" class="form-select form-control">
                                                <option value="">Semua Kategori</option>
                                                @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>
                                                        {{ $category->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <select name="movement_type" class="form-select form-control">
                                                <option value="">Semua Mutasi</option>
                                                <option value="IN" @selected($movementType === 'IN')>Stok Masuk</option>
                                                <option value="OUT" @selected($movementType === 'OUT')>Stok Keluar</option>
                                                <option value="ADJUSTMENT" @selected($movementType === 'ADJUSTMENT')>Penyesuaian</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                        </div>

                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $categoryId || $movementType || $dateFrom || $dateTo)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('stocks.movements') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <button
                                                class="btn btn-primary text-white px-4 w-100"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#createStockMovementCanvas"
                                                aria-controls="createStockMovementCanvas"
                                                type="button"
                                                @disabled($products->isEmpty())
                                            >
                                                <i class="ri-add-line"></i>
                                                Tambah Mutasi
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            @if ($products->isEmpty())
                                <div class="alert alert-warning bg-warning bg-opacity-10 border-0 text-warning mb-4" role="alert">
                                    <i class="material-symbols-outlined align-middle fs-18 me-1">warning</i>
                                    Belum ada produk aktif. Tambahkan produk terlebih dahulu sebelum mencatat mutasi stok.
                                </div>
                            @endif

                            <div class="koc-movement-list">
                                @forelse ($movements as $movement)
                                    @php
                                        $movementClass = match ($movement->movement_type) {
                                            'IN' => 'bg-success bg-opacity-10 text-success',
                                            'OUT' => 'bg-warning bg-opacity-10 text-warning',
                                            'ADJUSTMENT' => 'bg-info bg-opacity-10 text-info',
                                            default => 'bg-light text-body',
                                        };

                                        $iconClass = match ($movement->movement_type) {
                                            'IN' => 'bg-success bg-opacity-10 text-success',
                                            'OUT' => 'bg-warning bg-opacity-10 text-warning',
                                            'ADJUSTMENT' => 'bg-info bg-opacity-10 text-info',
                                            default => 'bg-primary bg-opacity-10 text-primary',
                                        };

                                        $iconName = match ($movement->movement_type) {
                                            'IN' => 'add_box',
                                            'OUT' => 'indeterminate_check_box',
                                            'ADJUSTMENT' => 'tune',
                                            default => 'sync_alt',
                                        };

                                        $changeClass = $movement->quantity_change >= 0
                                            ? 'text-success'
                                            : 'text-danger';
                                    @endphp

                                    <div class="koc-movement-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-movement-icon {{ $iconClass }} me-3">
                                                        <i class="material-symbols-outlined fs-22">{{ $iconName }}</i>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $movement->product?->name ?? 'Produk terhapus' }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $movement->product?->category?->name ?? 'Tanpa Kategori' }}
                                                        </p>

                                                        <div class="koc-movement-meta">
                                                            <span class="badge {{ $movementClass }} p-2 fs-12 fw-normal">
                                                                {{ $movement->movement_type_label }}
                                                            </span>

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $movement->movement_date->format('d/m/Y') }}
                                                            </span>

                                                            @if ($movement->reference_no)
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Ref: {{ $movement->reference_no }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Perubahan</span>
                                                <h6 class="fw-semibold fs-18 mb-0 {{ $changeClass }}">
                                                    {{ $movement->quantity_change_label }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Stok Awal</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($movement->stock_before, 0, ',', '.') }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Stok Akhir</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($movement->stock_after, 0, ',', '.') }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-1 col-md-12">
                                                <div class="d-flex justify-content-xl-end">
                                                    @if ($movement->product)
                                                        <a href="{{ route('products.index', ['q' => $movement->product->sku]) }}" class="btn btn-outline-primary btn-sm">
                                                            Detail Produk
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        @if ($movement->note)
                                            <div class="mt-3 pt-3 border-top">
                                                <span class="fs-12 text-body d-block mb-1">Catatan</span>
                                                <p class="mb-0 fs-13">{{ $movement->note }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <div class="mb-3">
                                            <i class="material-symbols-outlined text-body fs-40">sync_alt</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Belum ada mutasi stok</h6>

                                        <p class="text-body mb-3">
                                            Catat stok masuk, stok keluar, atau penyesuaian stok pertama.
                                        </p>

                                        <button
                                            class="btn btn-primary text-white"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#createStockMovementCanvas"
                                            type="button"
                                            @disabled($products->isEmpty())
                                        >
                                            Tambah Mutasi
                                        </button>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $movements->count() }} dari {{ $movements->total() }} mutasi
                                    </span>

                                    <div>
                                        {{ $movements->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="offcanvas offcanvas-end koc-offcanvas" tabindex="-1" id="createStockMovementCanvas" aria-labelledby="createStockMovementCanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="createStockMovementCanvasLabel">
                            Tambah Mutasi Stok
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
                    </div>

                    <div class="offcanvas-body">
                        <form action="{{ route('stocks.movements.store') }}" method="post">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Produk <span class="text-danger">*</span></label>
                                <select name="product_id" class="form-select" required>
                                    <option value="">Pilih Produk</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}" @selected((int) old('product_id') === $product->id)>
                                            {{ $product->name }} — Stok {{ $product->stock }} {{ $product->unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Mutasi <span class="text-danger">*</span></label>
                                <select name="movement_type" class="form-select" required>
                                    <option value="IN" @selected(old('movement_type') === 'IN')>Stok Masuk</option>
                                    <option value="OUT" @selected(old('movement_type') === 'OUT')>Stok Keluar</option>
                                    <option value="ADJUSTMENT" @selected(old('movement_type') === 'ADJUSTMENT')>Penyesuaian Stok</option>
                                </select>
                                <small class="text-body">
                                    Untuk penyesuaian stok, isi jumlah dengan stok akhir yang benar.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jumlah / Stok Akhir <span class="text-danger">*</span></label>
                                <input
                                    type="number"
                                    name="quantity"
                                    value="{{ old('quantity', 1) }}"
                                    class="form-control"
                                    min="0"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tanggal Mutasi <span class="text-danger">*</span></label>
                                <input
                                    type="date"
                                    name="movement_date"
                                    value="{{ old('movement_date', now()->toDateString()) }}"
                                    class="form-control"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Nomor Referensi</label>
                                <input
                                    type="text"
                                    name="reference_no"
                                    value="{{ old('reference_no') }}"
                                    class="form-control"
                                    placeholder="Contoh: PO-001 / NOTA-001"
                                >
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Catatan</label>
                                <textarea
                                    name="note"
                                    rows="4"
                                    class="form-control"
                                    placeholder="Contoh: Restok dari supplier / koreksi stok opname"
                                >{{ old('note') }}</textarea>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary text-white">
                                    Simpan Mutasi
                                </button>
                            </div>
                        </form>
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