@php
    $storeSetting = \App\Models\StoreSetting::current();

    $appName = 'Kasir Online Cerdas';
    $storeName = $storeSetting?->store_name ?: $appName;
    $storePhone = $storeSetting?->phone;
    $storeEmail = $storeSetting?->email;

    $logoUrl = $storeSetting?->logo_path
        ? asset('storage/' . $storeSetting->logo_path)
        : asset('assets/images/logo-icon.png');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ $appName }}</title>

    @include('partials.styles')

    <style>
        body.koc-login-body {
            min-height: 100vh;
            background:
                radial-gradient(circle at top left, rgba(99, 102, 241, 0.18), transparent 32%),
                radial-gradient(circle at bottom right, rgba(14, 165, 233, 0.16), transparent 30%),
                #f6f8fb;
        }

        .koc-login-wrapper {
            min-height: 100svh;
            padding: 16px;
            display: flex;
            align-items: center;
        }

        .koc-login-card {
            width: 100%;
            max-width: 1180px;
            height: calc(100svh - 32px);
            max-height: 760px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid rgba(226, 232, 240, 0.9);
            border-radius: 26px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, 0.10);
        }

        .koc-login-card > .row {
            height: 100%;
        }

        .koc-login-hero {
            position: relative;
            height: 100%;
            min-height: 0;
            padding: 34px 42px;
            color: #ffffff;
            background:
                linear-gradient(145deg, rgba(37, 99, 235, 0.95), rgba(79, 70, 229, 0.96)),
                url('{{ asset('assets/images/bg-1.jpg') }}');
            background-size: cover;
            background-position: center;
            overflow: hidden;
        }

        .koc-login-hero::before {
            content: "";
            position: absolute;
            width: 360px;
            height: 360px;
            right: -120px;
            top: -120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.12);
        }

        .koc-login-hero::after {
            content: "";
            position: absolute;
            width: 280px;
            height: 280px;
            left: -100px;
            bottom: -100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.10);
        }

        .koc-login-hero-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .koc-brand-badge {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            width: fit-content;
            padding: 10px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            backdrop-filter: blur(10px);
            color: #ffffff;
            font-weight: 700;
        }

        .koc-brand-badge img {
            width: 34px;
            height: 34px;
            object-fit: contain;
            border-radius: 10px;
            background: #ffffff;
            padding: 4px;
        }

        .koc-hero-title {
            max-width: 560px;
            font-size: clamp(34px, 4vw, 44px);
            line-height: 1.12;
            letter-spacing: -0.04em;
            font-weight: 800;
            margin-top: 46px;
            margin-bottom: 16px;
            color: #ffffff;
        }

        .koc-hero-description {
            max-width: 520px;
            font-size: 16px;
            line-height: 1.65;
            color: rgba(255, 255, 255, 0.86);
            margin-bottom: 22px;
        }

        .koc-feature-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 14px;
            margin-top: auto;
        }

        .koc-feature-item {
            padding: 15px;
            border-radius: 15px;
            background: rgba(255, 255, 255, 0.14);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.16);
        }

        .koc-feature-item i {
            display: inline-flex;
            width: 36px;
            height: 36px;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.18);
            margin-bottom: 12px;
        }

        .koc-feature-item h6 {
            color: #ffffff;
            font-size: 15px;
            margin-bottom: 5px;
        }

        .koc-feature-item p {
            color: rgba(255, 255, 255, 0.76);
            font-size: 13px;
            line-height: 1.55;
            margin-bottom: 0;
        }

        .koc-login-form-side {
            height: 100%;
            min-height: 0;
            padding: 34px 46px;
            display: flex;
            align-items: center;
        }

        .koc-login-form-box {
            width: 100%;
            max-width: 460px;
            margin: 0 auto;
        }

        .koc-logo-box {
            width: 62px;
            height: 62px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 18px;
            background: #eef2ff;
            margin-bottom: 22px;
        }

        .koc-logo-box img {
            width: 42px;
            height: 42px;
            object-fit: contain;
        }

        .koc-login-title {
            font-size: 30px;
            line-height: 1.2;
            letter-spacing: -0.03em;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 10px;
        }

        .koc-login-subtitle {
            color: #64748b;
            line-height: 1.7;
            margin-bottom: 26px;
        }

        .koc-access-card {
            display: flex;
            gap: 12px;
            align-items: flex-start;
            padding: 14px 16px;
            border-radius: 16px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
        }

        .koc-access-card i {
            color: #4f46e5;
        }

        .koc-access-card p {
            margin-bottom: 0;
            font-size: 13px;
            line-height: 1.6;
            color: #64748b;
        }

        .koc-form-control {
            min-height: 54px;
            border-radius: 14px;
            border-color: #dbe3ef;
            padding-left: 16px;
            padding-right: 16px;
        }

        .koc-form-control:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.12);
        }

        .koc-login-button {
            min-height: 54px;
            border-radius: 14px;
            font-weight: 700;
            box-shadow: 0 12px 24px rgba(79, 70, 229, 0.22);
        }

        .koc-footer-note {
            margin-top: 26px;
            padding-top: 20px;
            border-top: 1px solid #edf2f7;
            color: #64748b;
            font-size: 13px;
            line-height: 1.7;
        }

        .koc-footer-note a {
            text-decoration: none;
            font-weight: 700;
        }

        @media (max-height: 760px) and (min-width: 992px) {
            .koc-login-wrapper {
                padding: 10px;
            }

            .koc-login-card {
                height: calc(100svh - 20px);
                border-radius: 22px;
            }

            .koc-login-hero {
                padding: 28px 38px;
            }

            .koc-login-form-side {
                padding: 28px 42px;
            }

            .koc-brand-badge {
                padding: 8px 12px;
            }

            .koc-brand-badge img {
                width: 30px;
                height: 30px;
            }

            .koc-hero-title {
                margin-top: 34px;
                font-size: 38px;
            }

            .koc-hero-description {
                font-size: 15px;
                line-height: 1.55;
                margin-bottom: 18px;
            }

            .koc-feature-grid {
                gap: 10px;
            }

            .koc-feature-item {
                padding: 13px;
                border-radius: 15px;
            }

            .koc-feature-item i {
                width: 32px;
                height: 32px;
                margin-bottom: 8px;
            }

            .koc-feature-item h6 {
                font-size: 14px;
                margin-bottom: 4px;
            }

            .koc-feature-item p {
                font-size: 12px;
                line-height: 1.55;
            }

            .koc-logo-box {
                width: 54px;
                height: 54px;
                margin-bottom: 18px;
            }

            .koc-logo-box img {
                width: 38px;
                height: 38px;
            }

            .koc-login-title {
                font-size: 28px;
                margin-bottom: 8px;
            }

            .koc-login-subtitle {
                line-height: 1.55;
                margin-bottom: 18px;
            }

            .koc-access-card {
                padding: 12px 14px;
                margin-bottom: 18px;
            }

            .koc-form-control {
                min-height: 50px;
            }

            .form-group.mb-4 {
                margin-bottom: 18px !important;
            }

            .koc-login-button {
                min-height: 50px;
            }

            .koc-footer-note {
                margin-top: 18px;
                padding-top: 16px;
            }
        }

        @media (max-width: 991.98px) {
            .koc-login-card {
                height: auto;
                max-height: none;
            }

            .koc-login-card > .row {
                height: auto;
            }

            .koc-login-hero {
                height: auto;
                min-height: auto;
                padding: 34px;
            }

            .koc-login-form-side {
                height: auto;
                min-height: auto;
                padding: 34px;
            }

            .koc-hero-title {
                margin-top: 38px;
                font-size: 34px;
            }
        }

        @media (max-width: 575.98px) {
            .koc-login-wrapper {
                padding: 16px;
            }

            .koc-login-card {
                border-radius: 22px;
            }

            .koc-login-hero {
                padding: 28px;
            }

            .koc-login-form-side {
                padding: 28px;
            }

            .koc-feature-grid {
                grid-template-columns: 1fr;
            }

            .koc-hero-title {
                font-size: 30px;
            }

            .koc-login-title {
                font-size: 27px;
            }
        }
    </style>
