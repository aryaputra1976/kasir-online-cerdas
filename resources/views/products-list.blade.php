<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Produk - Kasir Online Cerdas</title>

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

            .koc-action-button {
                width: 30px;
                height: 30px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 8px;
                border: 0;
                background: transparent;
            }

            .koc-action-button:hover {
                background-color: rgba(96, 93, 255, 0.08);
            }

            .koc-offcanvas {
                width: 520px !important;
                max-width: 100%;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-price {
                letter-spacing: -0.2px;
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
                            <h3 class="mb-1">Produk</h3>
                            <p class="text-body mb-0">
                                Kelola produk, harga jual, stok, dan kategori produk toko.
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
                                    <span class="fw-medium">Master Data</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Produk</span>
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
                        <div class="col-lg-4 col-md-6">
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

                        <div class="col-lg-4 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Produk Aktif</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($activeProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success">
                                            <i class="material-symbols-outlined">check_circle</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-4 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Stok Menipis</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($lowStockProducts, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="material-symbols-outlined">warning</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('products.index') }}" method="get">
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

                                        <div class="col-xl-3 col-lg-4 col-md-6">
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
                                            <select name="status" class="form-select form-control">
                                                <option value="">Semua Status</option>
                                                <option value="active" @selected($status === 'active')>Aktif</option>
                                                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-4 col-md-6">
                                            <select name="stock_filter" class="form-select form-control">
                                                <option value="">Semua Stok</option>
                                                <option value="low" @selected($stockFilter === 'low')>Stok Menipis</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $status || $categoryId || $stockFilter)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('products.index') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <button
                                                class="btn btn-primary text-white px-4 w-100"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#createProductCanvas"
                                                aria-controls="createProductCanvas"
                                                type="button"
                                            >
                                                <i class="ri-add-line"></i>
                                                Tambah Produk
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="koc-product-list">
                                @forelse ($products as $product)
                                    <div class="koc-product-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-product-icon bg-primary bg-opacity-10 text-primary me-3">
                                                        <i class="material-symbols-outlined fs-22">inventory_2</i>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $product->name }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $product->description ?: 'Belum ada deskripsi produk.' }}
                                                        </p>

                                                        <div class="koc-product-meta">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                SKU: {{ $product->sku }}
                                                            </span>

                                                            @if ($product->barcode)
                                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                    Barcode: {{ $product->barcode }}
                                                                </span>
                                                            @endif

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Harga Jual</span>
                                                <h6 class="fw-semibold fs-14 mb-0 koc-price">
                                                    {{ $rupiah($product->selling_price) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Harga Modal</span>
                                                <h6 class="fw-semibold fs-14 mb-0 koc-price">
                                                    {{ $rupiah($product->purchase_price) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-1 col-lg-1 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Stok</span>
                                                @if ($product->is_low_stock)
                                                    <span class="badge bg-danger bg-opacity-10 text-danger p-2 fs-12 fw-normal">
                                                        {{ $product->stock }} {{ $product->unit }}
                                                    </span>
                                                @else
                                                    <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                        {{ $product->stock }} {{ $product->unit }}
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col-xl-1 col-lg-1 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Min.</span>
                                                <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                    {{ $product->minimum_stock }}
                                                </span>
                                            </div>

                                            <div class="col-xl-1 col-lg-1 col-md-4">
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

                                            <div class="col-xl-1 col-lg-1 col-md-4">
                                                <div class="d-flex align-items-center justify-content-end gap-1">
                                                    <button
                                                        class="koc-action-button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editProductModal{{ $product->id }}"
                                                        type="button"
                                                        title="Edit produk"
                                                    >
                                                        <i class="material-symbols-outlined fs-18 text-body">edit</i>
                                                    </button>

                                                    <form
                                                        action="{{ route('products.destroy', $product) }}"
                                                        method="post"
                                                        onsubmit="return confirm('Yakin ingin menghapus produk {{ $product->name }}?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            class="koc-action-button"
                                                            type="submit"
                                                            title="Hapus produk"
                                                        >
                                                            <i class="material-symbols-outlined fs-18 text-danger">delete</i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <div class="mb-3">
                                            <i class="material-symbols-outlined text-body fs-40">inventory_2</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Belum ada produk</h6>

                                        <p class="text-body mb-3">
                                            Tambahkan produk pertama agar nanti bisa dipakai di Kasir POS dan Order Online.
                                        </p>

                                        <button
                                            class="btn btn-primary text-white"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#createProductCanvas"
                                            type="button"
                                        >
                                            Tambah Produk Pertama
                                        </button>
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

                    @foreach ($products as $product)
                        <div class="modal fade" id="editProductModal{{ $product->id }}" tabindex="-1" aria-labelledby="editProductModalLabel{{ $product->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title" id="editProductModalLabel{{ $product->id }}">
                                            Edit Produk
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>

                                    <form action="{{ route('products.update', $product) }}" method="post">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-body">
                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                                        <input type="text" name="name" value="{{ old('name', $product->name) }}" class="form-control" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Kategori</label>
                                                        <select name="category_id" class="form-select">
                                                            <option value="">Tanpa Kategori</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->id }}" @selected((int) old('category_id', $product->category_id) === $category->id)>
                                                                    {{ $category->name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">SKU / Kode Produk <span class="text-danger">*</span></label>
                                                        <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="form-control" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Barcode</label>
                                                        <input type="text" name="barcode" value="{{ old('barcode', $product->barcode) }}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Slug</label>
                                                        <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Deskripsi</label>
                                                        <textarea name="description" rows="3" class="form-control">{{ old('description', $product->description) }}</textarea>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga Modal</label>
                                                        <input type="number" name="purchase_price" value="{{ old('purchase_price', (int) $product->purchase_price) }}" class="form-control" min="0">
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                                        <input type="number" name="selling_price" value="{{ old('selling_price', (int) $product->selling_price) }}" class="form-control" min="0" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Stok</label>
                                                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="form-control" min="0">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Stok Minimum</label>
                                                        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', $product->minimum_stock) }}" class="form-control" min="0">
                                                    </div>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="mb-3">
                                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                                        <input type="text" name="unit" value="{{ old('unit', $product->unit) }}" class="form-control" required>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="mb-3">
                                                        <label class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select name="is_active" class="form-select" required>
                                                            <option value="1" @selected(old('is_active', $product->is_active ? '1' : '0') === '1')>Aktif</option>
                                                            <option value="0" @selected(old('is_active', $product->is_active ? '1' : '0') === '0')>Nonaktif</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal-footer border-top">
                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                Batal
                                            </button>
                                            <button type="submit" class="btn btn-primary text-white">
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="offcanvas offcanvas-end koc-offcanvas" tabindex="-1" id="createProductCanvas" aria-labelledby="createProductCanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="createProductCanvasLabel">
                            Tambah Produk
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
                    </div>

                    <div class="offcanvas-body">
                        <form action="{{ route('products.store') }}" method="post">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" name="name" value="{{ old('name') }}" class="form-control" placeholder="Contoh: Kopi Susu Gula Aren" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Kategori</label>
                                <select name="category_id" class="form-select">
                                    <option value="">Tanpa Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) old('category_id') === $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">SKU / Kode Produk <span class="text-danger">*</span></label>
                                        <input type="text" name="sku" value="{{ old('sku') }}" class="form-control" placeholder="KOPI-001" required>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Barcode</label>
                                        <input type="text" name="barcode" value="{{ old('barcode') }}" class="form-control" placeholder="Opsional">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input type="text" name="slug" value="{{ old('slug') }}" class="form-control" placeholder="Otomatis jika dikosongkan">
                                <small class="text-body">Boleh dikosongkan, sistem akan membuat otomatis.</small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea name="description" rows="3" class="form-control" placeholder="Keterangan produk">{{ old('description') }}</textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Harga Modal</label>
                                        <input type="number" name="purchase_price" value="{{ old('purchase_price', 0) }}" class="form-control" min="0">
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Harga Jual <span class="text-danger">*</span></label>
                                        <input type="number" name="selling_price" value="{{ old('selling_price', 0) }}" class="form-control" min="0" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Stok</label>
                                        <input type="number" name="stock" value="{{ old('stock', 0) }}" class="form-control" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Stok Minimum</label>
                                        <input type="number" name="minimum_stock" value="{{ old('minimum_stock', 0) }}" class="form-control" min="0">
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                                        <input type="text" name="unit" value="{{ old('unit', 'pcs') }}" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" @selected(old('is_active', '1') === '1')>Aktif</option>
                                    <option value="0" @selected(old('is_active') === '0')>Nonaktif</option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary text-white">
                                    Simpan Produk
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