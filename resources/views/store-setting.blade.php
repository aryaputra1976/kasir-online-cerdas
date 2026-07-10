<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Pengaturan Toko - Kasir Online Cerdas</title>

        @include('partials.styles')

        <style>
            .koc-setting-icon {
                width: 48px;
                height: 48px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-logo-preview {
                width: 110px;
                height: 110px;
                border-radius: 18px;
                object-fit: cover;
                border: 1px solid #eef0f7;
                background-color: #f8fafc;
            }

            .koc-logo-placeholder {
                width: 110px;
                height: 110px;
                border-radius: 18px;
                border: 1px dashed #cbd5e1;
                background-color: #f8fafc;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                color: #64748b;
            }

            .koc-form-card .form-control,
            .koc-form-card .form-select {
                min-height: 48px;
            }

            .koc-help {
                font-size: 13px;
                color: #64748b;
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
                            <h3 class="mb-1">Pengaturan Toko</h3>
                            <p class="text-body mb-0">
                                Kelola profil usaha, logo, kontak, alamat, pajak, dan catatan struk.
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
                                    <span class="fw-medium">Pengaturan</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Profil Toko</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <strong>Data belum bisa disimpan.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="koc-setting-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">storefront</i>
                                        </div>

                                        <div>
                                            <h4 class="mb-1">Profil Saat Ini</h4>
                                            <p class="text-body fs-13 mb-0">
                                                Data yang akan digunakan pada laporan dan struk.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="text-center mb-4">
                                        @if ($setting->logo_path)
                                            <img
                                                src="{{ asset('storage/' . $setting->logo_path) }}"
                                                alt="Logo toko"
                                                class="koc-logo-preview"
                                            >
                                        @else
                                            <div class="koc-logo-placeholder">
                                                <i class="material-symbols-outlined fs-40">storefront</i>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="d-block text-body fs-13 mb-1">Nama Toko</span>
                                        <h5 class="fw-semibold mb-0">
                                            {{ $setting->store_name }}
                                        </h5>
                                    </div>

                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="d-block text-body fs-13 mb-1">Kontak</span>
                                        <p class="mb-1">
                                            {{ $setting->phone ?: '-' }}
                                        </p>
                                        <p class="mb-0 text-body fs-13">
                                            {{ $setting->email ?: '-' }}
                                        </p>
                                    </div>

                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="d-block text-body fs-13 mb-1">Alamat</span>
                                        <p class="mb-0">
                                            {{ $setting->address ?: '-' }}
                                        </p>
                                    </div>

                                    <div class="border rounded-3 p-3">
                                        <span class="d-block text-body fs-13 mb-1">Pajak Default</span>
                                        <h5 class="fw-semibold mb-0">
                                            {{ number_format((float) $setting->tax_percentage, 2, ',', '.') }}%
                                        </h5>
                                    </div>

                                    @if ($setting->logo_path)
                                        <form
                                            action="{{ route('settings.store.logo.destroy') }}"
                                            method="post"
                                            class="mt-3"
                                            onsubmit="return confirm('Hapus logo toko?')"
                                        >
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-outline-danger w-100">
                                                Hapus Logo
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="card bg-white border-0 rounded-3">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Form Profil Usaha</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Isi data toko dengan benar agar bisa digunakan pada struk dan laporan.
                                            </p>
                                        </div>
                                    </div>

                                    <form
                                        action="{{ route('settings.store.update') }}"
                                        method="post"
                                        enctype="multipart/form-data"
                                        class="koc-form-card"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="store_name" class="form-label fw-medium">
                                                    Nama Toko <span class="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="store_name"
                                                    id="store_name"
                                                    value="{{ old('store_name', $setting->store_name) }}"
                                                    class="form-control"
                                                    placeholder="Contoh: Kasir Online Cerdas"
                                                    required
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label for="owner_name" class="form-label fw-medium">
                                                    Nama Pemilik
                                                </label>
                                                <input
                                                    type="text"
                                                    name="owner_name"
                                                    id="owner_name"
                                                    value="{{ old('owner_name', $setting->owner_name) }}"
                                                    class="form-control"
                                                    placeholder="Nama pemilik usaha"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label for="phone" class="form-label fw-medium">
                                                    Nomor HP / WhatsApp
                                                </label>
                                                <input
                                                    type="text"
                                                    name="phone"
                                                    id="phone"
                                                    value="{{ old('phone', $setting->phone) }}"
                                                    class="form-control"
                                                    placeholder="08xxxxxxxxxx"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label for="email" class="form-label fw-medium">
                                                    Email
                                                </label>
                                                <input
                                                    type="email"
                                                    name="email"
                                                    id="email"
                                                    value="{{ old('email', $setting->email) }}"
                                                    class="form-control"
                                                    placeholder="email@toko.com"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label for="tax_percentage" class="form-label fw-medium">
                                                    Pajak Default (%)
                                                </label>
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    min="0"
                                                    max="100"
                                                    name="tax_percentage"
                                                    id="tax_percentage"
                                                    value="{{ old('tax_percentage', $setting->tax_percentage) }}"
                                                    class="form-control"
                                                    placeholder="0"
                                                >
                                                <div class="koc-help mt-1">
                                                    Isi 0 jika toko belum memakai pajak otomatis.
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <label for="logo" class="form-label fw-medium">
                                                    Logo Toko
                                                </label>
                                                <input
                                                    type="file"
                                                    name="logo"
                                                    id="logo"
                                                    class="form-control"
                                                    accept="image/png,image/jpeg,image/webp"
                                                >
                                                <div class="koc-help mt-1">
                                                    Format JPG, PNG, WEBP. Maksimal 2 MB.
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label for="address" class="form-label fw-medium">
                                                    Alamat Toko
                                                </label>
                                                <textarea
                                                    name="address"
                                                    id="address"
                                                    rows="4"
                                                    class="form-control"
                                                    placeholder="Alamat lengkap toko/usaha"
                                                >{{ old('address', $setting->address) }}</textarea>
                                            </div>

                                            <div class="col-12">
                                                <label for="receipt_footer" class="form-label fw-medium">
                                                    Catatan Footer Struk
                                                </label>
                                                <textarea
                                                    name="receipt_footer"
                                                    id="receipt_footer"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Contoh: Terima kasih sudah berbelanja."
                                                >{{ old('receipt_footer', $setting->receipt_footer) }}</textarea>
                                            </div>

                                            <div class="col-12">
                                                <div class="d-flex gap-2 flex-wrap">
                                                    <button type="submit" class="btn btn-primary text-white">
                                                        Simpan Pengaturan
                                                    </button>

                                                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                                                        Kembali
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </form>
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