</head>

<body class="boxed-size koc-login-body">
    @include('partials.preloader')

    <main class="koc-login-wrapper">
        <div class="koc-login-card">
            <div class="row g-0">
                <div class="col-lg-6">
                    <section class="koc-login-hero">
                        <div class="koc-login-hero-content">
                            <div class="koc-brand-badge">
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                                <span>{{ $appName }}</span>
                            </div>

                            <div>
                                <h1 class="koc-hero-title">
                                    Sistem POS dan order online untuk UMKM modern.
                                </h1>

                                <p class="koc-hero-description">
                                    Kelola transaksi kasir, produk, stok, pelanggan, pembayaran, dan laporan penjualan dalam satu aplikasi yang ringan dan mudah digunakan.
                                </p>
                            </div>

                            <div class="koc-feature-grid">
                                <div class="koc-feature-item">
                                    <i class="material-symbols-outlined">point_of_sale</i>
                                    <h6>Kasir POS</h6>
                                    <p>Transaksi cepat, struk siap cetak, dan stok otomatis berkurang.</p>
                                </div>

                                <div class="koc-feature-item">
                                    <i class="material-symbols-outlined">shopping_cart</i>
                                    <h6>Order Online</h6>
                                    <p>Pelanggan bisa pesan dari halaman menu publik.</p>
                                </div>

                                <div class="koc-feature-item">
                                    <i class="material-symbols-outlined">inventory_2</i>
                                    <h6>Manajemen Stok</h6>
                                    <p>Pantau stok menipis, mutasi barang, dan produk aktif.</p>
                                </div>

                                <div class="koc-feature-item">
                                    <i class="material-symbols-outlined">monitoring</i>
                                    <h6>Laporan Bisnis</h6>
                                    <p>Penjualan, laba rugi, produk terlaris, dan rekap order online.</p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="col-lg-6">
                    <section class="koc-login-form-side">
                        <div class="koc-login-form-box">
                            <div class="koc-logo-box">
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}">
                            </div>

                            <h2 class="koc-login-title">
                                Masuk ke dashboard
                            </h2>

                            <p class="koc-login-subtitle">
                                Gunakan akun yang sudah dibuat oleh Owner atau Admin untuk mengakses aplikasi {{ $storeName }}.
                            </p>

                            <div class="koc-access-card">
                                <i class="material-symbols-outlined">admin_panel_settings</i>
                                <p>
                                    Akses aplikasi dibagi berdasarkan role:
                                    <strong>Owner</strong>, <strong>Admin</strong>, dan <strong>Kasir</strong>.
                                </p>
                            </div>

                            @if (session('success'))
                                <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="alert alert-info bg-info bg-opacity-10 border-0 text-info mb-4" role="alert">
                                    {{ session('info') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="post" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group mb-4">
                                    <label class="label text-secondary fw-semibold" for="email">
                                        Email
                                    </label>

                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        class="form-control koc-form-control"
                                        value="{{ old('email') }}"
                                        placeholder="admin@kasir.test"
                                        required
                                        autofocus
                                    >
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label text-secondary fw-semibold" for="password">
                                        Password
                                    </label>

                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="form-control koc-form-control"
                                        placeholder="Masukkan password"
                                        required
                                    >
                                </div>

                                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
                                    <div class="form-check">
                                        <input
                                            id="remember"
                                            name="remember"
                                            type="checkbox"
                                            class="form-check-input"
                                            value="1"
                                            @checked(old('remember'))
                                        >

                                        <label class="form-check-label text-secondary" for="remember">
                                            Tetap masuk
                                        </label>
                                    </div>

                                    <a href="{{ route('product.demo') }}" class="text-primary fw-semibold text-decoration-none">
                                        Lihat demo produk
                                    </a>
                                </div>

                                <button type="submit" class="btn btn-primary text-white w-100 koc-login-button">
                                    <span class="d-flex align-items-center justify-content-center">
                                        <i class="material-symbols-outlined text-white fs-20 me-2">login</i>
                                        Masuk
                                    </span>
                                </button>

                                <div class="koc-footer-note">
                                    <strong>Pendaftaran publik tidak tersedia.</strong>
                                    Akun hanya dapat dibuat melalui menu User & Role oleh Owner atau Admin.

                                    @if ($storePhone || $storeEmail)
                                        <br>
                                        Bantuan akses:
                                        @if ($storePhone)
                                            {{ $storePhone }}
                                        @endif

                                        @if ($storePhone && $storeEmail)
                                            •
                                        @endif

                                        @if ($storeEmail)
                                            {{ $storeEmail }}
                                        @endif
                                    @endif
                                </div>
                            </form>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </main>

    @include('partials.scripts')
</body>
</html>