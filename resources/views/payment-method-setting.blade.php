<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Metode Pembayaran - Kasir Online Cerdas</title>

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

            .koc-switch-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 14px 16px;
                background-color: #ffffff;
            }

            .koc-qris-preview {
                width: 220px;
                max-width: 100%;
                border-radius: 16px;
                border: 1px solid #eef0f7;
                background-color: #ffffff;
                padding: 10px;
            }

            .koc-payment-card {
                border: 1px solid #eef0f7;
                border-radius: 16px;
                padding: 16px;
                background-color: #ffffff;
            }

            .koc-payment-card + .koc-payment-card {
                margin-top: 12px;
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
                            <h3 class="mb-1">Metode Pembayaran</h3>
                            <p class="text-body mb-0">
                                Atur metode pembayaran POS, QRIS manual, transfer bank, dan catatan EDC.
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
                                    <span class="fw-medium">Metode Pembayaran</span>
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
                            <strong>Metode pembayaran belum bisa disimpan.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form
                        action="{{ route('settings.payment-methods.update') }}"
                        method="post"
                        enctype="multipart/form-data"
                        class="koc-form-card"
                    >
                        @csrf
                        @method('PUT')

                        <div class="row g-4">
                            <div class="col-xl-7">
                                <div class="card bg-white border-0 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="koc-setting-icon bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="material-symbols-outlined">payments</i>
                                            </div>

                                            <div>
                                                <h3 class="mb-1">Metode Aktif</h3>
                                                <p class="text-body mb-0 fs-13">
                                                    Pilih metode pembayaran yang boleh digunakan di POS.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="d-flex flex-column gap-3">
                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="hidden" name="payment_cash_enabled" value="0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="payment_cash_enabled"
                                                        name="payment_cash_enabled"
                                                        value="1"
                                                        @checked(old('payment_cash_enabled', $setting->payment_cash_enabled))
                                                    >
                                                    <label class="form-check-label fw-medium" for="payment_cash_enabled">
                                                        Tunai
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="hidden" name="payment_qris_enabled" value="0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="payment_qris_enabled"
                                                        name="payment_qris_enabled"
                                                        value="1"
                                                        @checked(old('payment_qris_enabled', $setting->payment_qris_enabled))
                                                    >
                                                    <label class="form-check-label fw-medium" for="payment_qris_enabled">
                                                        QRIS Manual
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="hidden" name="payment_transfer_enabled" value="0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="payment_transfer_enabled"
                                                        name="payment_transfer_enabled"
                                                        value="1"
                                                        @checked(old('payment_transfer_enabled', $setting->payment_transfer_enabled))
                                                    >
                                                    <label class="form-check-label fw-medium" for="payment_transfer_enabled">
                                                        Transfer Bank
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="koc-switch-row">
                                                <div class="form-check form-switch mb-0">
                                                    <input type="hidden" name="payment_edc_enabled" value="0">
                                                    <input
                                                        class="form-check-input"
                                                        type="checkbox"
                                                        role="switch"
                                                        id="payment_edc_enabled"
                                                        name="payment_edc_enabled"
                                                        value="1"
                                                        @checked(old('payment_edc_enabled', $setting->payment_edc_enabled))
                                                    >
                                                    <label class="form-check-label fw-medium" for="payment_edc_enabled">
                                                        EDC / Kartu
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-white border-0 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="koc-setting-icon bg-success bg-opacity-10 text-success me-3">
                                                <i class="material-symbols-outlined">qr_code_2</i>
                                            </div>

                                            <div>
                                                <h3 class="mb-1">QRIS Manual</h3>
                                                <p class="text-body mb-0 fs-13">
                                                    Upload gambar QRIS agar kasir dapat menampilkan QRIS saat checkout.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Nama Merchant QRIS</label>
                                                <input
                                                    type="text"
                                                    name="qris_merchant_name"
                                                    value="{{ old('qris_merchant_name', $setting->qris_merchant_name) }}"
                                                    class="form-control"
                                                    placeholder="Contoh: TOKO KOPI ANWAR"
                                                >
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label fw-medium">Gambar QRIS</label>
                                                <input
                                                    type="file"
                                                    name="qris_image"
                                                    class="form-control"
                                                    accept="image/png,image/jpeg,image/webp"
                                                >
                                                <div class="koc-help mt-1">
                                                    Format JPG, PNG, WEBP. Maksimal 2 MB.
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-medium">Catatan QRIS</label>
                                                <textarea
                                                    name="qris_note"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Contoh: Scan QRIS, lalu pastikan nominal sesuai total transaksi."
                                                >{{ old('qris_note', $setting->qris_note) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-white border-0 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="koc-setting-icon bg-info bg-opacity-10 text-info me-3">
                                                <i class="material-symbols-outlined">account_balance</i>
                                            </div>

                                            <div>
                                                <h3 class="mb-1">Transfer Bank</h3>
                                                <p class="text-body mb-0 fs-13">
                                                    Data rekening yang akan ditampilkan saat metode transfer dipilih.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <label class="form-label fw-medium">Nama Bank</label>
                                                <input
                                                    type="text"
                                                    name="bank_name"
                                                    value="{{ old('bank_name', $setting->bank_name) }}"
                                                    class="form-control"
                                                    placeholder="Contoh: BCA"
                                                >
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-medium">Nomor Rekening</label>
                                                <input
                                                    type="text"
                                                    name="bank_account_number"
                                                    value="{{ old('bank_account_number', $setting->bank_account_number) }}"
                                                    class="form-control"
                                                    placeholder="1234567890"
                                                >
                                            </div>

                                            <div class="col-md-4">
                                                <label class="form-label fw-medium">Atas Nama</label>
                                                <input
                                                    type="text"
                                                    name="bank_account_name"
                                                    value="{{ old('bank_account_name', $setting->bank_account_name) }}"
                                                    class="form-control"
                                                    placeholder="Nama pemilik rekening"
                                                >
                                            </div>

                                            <div class="col-12">
                                                <label class="form-label fw-medium">Catatan Transfer</label>
                                                <textarea
                                                    name="transfer_note"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Contoh: Pastikan bukti transfer sesuai nominal transaksi."
                                                >{{ old('transfer_note', $setting->transfer_note) }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="card bg-white border-0 rounded-3">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="koc-setting-icon bg-warning bg-opacity-10 text-warning me-3">
                                                <i class="material-symbols-outlined">credit_card</i>
                                            </div>

                                            <div>
                                                <h3 class="mb-1">EDC / Kartu</h3>
                                                <p class="text-body mb-0 fs-13">
                                                    Catatan singkat untuk pembayaran kartu atau mesin EDC.
                                                </p>
                                            </div>
                                        </div>

                                        <label class="form-label fw-medium">Catatan EDC</label>
                                        <textarea
                                            name="edc_note"
                                            rows="3"
                                            class="form-control"
                                            placeholder="Contoh: Pastikan transaksi EDC berhasil sebelum struk dicetak."
                                        >{{ old('edc_note', $setting->edc_note) }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-5">
                                <div class="card bg-white border-0 rounded-3 mb-4">
                                    <div class="card-body p-4">
                                        <div class="d-flex align-items-center mb-4">
                                            <div class="koc-setting-icon bg-primary bg-opacity-10 text-primary me-3">
                                                <i class="material-symbols-outlined">visibility</i>
                                            </div>

                                            <div>
                                                <h3 class="mb-1">Preview Pembayaran</h3>
                                                <p class="text-body mb-0 fs-13">
                                                    Ringkasan pengaturan pembayaran yang aktif.
                                                </p>
                                            </div>
                                        </div>

                                        <div class="koc-payment-card">
                                            <h6 class="fw-semibold mb-2">Status Metode</h6>
                                            <div class="d-flex flex-wrap gap-2">
                                                <span class="badge {{ $setting->payment_cash_enabled ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-body border' }} p-2 fs-12 fw-normal">
                                                    Tunai: {{ $setting->payment_cash_enabled ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                                <span class="badge {{ $setting->payment_qris_enabled ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-body border' }} p-2 fs-12 fw-normal">
                                                    QRIS: {{ $setting->payment_qris_enabled ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                                <span class="badge {{ $setting->payment_transfer_enabled ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-body border' }} p-2 fs-12 fw-normal">
                                                    Transfer: {{ $setting->payment_transfer_enabled ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                                <span class="badge {{ $setting->payment_edc_enabled ? 'bg-success bg-opacity-10 text-success' : 'bg-light text-body border' }} p-2 fs-12 fw-normal">
                                                    EDC: {{ $setting->payment_edc_enabled ? 'Aktif' : 'Nonaktif' }}
                                                </span>
                                            </div>
                                        </div>

                                        <div class="koc-payment-card">
                                            <h6 class="fw-semibold mb-2">QRIS</h6>

                                            @if ($setting->qris_image_path)
                                                <div class="text-center mb-3">
                                                    <img
                                                        src="{{ asset('storage/' . $setting->qris_image_path) }}"
                                                        alt="QRIS"
                                                        class="koc-qris-preview"
                                                    >
                                                </div>
                                            @else
                                                <p class="text-body mb-2">
                                                    Gambar QRIS belum diupload.
                                                </p>
                                            @endif

                                            <p class="mb-1">
                                                Merchant:
                                                <strong>{{ $setting->qris_merchant_name ?: '-' }}</strong>
                                            </p>
                                            <p class="text-body mb-0 fs-13">
                                                {{ $setting->qris_note ?: 'Catatan QRIS belum diatur.' }}
                                            </p>

                                            @if ($setting->qris_image_path)
                                                <form
                                                    action="{{ route('settings.payment-methods.qris.destroy') }}"
                                                    method="post"
                                                    class="mt-3"
                                                    onsubmit="return confirm('Hapus gambar QRIS?')"
                                                >
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        Hapus QRIS
                                                    </button>
                                                </form>
                                            @endif
                                        </div>

                                        <div class="koc-payment-card">
                                            <h6 class="fw-semibold mb-2">Transfer Bank</h6>
                                            <p class="mb-1">
                                                Bank:
                                                <strong>{{ $setting->bank_name ?: '-' }}</strong>
                                            </p>
                                            <p class="mb-1">
                                                No. Rekening:
                                                <strong>{{ $setting->bank_account_number ?: '-' }}</strong>
                                            </p>
                                            <p class="mb-1">
                                                Atas Nama:
                                                <strong>{{ $setting->bank_account_name ?: '-' }}</strong>
                                            </p>
                                            <p class="text-body mb-0 fs-13">
                                                {{ $setting->transfer_note ?: 'Catatan transfer belum diatur.' }}
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 flex-wrap">
                                    <button type="submit" class="btn btn-primary text-white">
                                        Simpan Metode Pembayaran
                                    </button>

                                    <a href="{{ route('pos.index') }}" class="btn btn-outline-primary">
                                        Buka POS
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="flex-grow-1"></div>

                @include('partials.footer')
            </div>
        </div>

        @include('partials.theme_settings')
        @include('partials.scripts')
    </body>
</html>
