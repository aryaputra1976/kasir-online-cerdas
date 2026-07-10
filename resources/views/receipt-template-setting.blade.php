<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Template Struk - Kasir Online Cerdas</title>

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

            .koc-form-card .form-control,
            .koc-form-card .form-select {
                min-height: 48px;
            }

            .koc-help {
                font-size: 13px;
                color: #64748b;
            }

            .koc-preview-paper {
                width: 80mm;
                max-width: 100%;
                background: #ffffff;
                border: 1px solid #eef0f7;
                border-radius: 12px;
                padding: 16px 14px;
                margin: 0 auto;
                font-family: Arial, Helvetica, sans-serif;
                color: #111827;
            }

            .koc-preview-center {
                text-align: center;
            }

            .koc-preview-store {
                font-size: 16px;
                font-weight: 700;
                text-transform: uppercase;
                margin-bottom: 4px;
            }

            .koc-preview-small {
                font-size: 11px;
                line-height: 1.4;
                margin: 0;
            }

            .koc-preview-divider {
                border-top: 1px dashed #111827;
                margin: 10px 0;
            }

            .koc-preview-row {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                font-size: 12px;
                line-height: 1.45;
            }

            .koc-switch-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 14px 16px;
                background-color: #ffffff;
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
                            <h3 class="mb-1">Template Struk</h3>
                            <p class="text-body mb-0">
                                Atur teks dan tampilan tambahan pada struk POS.
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
                                    <span class="fw-medium">Template Struk</span>
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
                            <strong>Template struk belum bisa disimpan.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="card bg-white border-0 rounded-3">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="koc-setting-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">receipt_long</i>
                                        </div>

                                        <div>
                                            <h3 class="mb-1">Pengaturan Template</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Ubah teks footer, kebijakan, dan elemen yang tampil di struk.
                                            </p>
                                        </div>
                                    </div>

                                    <form
                                        action="{{ route('settings.receipt-template.update') }}"
                                        method="post"
                                        class="koc-form-card"
                                    >
                                        @csrf
                                        @method('PUT')

                                        <div class="mb-3">
                                            <label for="receipt_footer" class="form-label fw-medium">
                                                Teks Footer Struk
                                            </label>
                                            <textarea
                                                name="receipt_footer"
                                                id="receipt_footer"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Contoh: Terima kasih sudah berbelanja."
                                            >{{ old('receipt_footer', $setting->receipt_footer) }}</textarea>
                                            <div class="koc-help mt-1">
                                                Teks utama yang tampil di bagian bawah struk.
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="receipt_policy_text" class="form-label fw-medium">
                                                Teks Tambahan / Kebijakan Struk
                                            </label>
                                            <textarea
                                                name="receipt_policy_text"
                                                id="receipt_policy_text"
                                                rows="3"
                                                class="form-control"
                                                placeholder="Contoh: Barang yang sudah dibeli tidak dapat dikembalikan."
                                            >{{ old('receipt_policy_text', $setting->receipt_policy_text) }}</textarea>
                                            <div class="koc-help mt-1">
                                                Kosongkan jika tidak ingin menampilkan teks kebijakan tambahan.
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-3 mb-4">
                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input
                                                        type="hidden"
                                                        name="receipt_show_logo"
                                                        value="0"
                                                    >
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="receipt_show_logo"
                                                        name="receipt_show_logo"
                                                        value="1"
                                                        @checked(old('receipt_show_logo', $setting->receipt_show_logo))
                                                    >
                                                    <label class="form-check-label fw-medium" for="receipt_show_logo">
                                                        Tampilkan logo toko di struk
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input
                                                        type="hidden"
                                                        name="receipt_show_sku"
                                                        value="0"
                                                    >
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="receipt_show_sku"
                                                        name="receipt_show_sku"
                                                        value="1"
                                                        @checked(old('receipt_show_sku', $setting->receipt_show_sku))
                                                    >
                                                    <label class="form-check-label fw-medium" for="receipt_show_sku">
                                                        Tampilkan SKU produk di struk
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input
                                                        type="hidden"
                                                        name="receipt_show_powered_by"
                                                        value="0"
                                                    >
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="receipt_show_powered_by"
                                                        name="receipt_show_powered_by"
                                                        value="1"
                                                        @checked(old('receipt_show_powered_by', $setting->receipt_show_powered_by))
                                                    >
                                                    <label class="form-check-label fw-medium" for="receipt_show_powered_by">
                                                        Tampilkan teks â€œDicetak dari nama tokoâ€
                                                    </label>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2 flex-wrap">
                                            <button type="submit" class="btn btn-primary text-white">
                                                Simpan Template
                                            </button>

                                            <a href="{{ route('settings.store') }}" class="btn btn-outline-secondary">
                                                Profil Toko
                                            </a>

                                            <a href="{{ route('pos.index') }}" class="btn btn-outline-primary">
                                                Buka POS
                                            </a>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            <div class="card bg-white border-0 rounded-3">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center mb-4">
                                        <div class="koc-setting-icon bg-info bg-opacity-10 text-info me-3">
                                            <i class="material-symbols-outlined">visibility</i>
                                        </div>

                                        <div>
                                            <h3 class="mb-1">Preview Sederhana</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Gambaran isi footer dan elemen struk.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="koc-preview-paper">
                                        <div class="koc-preview-center">
                                            <div class="koc-preview-store">
                                                {{ $setting->store_name }}
                                            </div>
                                            <p class="koc-preview-small">
                                                {{ $setting->address ?: 'Alamat toko belum diatur' }}
                                            </p>
                                            <p class="koc-preview-small">
                                                {{ $setting->phone ? 'WA/HP: ' . $setting->phone : 'WA/HP belum diatur' }}
                                            </p>
                                        </div>

                                        <div class="koc-preview-divider"></div>

                                        <div class="koc-preview-row">
                                            <span>Invoice</span>
                                            <strong>POS-20260709-0001</strong>
                                        </div>

                                        <div class="koc-preview-row">
                                            <span>Total</span>
                                            <strong>Rp 11.000</strong>
                                        </div>

                                        <div class="koc-preview-divider"></div>

                                        <div class="koc-preview-center">
                                            @if ($setting->receipt_footer)
                                                <p class="koc-preview-small">
                                                    {{ $setting->receipt_footer }}
                                                </p>
                                            @endif

                                            @if ($setting->receipt_policy_text)
                                                <p class="koc-preview-small">
                                                    {{ $setting->receipt_policy_text }}
                                                </p>
                                            @endif

                                            @if ($setting->receipt_show_powered_by)
                                                <p class="koc-preview-small" style="margin-top: 8px;">
                                                    Dicetak dari {{ $setting->store_name }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mt-3">
                                        <p class="text-body fs-13 mb-1">
                                            Status tampilan:
                                        </p>

                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                Logo: {{ $setting->receipt_show_logo ? 'Tampil' : 'Sembunyi' }}
                                            </span>
                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                SKU: {{ $setting->receipt_show_sku ? 'Tampil' : 'Sembunyi' }}
                                            </span>
                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                Dicetak dari: {{ $setting->receipt_show_powered_by ? 'Tampil' : 'Sembunyi' }}
                                            </span>
                                        </div>
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
