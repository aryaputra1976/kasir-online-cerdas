<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kasir Online Cerdas - Aplikasi POS & Order Online untuk UMKM</title>

    <meta
        name="description"
        content="Kasir Online Cerdas membantu UMKM mengelola transaksi POS, stok, order online, pelanggan, pembayaran, dan laporan usaha dalam satu aplikasi."
    >

    @php
        /*
        |--------------------------------------------------------------------------
        | Pengaturan CTA Landing Page
        |--------------------------------------------------------------------------
        | Ganti URL Mayar dan nomor WhatsApp di bawah sebelum dipublikasikan.
        | Nomor WhatsApp harus memakai format internasional tanpa tanda +.
        | Contoh: 6281234567890
        */
        $mayarUrl = 'https://mayar.to/ruangcerdas';
        $whatsappNumber = '6280000000000';
        $whatsappMessage = rawurlencode(
            'Halo Ruang Cerdas, saya tertarik dengan Kasir Online Cerdas. Mohon informasi paket dan cara pembeliannya.'
        );
        $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$whatsappMessage}";
    @endphp

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1d4ed8;
            --primary-soft: #eff6ff;
            --accent: #f59e0b;
            --accent-dark: #d97706;
            --success: #16a34a;
            --dark: #0f172a;
            --text: #334155;
            --muted: #64748b;
            --border: #e2e8f0;
            --soft: #f8fafc;
            --white: #ffffff;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--text);
            background: var(--white);
            font-family:
                Inter,
                ui-sans-serif,
                system-ui,
                -apple-system,
                BlinkMacSystemFont,
                "Segoe UI",
                sans-serif;
        }

        a {
            text-decoration: none;
        }

        section {
            scroll-margin-top: 90px;
        }

        .landing-navbar {
            background: rgba(255, 255, 255, 0.96);
            border-bottom: 1px solid rgba(226, 232, 240, 0.9);
            backdrop-filter: blur(12px);
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 11px;
            color: var(--dark);
            font-weight: 800;
        }

        .brand-logo:hover {
            color: var(--dark);
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            border-radius: 13px;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
        }

        .navbar-nav .nav-link {
            color: #475569;
            font-weight: 650;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary);
        }

        .btn-main,
        .btn-outline-main,
        .btn-accent,
        .btn-whatsapp,
        .btn-white {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            min-height: 48px;
            padding: 12px 22px;
            border-radius: 12px;
            font-weight: 800;
            transition: all 0.2s ease;
        }

        .btn-main {
            color: var(--white);
            background: var(--primary);
            border: 1px solid var(--primary);
        }

        .btn-main:hover {
            color: var(--white);
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.24);
        }

        .btn-outline-main {
            color: var(--dark);
            background: var(--white);
            border: 1px solid var(--border);
        }

        .btn-outline-main:hover {
            color: var(--primary);
            border-color: var(--primary);
            transform: translateY(-2px);
        }

        .btn-accent {
            color: #111827;
            background: var(--accent);
            border: 1px solid var(--accent);
            box-shadow: 0 12px 30px rgba(245, 158, 11, 0.25);
        }

        .btn-accent:hover {
            color: #111827;
            background: #fbbf24;
            border-color: #fbbf24;
            transform: translateY(-2px);
        }

        .btn-whatsapp {
            color: var(--white);
            background: #16a34a;
            border: 1px solid #16a34a;
        }

        .btn-whatsapp:hover {
            color: var(--white);
            background: #15803d;
            border-color: #15803d;
            transform: translateY(-2px);
        }

        .btn-white {
            color: var(--primary);
            background: var(--white);
            border: 1px solid var(--white);
        }

        .btn-white:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 145px 0 90px;
            background:
                radial-gradient(circle at 85% 20%, rgba(37, 99, 235, 0.16), transparent 30%),
                radial-gradient(circle at 10% 80%, rgba(245, 158, 11, 0.12), transparent 28%),
                linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
        }

        .hero-badge,
        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .hero-badge {
            margin-bottom: 20px;
            padding: 8px 14px;
            background: var(--primary-soft);
            border: 1px solid #bfdbfe;
            border-radius: 999px;
            font-size: 0.8rem;
        }

        .hero-title {
            max-width: 760px;
            margin-bottom: 20px;
            color: var(--dark);
            font-size: clamp(2.6rem, 6vw, 4.7rem);
            line-height: 1.05;
            letter-spacing: -0.045em;
            font-weight: 900;
        }

        .text-gradient {
            color: transparent;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            background-clip: text;
            -webkit-background-clip: text;
        }

        .hero-description {
            max-width: 680px;
            margin-bottom: 26px;
            color: var(--muted);
            font-size: 1.08rem;
            line-height: 1.8;
        }

        .hero-checks {
            display: grid;
            gap: 11px;
            margin: 0 0 28px;
            padding: 0;
            list-style: none;
        }

        .hero-checks li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #334155;
            font-weight: 650;
        }

        .hero-checks i {
            color: var(--success);
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .trust-note {
            display: flex;
            flex-wrap: wrap;
            gap: 18px;
            margin-top: 24px;
            color: var(--muted);
            font-size: 0.88rem;
            font-weight: 650;
        }

        .trust-note span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .app-preview {
            position: relative;
            padding: 18px;
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid var(--border);
            border-radius: 24px;
            box-shadow: 0 30px 70px rgba(15, 23, 42, 0.14);
        }

        .preview-window {
            overflow: hidden;
            background: var(--soft);
            border-radius: 16px;
        }

        .preview-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px;
            background: var(--white);
            border-bottom: 1px solid var(--border);
        }

        .preview-dots {
            display: flex;
            gap: 6px;
        }

        .preview-dots span {
            width: 9px;
            height: 9px;
            background: #cbd5e1;
            border-radius: 50%;
        }

        .preview-content {
            padding: 17px;
        }

        .mini-stat {
            height: 100%;
            padding: 16px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .mini-stat i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 37px;
            height: 37px;
            margin-bottom: 12px;
            color: var(--primary);
            background: var(--primary-soft);
            border-radius: 11px;
        }

        .mini-stat small {
            display: block;
            color: var(--muted);
        }

        .mini-stat strong {
            color: var(--dark);
            font-size: 1rem;
        }

        .chart-card {
            margin-top: 15px;
            padding: 17px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
        }

        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 9px;
            height: 135px;
            margin-top: 16px;
        }

        .chart-bars span {
            flex: 1;
            min-width: 10px;
            background: linear-gradient(180deg, var(--primary), #93c5fd);
            border-radius: 7px 7px 2px 2px;
        }

        .floating-proof {
            position: absolute;
            right: -20px;
            bottom: 35px;
            display: flex;
            align-items: center;
            gap: 11px;
            min-width: 205px;
            padding: 14px 16px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(15, 23, 42, 0.14);
        }

        .floating-proof i {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: var(--white);
            background: var(--success);
            border-radius: 12px;
        }

        .trust-strip {
            padding: 22px 0;
            background: var(--dark);
        }

        .trust-strip .item {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 9px;
            color: #e2e8f0;
            font-weight: 700;
        }

        .trust-strip i {
            color: #fbbf24;
        }

        .section-space {
            padding: 92px 0;
        }

        .section-soft {
            background: var(--soft);
        }

        .section-heading {
            max-width: 760px;
            margin: 0 auto 48px;
            text-align: center;
        }

        .eyebrow {
            margin-bottom: 11px;
            font-size: 0.8rem;
        }

        .section-title {
            margin-bottom: 15px;
            color: var(--dark);
            font-size: clamp(2rem, 4vw, 3rem);
            letter-spacing: -0.03em;
            font-weight: 900;
        }

        .section-description {
            margin: 0;
            color: var(--muted);
            line-height: 1.8;
        }

        .problem-card,
        .feature-card,
        .business-card,
        .workflow-card,
        .demo-card,
        .bonus-card {
            height: 100%;
            padding: 26px;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 18px;
            transition: all 0.22s ease;
        }

        .problem-card:hover,
        .feature-card:hover,
        .demo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 44px rgba(15, 23, 42, 0.08);
        }

        .card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 18px;
            color: var(--primary);
            background: var(--primary-soft);
            border-radius: 15px;
            font-size: 1.35rem;
        }

        .problem-card .card-icon {
            color: #dc2626;
            background: #fef2f2;
        }

        .problem-card h3,
        .feature-card h3,
        .workflow-card h3,
        .demo-card h3,
        .bonus-card h3 {
            margin-bottom: 9px;
            color: var(--dark);
            font-size: 1.05rem;
            font-weight: 850;
        }

        .problem-card p,
        .feature-card p,
        .workflow-card p,
        .demo-card p,
        .bonus-card p {
            margin: 0;
            color: var(--muted);
            font-size: 0.93rem;
            line-height: 1.7;
        }

        .business-card {
            padding: 24px 16px;
            text-align: center;
        }

        .business-card .card-icon {
            width: 62px;
            height: 62px;
            margin-bottom: 16px;
            font-size: 1.55rem;
        }

        .business-card h3 {
            margin: 0;
            color: var(--dark);
            font-size: 1rem;
            font-weight: 850;
        }

        .workflow-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            margin-bottom: 17px;
            color: var(--white);
            background: var(--primary);
            border-radius: 50%;
            font-weight: 900;
        }

        .value-box {
            overflow: hidden;
            background: var(--white);
            border: 1px solid var(--border);
            border-radius: 22px;
            box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
        }

        .value-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 18px 24px;
            border-bottom: 1px solid var(--border);
        }

        .value-row:last-child {
            border-bottom: 0;
        }

        .value-item {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--dark);
            font-weight: 750;
        }

        .value-item i {
            color: var(--success);
        }

        .value-price {
            color: var(--muted);
            text-decoration: line-through;
            white-space: nowrap;
        }

        .value-total {
            padding: 24px;
            color: var(--white);
            background: linear-gradient(135deg, var(--primary), #7c3aed);
        }

        .value-total strong {
            font-size: clamp(1.9rem, 4vw, 3rem);
        }

        .offer-card {
            position: relative;
            overflow: hidden;
            padding: 38px;
            background: var(--white);
            border: 2px solid var(--primary);
            border-radius: 24px;
            box-shadow: 0 24px 60px rgba(37, 99, 235, 0.14);
        }

        .offer-badge {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            margin-bottom: 17px;
            padding: 7px 12px;
            color: #92400e;
            background: #fef3c7;
            border-radius: 999px;
            font-size: 0.78rem;
            font-weight: 850;
        }

        .normal-price {
            color: var(--muted);
            text-decoration: line-through;
        }

        .promo-price {
            margin: 4px 0;
            color: var(--primary);
            font-size: clamp(2.5rem, 7vw, 4.2rem);
            line-height: 1;
            font-weight: 900;
        }

        .offer-list {
            display: grid;
            gap: 12px;
            margin: 25px 0;
            padding: 0;
            list-style: none;
        }

        .offer-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .offer-list i {
            color: var(--success);
        }

        .security-note {
            margin-top: 16px;
            color: var(--muted);
            font-size: 0.86rem;
            text-align: center;
        }

        .demo-card p {
            min-height: 74px;
        }

        .accordion-item {
            overflow: hidden;
            margin-bottom: 14px;
            border: 1px solid var(--border);
            border-radius: 14px !important;
        }

        .accordion-button {
            color: var(--dark);
            background: var(--white);
            font-weight: 800;
        }

        .accordion-button:not(.collapsed) {
            color: var(--primary);
            background: var(--primary-soft);
            box-shadow: none;
        }

        .accordion-button:focus {
            box-shadow: none;
        }

        .final-cta {
            padding: 85px 0;
        }

        .final-cta-box {
            padding: 65px 38px;
            color: var(--white);
            text-align: center;
            background:
                radial-gradient(circle at 10% 20%, rgba(255, 255, 255, 0.18), transparent 26%),
                linear-gradient(135deg, var(--primary), #7c3aed);
            border-radius: 28px;
        }

        .final-cta-box h2 {
            max-width: 760px;
            margin: 0 auto 17px;
            font-size: clamp(2rem, 4vw, 3.1rem);
            letter-spacing: -0.03em;
            font-weight: 900;
        }

        .final-cta-box p {
            max-width: 650px;
            margin: 0 auto 28px;
            color: rgba(255, 255, 255, 0.86);
            line-height: 1.8;
        }

        .footer {
            padding: 48px 0 100px;
            background: #0f172a;
        }

        .footer .brand-logo {
            color: var(--white);
        }

        .footer p {
            max-width: 470px;
            margin-top: 15px;
            color: #94a3b8;
            line-height: 1.7;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 20px;
        }

        .footer-links a {
            color: #cbd5e1;
            font-weight: 650;
        }

        .footer-links a:hover {
            color: var(--white);
        }

        .footer-bottom {
            margin-top: 32px;
            padding-top: 23px;
            color: #64748b;
            border-top: 1px solid #273449;
            font-size: 0.85rem;
        }

        .mobile-buy-bar {
            position: fixed;
            right: 0;
            bottom: 0;
            left: 0;
            z-index: 1050;
            display: none;
            padding: 10px 12px;
            background: rgba(255, 255, 255, 0.97);
            border-top: 1px solid var(--border);
            box-shadow: 0 -10px 30px rgba(15, 23, 42, 0.1);
            backdrop-filter: blur(10px);
        }

        .mobile-buy-price small {
            display: block;
            color: var(--muted);
            text-decoration: line-through;
        }

        .mobile-buy-price strong {
            color: var(--primary);
            font-size: 1.2rem;
        }

        @media (max-width: 991.98px) {
            .hero {
                padding-top: 120px;
            }

            .app-preview {
                margin-top: 50px;
            }

            .floating-proof {
                right: 15px;
            }

            .footer-links {
                justify-content: flex-start;
                margin-top: 24px;
            }
        }

        @media (max-width: 767.98px) {
            body {
                padding-bottom: 78px;
            }

            .hero {
                padding: 110px 0 70px;
            }

            .hero-title {
                font-size: 2.65rem;
            }

            .hero-actions > a {
                width: 100%;
            }

            .trust-note {
                display: grid;
                gap: 9px;
            }

            .floating-proof {
                position: static;
                margin-top: 14px;
            }

            .section-space {
                padding: 72px 0;
            }

            .value-row {
                align-items: flex-start;
                padding: 16px;
            }

            .value-price {
                font-size: 0.84rem;
            }

            .offer-card {
                padding: 26px 22px;
            }

            .final-cta {
                padding: 65px 0;
            }

            .final-cta-box {
                padding: 50px 20px;
                border-radius: 22px;
            }

            .final-cta-box .d-flex > a {
                width: 100%;
            }

            .mobile-buy-bar {
                display: block;
            }
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg landing-navbar fixed-top">
        <div class="container py-2">
            <a class="brand-logo" href="{{ route('product.demo') }}">
                <span class="brand-icon">
                    <i class="bi bi-shop"></i>
                </span>
                <span>Kasir Online Cerdas</span>
            </a>

            <button
                class="navbar-toggler border-0 shadow-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#landingNavbar"
                aria-controls="landingNavbar"
                aria-expanded="false"
                aria-label="Buka navigasi"
            >
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="landingNavbar">
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#yang-didapat">Yang Didapat</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#harga">Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#demo">Demo</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#faq">FAQ</a>
                    </li>
                </ul>

                <a
                    href="{{ $mayarUrl }}"
                    class="btn-accent"
                    target="_blank"
                    rel="noopener noreferrer"
                >
                    <i class="bi bi-bag-check-fill"></i>
                    Beli Sekarang
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <span class="hero-badge">
                            <i class="bi bi-stars"></i>
                            Aplikasi POS + Order Online untuk UMKM
                        </span>

                        <h1 class="hero-title">
                            Kelola kasir, stok, order online, dan laporan
                            <span class="text-gradient">dalam satu aplikasi</span>
                        </h1>

                        <p class="hero-description">
                            Kasir Online Cerdas membantu cafe, warung kopi,
                            toko kecil, dan UMKM mengelola transaksi POS,
                            persediaan, pelanggan, pembayaran, serta laporan
                            bisnis dengan lebih cepat dan teratur.
                        </p>

                        <ul class="hero-checks">
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Kasir POS, produk, stok, pelanggan, dan laporan terintegrasi
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Pelanggan dapat memesan melalui menu online
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Pembayaran tunai, QRIS, transfer, EDC, dan COD
                            </li>
                            <li>
                                <i class="bi bi-check-circle-fill"></i>
                                Source code Laravel lengkap dan dapat dikembangkan
                            </li>
                        </ul>

                        <div class="hero-actions">
                            <a href="{{ route('dashboard') }}" class="btn-main">
                                <i class="bi bi-play-circle-fill"></i>
                                Coba Demo Aplikasi
                            </a>

                            <a
                                href="{{ $mayarUrl }}"
                                class="btn-accent"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-bag-check-fill"></i>
                                Beli Sekarang
                            </a>

                            <a
                                href="{{ $whatsappUrl }}"
                                class="btn-outline-main"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-whatsapp"></i>
                                Tanya Admin
                            </a>
                        </div>

                        <div class="trust-note">
                            <span>
                                <i class="bi bi-shield-check"></i>
                                Pembayaran melalui Mayar
                            </span>
                            <span>
                                <i class="bi bi-infinity"></i>
                                Sekali bayar
                            </span>
                            <span>
                                <i class="bi bi-code-slash"></i>
                                Source code lengkap
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="app-preview">
                            <div class="preview-window">
                                <div class="preview-top">
                                    <div class="preview-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <small class="text-secondary fw-semibold">
                                        Dashboard Kasir Online Cerdas
                                    </small>
                                </div>

                                <div class="preview-content">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="mini-stat">
                                                <i class="bi bi-cash-stack"></i>
                                                <small>Penjualan</small>
                                                <strong>Rp4,8 Jt</strong>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="mini-stat">
                                                <i class="bi bi-bag-check"></i>
                                                <small>Order Online</small>
                                                <strong>128 Order</strong>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="mini-stat">
                                                <i class="bi bi-box-seam"></i>
                                                <small>Produk Aktif</small>
                                                <strong>65 Produk</strong>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="mini-stat">
                                                <i class="bi bi-people"></i>
                                                <small>Pelanggan</small>
                                                <strong>245 Orang</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="chart-card">
                                        <div class="d-flex justify-content-between">
                                            <div>
                                                <strong class="d-block text-dark">
                                                    Tren Penjualan
                                                </strong>
                                                <small class="text-secondary">
                                                    Ringkasan tujuh hari
                                                </small>
                                            </div>
                                            <i class="bi bi-graph-up-arrow text-success fs-4"></i>
                                        </div>

                                        <div class="chart-bars">
                                            <span style="height: 42%"></span>
                                            <span style="height: 61%"></span>
                                            <span style="height: 48%"></span>
                                            <span style="height: 72%"></span>
                                            <span style="height: 58%"></span>
                                            <span style="height: 86%"></span>
                                            <span style="height: 74%"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="floating-proof">
                                <i class="bi bi-check-lg"></i>
                                <div>
                                    <small class="text-secondary d-block">
                                        Transaksi berhasil
                                    </small>
                                    <strong class="text-dark">
                                        Stok otomatis terupdate
                                    </strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="trust-strip">
            <div class="container">
                <div class="row g-3">
                    <div class="col-6 col-lg-3">
                        <div class="item">
                            <i class="bi bi-check-circle-fill"></i>
                            POS Terintegrasi
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="item">
                            <i class="bi bi-check-circle-fill"></i>
                            Order Online
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="item">
                            <i class="bi bi-check-circle-fill"></i>
                            Stok Otomatis
                        </div>
                    </div>
                    <div class="col-6 col-lg-3">
                        <div class="item">
                            <i class="bi bi-check-circle-fill"></i>
                            Laporan Bisnis
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <section class="section-space" id="masalah">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Masalah UMKM</span>
                    <h2 class="section-title">
                        Apakah usaha Anda masih mengalami hal-hal ini?
                    </h2>
                    <p class="section-description">
                        Pencatatan manual sering membuat pemilik usaha kesulitan
                        mengetahui kondisi bisnis secara cepat dan akurat.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-md-6 col-lg-3">
                        <article class="problem-card">
                            <span class="card-icon">
                                <i class="bi bi-journal-x"></i>
                            </span>
                            <h3>Transaksi Masih Manual</h3>
                            <p>
                                Penjualan dicatat di buku atau spreadsheet
                                sehingga mudah terlewat dan sulit direkap.
                            </p>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <article class="problem-card">
                            <span class="card-icon">
                                <i class="bi bi-box2-heart"></i>
                            </span>
                            <h3>Stok Tidak Terkontrol</h3>
                            <p>
                                Barang habis tidak diketahui lebih awal dan
                                mutasi stok sulit ditelusuri.
                            </p>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <article class="problem-card">
                            <span class="card-icon">
                                <i class="bi bi-chat-left-dots"></i>
                            </span>
                            <h3>Order WhatsApp Berantakan</h3>
                            <p>
                                Pesanan pelanggan bercampur dengan chat lain
                                dan statusnya sulit dipantau.
                            </p>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <article class="problem-card">
                            <span class="card-icon">
                                <i class="bi bi-graph-down-arrow"></i>
                            </span>
                            <h3>Tidak Tahu Laba Usaha</h3>
                            <p>
                                Omzet terlihat, tetapi modal, laba kotor,
                                produk terlaris, dan margin tidak terbaca.
                            </p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-space section-soft" id="fitur">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Solusi Lengkap</span>
                    <h2 class="section-title">
                        Semua operasional usaha dalam satu sistem
                    </h2>
                    <p class="section-description">
                        Dirancang agar pemilik usaha dan petugas kasir dapat
                        bekerja lebih cepat tanpa berpindah-pindah aplikasi.
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['bi-cart-check', 'Kasir POS', 'Keranjang transaksi, pelanggan, pajak, metode pembayaran, checkout, dan cetak struk.'],
                        ['bi-boxes', 'Produk & Stok', 'Kategori, produk, stok tersedia, stok menipis, dan mutasi barang.'],
                        ['bi-phone', 'Order Online', 'Menu publik, checkout pelanggan, pelacakan pesanan, dan bukti pembayaran.'],
                        ['bi-qr-code-scan', 'Pembayaran Fleksibel', 'Tunai, QRIS, transfer, kartu EDC, dan COD sesuai kebutuhan.'],
                        ['bi-file-earmark-bar-graph', 'Laporan Penjualan', 'Riwayat transaksi, omzet, pembayaran, order online, dan export laporan.'],
                        ['bi-graph-up-arrow', 'Laba Rugi Sederhana', 'Pendapatan, modal produk, laba kotor, dan margin penjualan.'],
                        ['bi-people', 'Manajemen Pelanggan', 'Profil, riwayat transaksi, total pembelian, dan aktivitas pelanggan.'],
                        ['bi-speedometer2', 'Dashboard Analitik', 'Ringkasan penjualan, pembayaran, stok, order, dan pelanggan.'],
                    ] as [$icon, $title, $description])
                        <div class="col-sm-6 col-lg-3">
                            <article class="feature-card">
                                <span class="card-icon">
                                    <i class="bi {{ $icon }}"></i>
                                </span>
                                <h3>{{ $title }}</h3>
                                <p>{{ $description }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space" id="cocok-untuk">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Cocok Untuk</span>
                    <h2 class="section-title">
                        Fleksibel untuk berbagai jenis usaha
                    </h2>
                    <p class="section-description">
                        Struktur produk, pembayaran, dan laporan dapat
                        digunakan oleh banyak jenis UMKM.
                    </p>
                </div>

                <div class="row g-4 justify-content-center">
                    @foreach ([
                        ['bi-cup-hot', 'Cafe'],
                        ['bi-cup-straw', 'Warung Kopi'],
                        ['bi-shop-window', 'Toko Kelontong'],
                        ['bi-basket', 'UMKM Kuliner'],
                        ['bi-bag', 'Toko Retail'],
                        ['bi-snow', 'Frozen Food'],
                    ] as [$icon, $title])
                        <div class="col-6 col-md-4 col-lg-2">
                            <article class="business-card">
                                <span class="card-icon">
                                    <i class="bi {{ $icon }}"></i>
                                </span>
                                <h3>{{ $title }}</h3>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space section-soft" id="alur">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Cara Kerja</span>
                    <h2 class="section-title">
                        Dari produk sampai laporan hanya dalam lima langkah
                    </h2>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['1', 'Input Produk', 'Tambahkan kategori, produk, harga, stok awal, dan batas minimum stok.'],
                        ['2', 'Transaksi POS', 'Pilih produk dan pelanggan, lalu selesaikan pembayaran di kasir.'],
                        ['3', 'Terima Order Online', 'Pelanggan dapat memilih menu dan checkout melalui HP.'],
                        ['4', 'Stok Terupdate', 'Stok berkurang sesuai transaksi dan pergerakannya tercatat.'],
                        ['5', 'Pantau Laporan', 'Lihat omzet, stok, produk terlaris, pelanggan, dan laba rugi.'],
                    ] as [$number, $title, $description])
                        <div class="col-md-6 col-lg">
                            <article class="workflow-card">
                                <span class="workflow-number">{{ $number }}</span>
                                <h3>{{ $title }}</h3>
                                <p>{{ $description }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space" id="yang-didapat">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Yang Anda Dapatkan</span>
                    <h2 class="section-title">
                        Semua ini tersedia dalam satu paket
                    </h2>
                    <p class="section-description">
                        Nilai berikut adalah ilustrasi nilai modul bila dibuat
                        atau dibeli secara terpisah.
                    </p>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="value-box">
                            @foreach ([
                                ['Source code Laravel lengkap', 'Rp300.000'],
                                ['Modul POS dan cetak struk', 'Rp250.000'],
                                ['Modul produk, stok, dan mutasi', 'Rp200.000'],
                                ['Modul order online dan tracking', 'Rp250.000'],
                                ['Modul pembayaran QRIS/transfer/COD', 'Rp100.000'],
                                ['Dashboard analitik dan laporan', 'Rp250.000'],
                                ['Manajemen dan analitik pelanggan', 'Rp150.000'],
                                ['Pengaturan toko dan template struk', 'Rp100.000'],
                                ['Database demo dan panduan instalasi', 'Rp100.000'],
                            ] as [$item, $price])
                                <div class="value-row">
                                    <div class="value-item">
                                        <i class="bi bi-check-circle-fill"></i>
                                        <span>{{ $item }}</span>
                                    </div>
                                    <span class="value-price">{{ $price }}</span>
                                </div>
                            @endforeach

                            <div class="value-total d-flex flex-wrap justify-content-between align-items-center gap-3">
                                <div>
                                    <small class="d-block opacity-75">
                                        Total nilai jika dibeli terpisah
                                    </small>
                                    <strong>Rp1.700.000</strong>
                                </div>
                                <div class="text-md-end">
                                    <small class="d-block opacity-75">
                                        Semua dalam satu paket
                                    </small>
                                    <strong>Rp299.000</strong>
                                </div>
                            </div>
                        </div>

                        <p class="text-center text-secondary small mt-3 mb-0">
                            Nilai modul di atas merupakan ilustrasi pemasaran,
                            bukan harga jual satuan yang berlaku.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-space section-soft" id="bonus">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Bonus Pembelian</span>
                    <h2 class="section-title">
                        Bukan hanya source code
                    </h2>
                    <p class="section-description">
                        Pembeli juga memperoleh bahan pendukung agar lebih
                        mudah mempelajari dan menjalankan aplikasi.
                    </p>
                </div>

                <div class="row g-4 justify-content-center">
                    @foreach ([
                        ['bi-file-earmark-pdf', 'Manual Instalasi', 'Panduan persiapan database, konfigurasi environment, dan menjalankan aplikasi.'],
                        ['bi-database-check', 'Database Demo', 'Struktur database dan data awal agar aplikasi dapat langsung dipelajari.'],
                        ['bi-play-btn', 'Panduan Penggunaan', 'Panduan alur produk, POS, stok, order online, pelanggan, dan laporan.'],
                        ['bi-tools', 'Dukungan Awal', 'Bantuan untuk pertanyaan dasar instalasi sesuai ketentuan paket.'],
                    ] as [$icon, $title, $description])
                        <div class="col-md-6 col-lg-3">
                            <article class="bonus-card">
                                <span class="card-icon">
                                    <i class="bi {{ $icon }}"></i>
                                </span>
                                <h3>{{ $title }}</h3>
                                <p>{{ $description }}</p>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space" id="harga">
            <div class="container">
                <div class="row justify-content-center align-items-center g-5">
                    <div class="col-lg-5">
                        <span class="eyebrow">Penawaran Launching</span>
                        <h2 class="section-title">
                            Mulai jual atau gunakan aplikasi POS Anda sendiri
                        </h2>
                        <p class="section-description mb-4">
                            Dapatkan aplikasi Kasir Online Cerdas dengan modul
                            POS, order online, pelanggan, stok, pembayaran,
                            dashboard, dan laporan bisnis.
                        </p>

                        <div class="d-grid gap-3">
                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <strong class="text-dark d-block">
                                        Cocok untuk developer dan UMKM
                                    </strong>
                                    <span class="text-secondary">
                                        Dapat dipelajari, dipakai, dan dikembangkan.
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <strong class="text-dark d-block">
                                        Sekali bayar
                                    </strong>
                                    <span class="text-secondary">
                                        Tidak ada biaya langganan untuk source code.
                                    </span>
                                </div>
                            </div>

                            <div class="d-flex align-items-start gap-3">
                                <i class="bi bi-check-circle-fill text-success fs-5"></i>
                                <div>
                                    <strong class="text-dark d-block">
                                        Checkout aman
                                    </strong>
                                    <span class="text-secondary">
                                        Pembelian dilakukan melalui halaman Mayar.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <article class="offer-card">
                            <span class="offer-badge">
                                <i class="bi bi-fire"></i>
                                HARGA LAUNCHING
                            </span>

                            <h3 class="text-dark fw-bold">
                                Kasir Online Cerdas — Paket Source Code
                            </h3>

                            <div class="normal-price">Harga normal Rp799.000</div>
                            <div class="promo-price">Rp299.000</div>
                            <div class="text-secondary">
                                Bayar sekali · Source code lengkap
                            </div>

                            <ul class="offer-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Source code Laravel + Blade + Bootstrap
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    POS, stok, order online, pelanggan, dan laporan
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Database dan panduan instalasi
                                </li>
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Dapat digunakan dan dikembangkan sendiri
                                </li>
                            </ul>

                            <a
                                href="{{ $mayarUrl }}"
                                class="btn-accent w-100"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-cart-check-fill"></i>
                                Beli Sekarang melalui Mayar
                            </a>

                            <a
                                href="{{ $whatsappUrl }}"
                                class="btn-whatsapp w-100 mt-3"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-whatsapp"></i>
                                Tanya Admin Sebelum Membeli
                            </a>

                            <div class="security-note">
                                <i class="bi bi-lock-fill"></i>
                                Pembayaran aman · Produk digital · Tanpa langganan
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="section-space section-soft" id="demo">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Coba Aplikasinya</span>
                    <h2 class="section-title">
                        Jelajahi fitur sebelum membeli
                    </h2>
                    <p class="section-description">
                        Buka beberapa bagian utama untuk memahami alur kerja
                        Kasir Online Cerdas.
                    </p>
                </div>

                <div class="row g-4">
                    @foreach ([
                        ['bi-speedometer2', 'Dashboard', 'Lihat ringkasan penjualan, order, pembayaran, stok, dan aktivitas usaha.', route('dashboard'), 'Buka Dashboard'],
                        ['bi-cart-check', 'Kasir POS', 'Simulasikan transaksi, pilih produk, pelanggan, dan metode pembayaran.', route('pos.index'), 'Buka Kasir'],
                        ['bi-phone', 'Menu Online', 'Lihat menu publik dan proses pemesanan dari perangkat pelanggan.', route('public.menu'), 'Buka Menu Online'],
                        ['bi-file-earmark-bar-graph', 'Laporan Penjualan', 'Tinjau transaksi dan performa penjualan berdasarkan periode.', route('sales.report'), 'Buka Laporan'],
                    ] as [$icon, $title, $description, $url, $label])
                        <div class="col-sm-6 col-lg-3">
                            <article class="demo-card">
                                <span class="card-icon">
                                    <i class="bi {{ $icon }}"></i>
                                </span>
                                <h3>{{ $title }}</h3>
                                <p>{{ $description }}</p>
                                <a href="{{ $url }}" class="btn-main w-100">
                                    {{ $label }}
                                </a>
                            </article>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="section-space" id="faq">
            <div class="container">
                <div class="section-heading">
                    <span class="eyebrow">Pertanyaan Umum</span>
                    <h2 class="section-title">
                        Hal yang sering ditanyakan sebelum membeli
                    </h2>
                </div>

                <div class="row justify-content-center">
                    <div class="col-lg-9">
                        <div class="accordion" id="faqAccordion">
                            @foreach ([
                                ['Apakah ini aplikasi siap pakai?', 'Produk utama berupa source code aplikasi Laravel. Aplikasi dapat dijalankan di localhost atau hosting yang mendukung kebutuhan Laravel dan MySQL.'],
                                ['Apakah ada biaya bulanan?', 'Tidak ada biaya bulanan untuk membeli source code. Biaya hosting, domain, layanan pihak ketiga, dan pengembangan lanjutan ditanggung pengguna bila diperlukan.'],
                                ['Apakah source code dapat diubah?', 'Ya. Source code dapat dipelajari dan dikembangkan untuk kebutuhan sendiri sesuai ketentuan lisensi produk.'],
                                ['Apakah sudah mendukung QRIS?', 'Aplikasi mendukung pengaturan QRIS manual, transfer, tunai, EDC, dan COD. Integrasi payment gateway otomatis belum termasuk.'],
                                ['Apakah bisa digunakan untuk cafe dan toko?', 'Bisa. Struktur produk, stok, POS, pelanggan, dan laporan cocok untuk cafe, warung kopi, kuliner, toko kelontong, dan retail kecil.'],
                                ['Apakah sudah ada order online?', 'Ya. Pelanggan dapat membuka menu publik, menambahkan produk ke keranjang, checkout, mengunggah bukti pembayaran, dan melacak pesanan.'],
                                ['Apakah instalasi termasuk?', 'Harga source code tidak otomatis mencakup instalasi ke hosting. Layanan instalasi dan branding dapat ditawarkan sebagai paket terpisah.'],
                                ['Bagaimana produk dikirim setelah pembayaran?', 'Detail pengiriman produk, akses file, lisensi, dan panduan akan mengikuti pengaturan produk Anda di Mayar dan prosedur Ruang Cerdas.'],
                            ] as $index => [$question, $answer])
                                <div class="accordion-item">
                                    <h2 class="accordion-header">
                                        <button
                                            class="accordion-button {{ $index !== 0 ? 'collapsed' : '' }}"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#faq{{ $index }}"
                                            aria-expanded="{{ $index === 0 ? 'true' : 'false' }}"
                                            aria-controls="faq{{ $index }}"
                                        >
                                            {{ $question }}
                                        </button>
                                    </h2>

                                    <div
                                        id="faq{{ $index }}"
                                        class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}"
                                        data-bs-parent="#faqAccordion"
                                    >
                                        <div class="accordion-body text-secondary">
                                            {{ $answer }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="final-cta">
            <div class="container">
                <div class="final-cta-box">
                    <h2>
                        Siap memiliki aplikasi POS dan order online sendiri?
                    </h2>
                    <p>
                        Pelajari demonya terlebih dahulu atau lanjutkan
                        pembelian melalui Mayar. Untuk layanan instalasi,
                        branding, dan custom fitur, konsultasikan melalui WhatsApp.
                    </p>

                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <a
                            href="{{ $mayarUrl }}"
                            class="btn-white"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="bi bi-bag-check-fill"></i>
                            Beli Sekarang
                        </a>

                        <a
                            href="{{ $whatsappUrl }}"
                            class="btn-whatsapp"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="bi bi-whatsapp"></i>
                            Konsultasi WhatsApp
                        </a>

                        <a href="{{ route('dashboard') }}" class="btn-outline-main">
                            <i class="bi bi-play-circle"></i>
                            Coba Demo
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <a class="brand-logo" href="{{ route('product.demo') }}">
                        <span class="brand-icon">
                            <i class="bi bi-shop"></i>
                        </span>
                        <span>Kasir Online Cerdas</span>
                    </a>

                    <p>
                        Produk aplikasi dari Ruang Cerdas untuk membantu
                        UMKM mengelola transaksi, stok, order online,
                        pelanggan, pembayaran, dan laporan bisnis.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="footer-links">
                        <a href="#fitur">Fitur</a>
                        <a href="#yang-didapat">Yang Didapat</a>
                        <a href="#harga">Harga</a>
                        <a href="#demo">Demo</a>
                        <a href="#faq">FAQ</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom text-center text-md-start">
                &copy; {{ date('Y') }} Ruang Cerdas.
                Kasir Online Cerdas. Semua hak dilindungi.
            </div>
        </div>
    </footer>

    <div class="mobile-buy-bar">
        <div class="d-flex align-items-center justify-content-between gap-3">
            <div class="mobile-buy-price">
                <small>Rp799.000</small>
                <strong>Rp299.000</strong>
            </div>

            <a
                href="{{ $mayarUrl }}"
                class="btn-accent flex-grow-1"
                target="_blank"
                rel="noopener noreferrer"
            >
                <i class="bi bi-cart-check-fill"></i>
                Beli Sekarang
            </a>
        </div>
    </div>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
</body>
</html>
