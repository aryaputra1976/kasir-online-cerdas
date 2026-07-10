<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Kategori Produk - Kasir Online Cerdas</title>

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

            .koc-category-icon {
                width: 42px;
                height: 42px;
                min-width: 42px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 12px;
            }

            .koc-table {
                width: 100%;
                min-width: 0;
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
                width: 430px !important;
                max-width: 100%;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-description {
                max-width: 360px;
                white-space: normal;
                line-height: 1.45;
            }

            .koc-category-name {
                min-width: 210px;
            }

            @media (max-width: 991.98px) {
                .koc-table {
                    min-width: 760px;
                }
            }

            .koc-category-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-category-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-category-row:hover {
                background-color: #fafaff;
            }

            .koc-category-meta {
                display: flex;
                flex-wrap: wrap;
                gap: 8px;
                margin-top: 8px;
            }

            .koc-category-actions {
                display: flex;
                align-items: center;
                justify-content: end;
                gap: 6px;
            }

            @media (max-width: 767.98px) {
                .koc-category-actions {
                    justify-content: start;
                    margin-top: 15px;
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
                            <h3 class="mb-1">Kategori Produk</h3>
                            <p class="text-body mb-0">
                                Kelola kategori untuk pengelompokan produk toko.
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
                                    <span class="fw-medium">Kategori Produk</span>
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
                                            <span class="d-block text-body mb-2">Total Kategori</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($totalCategories, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="material-symbols-outlined">category</i>
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
                                            <span class="d-block text-body mb-2">Kategori Aktif</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($activeCategories, 0, ',', '.') }}
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
                                            <span class="d-block text-body mb-2">Kategori Nonaktif</span>
                                            <h3 class="fs-24 fw-semibold mb-0">
                                                {{ number_format($inactiveCategories, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-danger bg-opacity-10 text-danger">
                                            <i class="material-symbols-outlined">block</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('categories.index') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-4 col-lg-5 col-md-6">
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Cari kategori atau slug..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-3 col-lg-3 col-md-6">
                                            <select name="status" class="form-select form-control">
                                                <option value="">Semua Status</option>
                                                <option value="active" @selected($status === 'active')>Aktif</option>
                                                <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                                            </select>
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $status)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <button
                                                class="btn btn-primary text-white px-4 w-100"
                                                data-bs-toggle="offcanvas"
                                                data-bs-target="#createCategoryCanvas"
                                                aria-controls="createCategoryCanvas"
                                                type="button"
                                            >
                                                <i class="ri-add-line"></i>
                                                Tambah Kategori
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="koc-category-list">
                                @forelse ($categories as $category)
                                    <div class="koc-category-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-lg-5 col-md-6">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-category-icon bg-primary bg-opacity-10 text-primary me-3">
                                                        <i class="material-symbols-outlined fs-20">category</i>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $category->name }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $category->description ?: 'Belum ada deskripsi kategori.' }}
                                                        </p>

                                                        <div class="koc-category-meta">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                Slug: {{ $category->slug }}
                                                            </span>

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                Urutan: {{ $category->sort_order }}
                                                            </span>

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                Dibuat: {{ $category->created_at->format('d/m/Y') }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-lg-2 col-md-2">
                                                <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                    0 Produk
                                                </span>
                                            </div>

                                            <div class="col-lg-2 col-md-2">
                                                @if ($category->is_active)
                                                    <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                        Aktif
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger p-2 fs-12 fw-normal">
                                                        Nonaktif
                                                    </span>
                                                @endif
                                            </div>

                                            <div class="col-lg-3 col-md-2">
                                                <div class="koc-category-actions">
                                                    <button
                                                        class="koc-action-button"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#editCategoryModal{{ $category->id }}"
                                                        type="button"
                                                        title="Edit kategori"
                                                    >
                                                        <i class="material-symbols-outlined fs-18 text-body">edit</i>
                                                    </button>

                                                    <form
                                                        action="{{ route('categories.destroy', $category) }}"
                                                        method="post"
                                                        onsubmit="return confirm('Yakin ingin menghapus kategori {{ $category->name }}?')"
                                                    >
                                                        @csrf
                                                        @method('DELETE')

                                                        <button
                                                            class="koc-action-button"
                                                            type="submit"
                                                            title="Hapus kategori"
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
                                            <i class="material-symbols-outlined text-body fs-40">category</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Belum ada kategori produk</h6>

                                        <p class="text-body mb-3">
                                            Tambahkan kategori seperti Makanan, Minuman, ATK, atau Jasa.
                                        </p>

                                        <button
                                            class="btn btn-primary text-white"
                                            data-bs-toggle="offcanvas"
                                            data-bs-target="#createCategoryCanvas"
                                            type="button"
                                        >
                                            Tambah Kategori Pertama
                                        </button>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $categories->count() }} dari {{ $categories->total() }} kategori
                                    </span>

                                    <div>
                                        {{ $categories->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($categories as $category)
                        <div class="modal fade" id="editCategoryModal{{ $category->id }}" tabindex="-1" aria-labelledby="editCategoryModalLabel{{ $category->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header border-bottom">
                                        <h5 class="modal-title" id="editCategoryModalLabel{{ $category->id }}">
                                            Edit Kategori
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>

                                    <form action="{{ route('categories.update', $category) }}" method="post">
                                        @csrf
                                        @method('PUT')

                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">
                                                    Nama Kategori <span class="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ old('name', $category->name) }}"
                                                    class="form-control"
                                                    required
                                                >
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Slug</label>
                                                <input
                                                    type="text"
                                                    name="slug"
                                                    value="{{ old('slug', $category->slug) }}"
                                                    class="form-control"
                                                    placeholder="Otomatis jika dikosongkan"
                                                >
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Deskripsi</label>
                                                <textarea
                                                    name="description"
                                                    rows="3"
                                                    class="form-control"
                                                >{{ old('description', $category->description) }}</textarea>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">Urutan</label>
                                                        <input
                                                            type="number"
                                                            name="sort_order"
                                                            value="{{ old('sort_order', $category->sort_order) }}"
                                                            class="form-control"
                                                            min="0"
                                                        >
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label">
                                                            Status <span class="text-danger">*</span>
                                                        </label>
                                                        <select name="is_active" class="form-select" required>
                                                            <option value="1" @selected(old('is_active', $category->is_active ? '1' : '0') === '1')>
                                                                Aktif
                                                            </option>
                                                            <option value="0" @selected(old('is_active', $category->is_active ? '1' : '0') === '0')>
                                                                Nonaktif
                                                            </option>
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

                <div class="offcanvas offcanvas-end koc-offcanvas" tabindex="-1" id="createCategoryCanvas" aria-labelledby="createCategoryCanvasLabel">
                    <div class="offcanvas-header border-bottom">
                        <h5 class="offcanvas-title" id="createCategoryCanvasLabel">
                            Tambah Kategori Produk
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
                    </div>

                    <div class="offcanvas-body">
                        <form action="{{ route('categories.store') }}" method="post">
                            @csrf

                            <div class="mb-3">
                                <label class="form-label">
                                    Nama Kategori <span class="text-danger">*</span>
                                </label>
                                <input
                                    type="text"
                                    name="name"
                                    value="{{ old('name') }}"
                                    class="form-control"
                                    placeholder="Contoh: Makanan"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Slug</label>
                                <input
                                    type="text"
                                    name="slug"
                                    value="{{ old('slug') }}"
                                    class="form-control"
                                    placeholder="Contoh: makanan"
                                >
                                <small class="text-body">
                                    Boleh dikosongkan, sistem akan membuat otomatis.
                                </small>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Deskripsi</label>
                                <textarea
                                    name="description"
                                    rows="4"
                                    class="form-control"
                                    placeholder="Keterangan kategori produk"
                                >{{ old('description') }}</textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Urutan</label>
                                <input
                                    type="number"
                                    name="sort_order"
                                    value="{{ old('sort_order', 0) }}"
                                    class="form-control"
                                    min="0"
                                >
                            </div>

                            <div class="mb-4">
                                <label class="form-label">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select name="is_active" class="form-select" required>
                                    <option value="1" @selected(old('is_active', '1') === '1')>
                                        Aktif
                                    </option>
                                    <option value="0" @selected(old('is_active') === '0')>
                                        Nonaktif
                                    </option>
                                </select>
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="offcanvas">
                                    Batal
                                </button>
                                <button type="submit" class="btn btn-primary text-white">
                                    Simpan Kategori
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
