<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Kasir Online Cerdas - Aplikasi POS dan Order Online UMKM</title>

    <meta
        name="description"
        content="Kasir Online Cerdas adalah aplikasi POS, stok, order online, pelanggan, dan laporan usaha untuk UMKM, cafe, warung kopi, dan toko retail."
    >

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
            --primary-color: #605dff;
            --primary-dark: #4b47db;
            --secondary-color: #f3f2ff;
            --dark-color: #1f2937;
            --muted-color: #64748b;
            --border-color: #e8e7f2;
            --success-color: #22c55e;
            --warning-color: #f59e0b;
            --page-background: #f8f9fc;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            margin: 0;
            color: var(--dark-color);
            background: #ffffff;
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

        .landing-navbar {
            background: rgba(255, 255, 255, 0.94);
            border-bottom: 1px solid rgba(232, 231, 242, 0.9);
            backdrop-filter: blur(14px);
        }

        .brand-logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            color: var(--dark-color);
            font-size: 1.1rem;
            font-weight: 800;
        }

        .brand-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: #ffffff;
            background: linear-gradient(
                135deg,
                var(--primary-color),
                #8b5cf6
            );
            border-radius: 13px;
            box-shadow: 0 10px 24px rgba(96, 93, 255, 0.25);
        }

        .navbar-nav .nav-link {
            color: #475569;
            font-weight: 600;
        }

        .navbar-nav .nav-link:hover {
            color: var(--primary-color);
        }

        .btn-primary-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 11px 22px;
            color: #ffffff;
            background: var(--primary-color);
            border: 1px solid var(--primary-color);
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn-primary-custom:hover {
            color: #ffffff;
            background: var(--primary-dark);
            border-color: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 12px 26px rgba(96, 93, 255, 0.25);
        }

        .btn-light-custom {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 46px;
            padding: 11px 22px;
            color: var(--dark-color);
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            font-weight: 700;
            transition: all 0.2s ease;
        }

        .btn-light-custom:hover {
            color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }

        .hero-section {
            position: relative;
            overflow: hidden;
            padding: 150px 0 100px;
            background:
                radial-gradient(
                    circle at 85% 20%,
                    rgba(96, 93, 255, 0.18),
                    transparent 30%
                ),
                radial-gradient(
                    circle at 10% 85%,
                    rgba(34, 197, 94, 0.12),
                    transparent 28%
                ),
                linear-gradient(180deg, #fafaff 0%, #ffffff 100%);
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 22px;
            padding: 8px 14px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border: 1px solid #dedcff;
            border-radius: 999px;
            font-size: 0.88rem;
            font-weight: 700;
        }

        .hero-title {
            max-width: 720px;
            margin-bottom: 20px;
            font-size: clamp(2.6rem, 6vw, 4.8rem);
            line-height: 1.05;
            letter-spacing: -0.04em;
            font-weight: 850;
        }

        .text-gradient {
            color: transparent;
            background: linear-gradient(
                135deg,
                var(--primary-color),
                #8b5cf6
            );
            background-clip: text;
            -webkit-background-clip: text;
        }

        .hero-description {
            max-width: 650px;
            margin-bottom: 30px;
            color: var(--muted-color);
            font-size: 1.1rem;
            line-height: 1.8;
        }

        .hero-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .hero-note {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 30px;
            color: #475569;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .hero-note span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .hero-note i {
            color: var(--success-color);
        }

        .dashboard-preview {
            position: relative;
            padding: 18px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid var(--border-color);
            border-radius: 24px;
            box-shadow: 0 30px 70px rgba(31, 41, 55, 0.14);
        }

        .preview-window {
            overflow: hidden;
            background: var(--page-background);
            border-radius: 17px;
        }

        .preview-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 16px;
            background: #ffffff;
            border-bottom: 1px solid var(--border-color);
        }

        .preview-dots {
            display: flex;
            gap: 6px;
        }

        .preview-dots span {
            display: block;
            width: 9px;
            height: 9px;
            background: #cbd5e1;
            border-radius: 50%;
        }

        .preview-content {
            padding: 18px;
        }

        .preview-stat {
            height: 100%;
            padding: 18px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
        }

        .preview-stat-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            margin-bottom: 14px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border-radius: 11px;
        }

        .preview-label {
            margin-bottom: 5px;
            color: var(--muted-color);
            font-size: 0.77rem;
        }

        .preview-value {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 800;
        }

        .preview-chart {
            margin-top: 16px;
            padding: 18px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
        }

        .chart-bars {
            display: flex;
            align-items: flex-end;
            gap: 10px;
            height: 150px;
            padding-top: 20px;
        }

        .chart-bars span {
            flex: 1;
            min-width: 12px;
            background: linear-gradient(
                180deg,
                var(--primary-color),
                #aaa8ff
            );
            border-radius: 7px 7px 2px 2px;
        }

        .floating-card {
            position: absolute;
            right: -25px;
            bottom: 42px;
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 190px;
            padding: 14px 16px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 14px;
            box-shadow: 0 18px 40px rgba(31, 41, 55, 0.14);
        }

        .floating-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            color: #ffffff;
            background: var(--success-color);
            border-radius: 12px;
        }

        .section-space {
            padding: 96px 0;
        }

        .section-soft {
            background: var(--page-background);
        }

        .section-heading {
            max-width: 700px;
            margin: 0 auto 50px;
            text-align: center;
        }

        .section-eyebrow {
            display: inline-block;
            margin-bottom: 12px;
            color: var(--primary-color);
            font-size: 0.82rem;
            font-weight: 800;
            letter-spacing: 0.12em;
            text-transform: uppercase;
        }

        .section-title {
            margin-bottom: 16px;
            font-size: clamp(2rem, 4vw, 3rem);
            letter-spacing: -0.03em;
            font-weight: 820;
        }

        .section-description {
            margin: 0;
            color: var(--muted-color);
            font-size: 1.02rem;
            line-height: 1.8;
        }

        .feature-card {
            height: 100%;
            padding: 28px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            transition: all 0.22s ease;
        }

        .feature-card:hover {
            transform: translateY(-6px);
            border-color: #cbc9ff;
            box-shadow: 0 18px 45px rgba(31, 41, 55, 0.08);
        }

        .feature-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 54px;
            height: 54px;
            margin-bottom: 20px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border-radius: 15px;
            font-size: 1.4rem;
        }

        .feature-card h3 {
            margin-bottom: 10px;
            font-size: 1.08rem;
            font-weight: 800;
        }

        .feature-card p {
            margin: 0;
            color: var(--muted-color);
            font-size: 0.94rem;
            line-height: 1.7;
        }

        .business-card {
            height: 100%;
            padding: 28px 20px;
            text-align: center;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
        }

        .business-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 64px;
            height: 64px;
            margin-bottom: 18px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border-radius: 18px;
            font-size: 1.65rem;
        }

        .business-card h3 {
            margin: 0;
            font-size: 1rem;
            font-weight: 800;
        }

        .workflow-wrapper {
            position: relative;
        }

        .workflow-item {
            position: relative;
            height: 100%;
            padding: 28px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
        }

        .workflow-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            margin-bottom: 18px;
            color: #ffffff;
            background: var(--primary-color);
            border-radius: 50%;
            font-weight: 800;
        }

        .workflow-item h3 {
            margin-bottom: 10px;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .workflow-item p {
            margin: 0;
            color: var(--muted-color);
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .pricing-card {
            position: relative;
            height: 100%;
            padding: 32px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 22px;
        }

        .pricing-card.featured {
            border: 2px solid var(--primary-color);
            box-shadow: 0 22px 55px rgba(96, 93, 255, 0.14);
        }

        .pricing-badge {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 10px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border-radius: 999px;
            font-size: 0.72rem;
            font-weight: 800;
        }

        .pricing-name {
            margin-bottom: 8px;
            font-size: 1.35rem;
            font-weight: 850;
        }

        .pricing-description {
            min-height: 50px;
            margin-bottom: 22px;
            color: var(--muted-color);
            font-size: 0.92rem;
            line-height: 1.6;
        }

        .pricing-price {
            margin-bottom: 5px;
            font-size: 2rem;
            font-weight: 850;
        }

        .pricing-note {
            margin-bottom: 25px;
            color: var(--muted-color);
            font-size: 0.85rem;
        }

        .pricing-list {
            display: grid;
            gap: 13px;
            margin: 0 0 28px;
            padding: 0;
            list-style: none;
        }

        .pricing-list li {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            color: #475569;
            font-size: 0.92rem;
        }

        .pricing-list i {
            color: var(--success-color);
        }

        .demo-card {
            height: 100%;
            padding: 28px;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 18px;
            transition: all 0.2s ease;
        }

        .demo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 18px 45px rgba(31, 41, 55, 0.08);
        }

        .demo-card-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 50px;
            height: 50px;
            margin-bottom: 18px;
            color: var(--primary-color);
            background: var(--secondary-color);
            border-radius: 14px;
            font-size: 1.3rem;
        }

        .demo-card h3 {
            margin-bottom: 10px;
            font-size: 1.05rem;
            font-weight: 800;
        }

        .demo-card p {
            min-height: 52px;
            margin-bottom: 20px;
            color: var(--muted-color);
            font-size: 0.9rem;
            line-height: 1.65;
        }

        .cta-section {
            padding: 90px 0;
        }

        .cta-wrapper {
            position: relative;
            overflow: hidden;
            padding: 70px 40px;
            color: #ffffff;
            text-align: center;
            background:
                radial-gradient(
                    circle at 10% 20%,
                    rgba(255, 255, 255, 0.17),
                    transparent 25%
                ),
                linear-gradient(
                    135deg,
                    var(--primary-color),
                    #7c3aed
                );
            border-radius: 28px;
        }

        .cta-wrapper h2 {
            max-width: 760px;
            margin: 0 auto 18px;
            font-size: clamp(2rem, 4vw, 3.1rem);
            font-weight: 850;
            letter-spacing: -0.03em;
        }

        .cta-wrapper p {
            max-width: 650px;
            margin: 0 auto 30px;
            color: rgba(255, 255, 255, 0.86);
            line-height: 1.8;
        }

        .cta-actions {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 12px;
        }

        .btn-white {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 47px;
            padding: 12px 24px;
            color: var(--primary-color);
            background: #ffffff;
            border: 1px solid #ffffff;
            border-radius: 12px;
            font-weight: 800;
            transition: all 0.2s ease;
        }

        .btn-white:hover {
            color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-outline-white {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            min-height: 47px;
            padding: 12px 24px;
            color: #ffffff;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.65);
            border-radius: 12px;
            font-weight: 800;
            transition: all 0.2s ease;
        }

        .btn-outline-white:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.12);
            border-color: #ffffff;
        }

        .landing-footer {
            padding: 50px 0 25px;
            background: #111827;
        }

        .footer-brand {
            color: #ffffff;
        }

        .footer-brand:hover {
            color: #ffffff;
        }

        .footer-description {
            max-width: 450px;
            margin-top: 15px;
            color: #94a3b8;
            font-size: 0.92rem;
            line-height: 1.7;
        }

        .footer-links {
            display: flex;
            flex-wrap: wrap;
            justify-content: flex-end;
            gap: 22px;
        }

        .footer-links a {
            color: #cbd5e1;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .footer-links a:hover {
            color: #ffffff;
        }

        .footer-bottom {
            margin-top: 35px;
            padding-top: 25px;
            color: #64748b;
            border-top: 1px solid #273244;
            font-size: 0.85rem;
        }

        @media (max-width: 991.98px) {
            .hero-section {
                padding-top: 125px;
            }

            .dashboard-preview {
                margin-top: 55px;
            }

            .floating-card {
                right: 15px;
            }

            .footer-links {
                justify-content: flex-start;
                margin-top: 25px;
            }
        }

        @media (max-width: 767.98px) {
            .hero-section {
                padding: 115px 0 75px;
            }

            .section-space {
                padding: 75px 0;
            }

            .hero-title {
                font-size: 2.7rem;
            }

            .hero-description {
                font-size: 1rem;
            }

            .hero-actions > a {
                width: 100%;
            }

            .hero-note {
                display: grid;
                gap: 10px;
            }

            .floating-card {
                position: static;
                margin-top: 16px;
            }

            .cta-section {
                padding: 70px 0;
            }

            .cta-wrapper {
                padding: 55px 22px;
                border-radius: 22px;
            }

            .cta-actions > a {
                width: 100%;
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

            <div
                class="collapse navbar-collapse"
                id="landingNavbar"
            >
                <ul class="navbar-nav mx-auto mb-3 mb-lg-0 gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="#fitur">Fitur</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#cocok-untuk">
                            Cocok Untuk
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#alur">Cara Kerja</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#paket">Paket</a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="#demo">Demo</a>
                    </li>
                </ul>

                <a
                    href="{{ route('dashboard') }}"
                    class="btn-primary-custom"
                >
                    <i class="bi bi-grid-1x2-fill"></i>
                    Masuk Dashboard
                </a>
            </div>
        </div>
    </nav>

    <main>
        <section class="hero-section">
            <div class="container">
                <div class="row align-items-center g-5">
                    <div class="col-lg-7">
                        <span class="hero-badge">
                            <i class="bi bi-stars"></i>
                            Solusi Kasir Digital untuk UMKM
                        </span>

                        <h1 class="hero-title">
                            Kelola usaha lebih mudah dengan
                            <span class="text-gradient">
                                Kasir Online Cerdas
                            </span>
                        </h1>

                        <p class="hero-description">
                            Aplikasi POS dan order online untuk membantu
                            UMKM mengelola transaksi kasir, produk, stok,
                            pembayaran, pelanggan, serta laporan bisnis
                            dalam satu sistem.
                        </p>

                        <div class="hero-actions">
                            <a
                                href="{{ route('pos.index') }}"
                                class="btn-primary-custom"
                            >
                                <i class="bi bi-cart-check-fill"></i>
                                Coba Demo Kasir
                            </a>

                            <a
                                href="{{ route('public.menu') }}"
                                class="btn-light-custom"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <i class="bi bi-phone"></i>
                                Lihat Menu Online
                            </a>

                            <a
                                href="{{ route('dashboard') }}"
                                class="btn-light-custom"
                            >
                                <i class="bi bi-speedometer2"></i>
                                Masuk Dashboard
                            </a>
                        </div>

                        <div class="hero-note">
                            <span>
                                <i class="bi bi-check-circle-fill"></i>
                                Mudah digunakan
                            </span>

                            <span>
                                <i class="bi bi-check-circle-fill"></i>
                                Tampilan responsive
                            </span>

                            <span>
                                <i class="bi bi-check-circle-fill"></i>
                                Cocok untuk UMKM
                            </span>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="dashboard-preview">
                            <div class="preview-window">
                                <div class="preview-header">
                                    <div class="preview-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>

                                    <small class="text-secondary fw-semibold">
                                        Dashboard Bisnis
                                    </small>
                                </div>

                                <div class="preview-content">
                                    <div class="row g-3">
                                        <div class="col-6">
                                            <div class="preview-stat">
                                                <span class="preview-stat-icon">
                                                    <i class="bi bi-cash-stack"></i>
                                                </span>

                                                <div class="preview-label">
                                                    Penjualan
                                                </div>

                                                <p class="preview-value">
                                                    Rp4,8 Jt
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="preview-stat">
                                                <span class="preview-stat-icon">
                                                    <i class="bi bi-bag-check"></i>
                                                </span>

                                                <div class="preview-label">
                                                    Order Online
                                                </div>

                                                <p class="preview-value">
                                                    128 Order
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="preview-stat">
                                                <span class="preview-stat-icon">
                                                    <i class="bi bi-box-seam"></i>
                                                </span>

                                                <div class="preview-label">
                                                    Produk Aktif
                                                </div>

                                                <p class="preview-value">
                                                    65 Produk
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <div class="preview-stat">
                                                <span class="preview-stat-icon">
                                                    <i class="bi bi-people"></i>
                                                </span>

                                                <div class="preview-label">
                                                    Pelanggan
                                                </div>

                                                <p class="preview-value">
                                                    245 Orang
                                                </p>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="preview-chart">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <strong class="d-block">
                                                    Tren Penjualan
                                                </strong>

                                                <small class="text-secondary">
                                                    Ringkasan transaksi
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

                            <div class="floating-card">
                                <span class="floating-icon">
                                    <i class="bi bi-check-lg"></i>
                                </span>

                                <div>
                                    <small class="text-secondary d-block">
                                        Transaksi berhasil
                                    </small>

                                    <strong>Stok otomatis terupdate</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="section-space"
            id="fitur"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-eyebrow">Fitur Utama</span>

                    <h2 class="section-title">
                        Semua kebutuhan operasional usaha dalam satu aplikasi
                    </h2>

                    <p class="section-description">
                        Dari transaksi kasir sampai laporan bisnis,
                        Kasir Online Cerdas membantu pekerjaan harian
                        menjadi lebih teratur dan mudah dipantau.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-cart-check"></i>
                            </span>

                            <h3>Kasir POS</h3>

                            <p>
                                Proses transaksi penjualan dengan keranjang
                                belanja, metode pembayaran, pajak, dan struk.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-boxes"></i>
                            </span>

                            <h3>Produk & Stok</h3>

                            <p>
                                Kelola kategori, produk, stok tersedia,
                                stok menipis, dan seluruh mutasi barang.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-phone"></i>
                            </span>

                            <h3>Order Online</h3>

                            <p>
                                Pelanggan dapat melihat menu, membuat order,
                                checkout, dan melacak status pesanan.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-qr-code-scan"></i>
                            </span>

                            <h3>Pembayaran Fleksibel</h3>

                            <p>
                                Mendukung pembayaran tunai, QRIS, transfer,
                                kartu EDC, dan COD sesuai kebutuhan usaha.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                            </span>

                            <h3>Laporan Penjualan</h3>

                            <p>
                                Pantau transaksi, omzet, metode pembayaran,
                                produk terlaris, dan laporan order online.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-graph-up-arrow"></i>
                            </span>

                            <h3>Laba Rugi Sederhana</h3>

                            <p>
                                Lihat ringkasan pendapatan, modal produk,
                                laba kotor, serta margin penjualan.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-people"></i>
                            </span>

                            <h3>Data Pelanggan</h3>

                            <p>
                                Simpan profil pelanggan, riwayat transaksi,
                                total pembelian, dan aktivitas terakhir.
                            </p>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="feature-card">
                            <span class="feature-icon">
                                <i class="bi bi-speedometer2"></i>
                            </span>

                            <h3>Dashboard Analitik</h3>

                            <p>
                                Pantau kondisi usaha melalui ringkasan
                                penjualan, order, pembayaran, dan stok.
                            </p>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="section-space section-soft"
            id="cocok-untuk"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-eyebrow">Cocok Digunakan</span>

                    <h2 class="section-title">
                        Dibuat untuk berbagai jenis usaha kecil
                    </h2>

                    <p class="section-description">
                        Tampilan sederhana dan fitur yang praktis membuat
                        aplikasi mudah disesuaikan untuk kebutuhan usaha.
                    </p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-6 col-md-4 col-lg">
                        <div class="business-card">
                            <span class="business-icon">
                                <i class="bi bi-cup-hot"></i>
                            </span>

                            <h3>Cafe</h3>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg">
                        <div class="business-card">
                            <span class="business-icon">
                                <i class="bi bi-cup-straw"></i>
                            </span>

                            <h3>Warung Kopi</h3>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg">
                        <div class="business-card">
                            <span class="business-icon">
                                <i class="bi bi-shop-window"></i>
                            </span>

                            <h3>Toko Kelontong</h3>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg">
                        <div class="business-card">
                            <span class="business-icon">
                                <i class="bi bi-basket"></i>
                            </span>

                            <h3>UMKM Kuliner</h3>
                        </div>
                    </div>

                    <div class="col-6 col-md-4 col-lg">
                        <div class="business-card">
                            <span class="business-icon">
                                <i class="bi bi-bag"></i>
                            </span>

                            <h3>Toko Retail</h3>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="section-space"
            id="alur"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-eyebrow">Alur Kerja</span>

                    <h2 class="section-title">
                        Mulai dari produk sampai laporan bisnis
                    </h2>

                    <p class="section-description">
                        Proses operasional dirancang berurutan agar mudah
                        dipahami oleh pemilik usaha maupun petugas kasir.
                    </p>
                </div>

                <div class="workflow-wrapper">
                    <div class="row g-4">
                        <div class="col-md-6 col-lg">
                            <article class="workflow-item">
                                <span class="workflow-number">1</span>

                                <h3>Input Produk</h3>

                                <p>
                                    Tambahkan kategori, nama produk, harga,
                                    stok awal, dan batas minimum stok.
                                </p>
                            </article>
                        </div>

                        <div class="col-md-6 col-lg">
                            <article class="workflow-item">
                                <span class="workflow-number">2</span>

                                <h3>Transaksi POS</h3>

                                <p>
                                    Pilih produk, pelanggan, metode pembayaran,
                                    lalu selesaikan transaksi kasir.
                                </p>
                            </article>
                        </div>

                        <div class="col-md-6 col-lg">
                            <article class="workflow-item">
                                <span class="workflow-number">3</span>

                                <h3>Order Online</h3>

                                <p>
                                    Pelanggan membuat pesanan melalui
                                    halaman menu online yang responsive.
                                </p>
                            </article>
                        </div>

                        <div class="col-md-6 col-lg">
                            <article class="workflow-item">
                                <span class="workflow-number">4</span>

                                <h3>Stok Otomatis</h3>

                                <p>
                                    Stok barang diperbarui saat transaksi
                                    atau order online berhasil diproses.
                                </p>
                            </article>
                        </div>

                        <div class="col-md-6 col-lg">
                            <article class="workflow-item">
                                <span class="workflow-number">5</span>

                                <h3>Laporan Bisnis</h3>

                                <p>
                                    Pemilik usaha dapat melihat omzet,
                                    produk terlaris, stok, dan laba rugi.
                                </p>
                            </article>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="section-space section-soft"
            id="paket"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-eyebrow">Paket Produk</span>

                    <h2 class="section-title">
                        Pilihan paket sesuai kebutuhan usaha
                    </h2>

                    <p class="section-description">
                        Paket berikut merupakan contoh penawaran produk.
                        Harga dan cakupan layanan dapat disesuaikan kembali
                        sebelum dipublikasikan.
                    </p>
                </div>

                <div class="row g-4 justify-content-center">
                    <div class="col-md-6 col-lg-4">
                        <article class="pricing-card">
                            <h3 class="pricing-name">Basic</h3>

                            <p class="pricing-description">
                                Cocok untuk usaha kecil yang baru mulai
                                menggunakan aplikasi kasir digital.
                            </p>

                            <div class="pricing-price">
                                Hubungi Kami
                            </div>

                            <div class="pricing-note">
                                Paket dasar aplikasi POS
                            </div>

                            <ul class="pricing-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Kasir POS dan cetak struk
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Manajemen produk dan kategori
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Stok dan mutasi barang
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Laporan penjualan
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Pengaturan profil toko
                                </li>
                            </ul>

                            <a
                                href="{{ route('pos.index') }}"
                                class="btn-light-custom w-100"
                            >
                                Lihat Demo Basic
                            </a>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <article class="pricing-card featured">
                            <span class="pricing-badge">
                                PALING LENGKAP
                            </span>

                            <h3 class="pricing-name">Pro</h3>

                            <p class="pricing-description">
                                Cocok untuk usaha yang membutuhkan POS,
                                order online, pelanggan, dan analitik.
                            </p>

                            <div class="pricing-price">
                                Hubungi Kami
                            </div>

                            <div class="pricing-note">
                                Paket lengkap untuk operasional usaha
                            </div>

                            <ul class="pricing-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Semua fitur paket Basic
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Menu dan checkout online
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    QRIS, transfer, COD, dan EDC
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Manajemen dan analitik pelanggan
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Dashboard analitik bisnis
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Laporan laba rugi sederhana
                                </li>
                            </ul>

                            <a
                                href="{{ route('dashboard') }}"
                                class="btn-primary-custom w-100"
                            >
                                Lihat Demo Pro
                            </a>
                        </article>
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <article class="pricing-card">
                            <h3 class="pricing-name">Custom</h3>

                            <p class="pricing-description">
                                Cocok untuk usaha yang memerlukan branding,
                                fitur, dan penyesuaian khusus.
                            </p>

                            <div class="pricing-price">
                                Sesuai Kebutuhan
                            </div>

                            <div class="pricing-note">
                                Pengembangan berdasarkan permintaan
                            </div>

                            <ul class="pricing-list">
                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Semua fitur paket Pro
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Branding dan identitas usaha
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Penyesuaian fitur tertentu
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Penyesuaian laporan
                                </li>

                                <li>
                                    <i class="bi bi-check-circle-fill"></i>
                                    Dukungan implementasi
                                </li>
                            </ul>

                            <a
                                href="#demo"
                                class="btn-light-custom w-100"
                            >
                                Jelajahi Aplikasi
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section
            class="section-space"
            id="demo"
        >
            <div class="container">
                <div class="section-heading">
                    <span class="section-eyebrow">Demo Aplikasi</span>

                    <h2 class="section-title">
                        Jelajahi fitur Kasir Online Cerdas
                    </h2>

                    <p class="section-description">
                        Buka langsung halaman utama aplikasi untuk melihat
                        alur kasir, menu pelanggan, dan laporan penjualan.
                    </p>
                </div>

                <div class="row g-4">
                    <div class="col-sm-6 col-lg-3">
                        <article class="demo-card">
                            <span class="demo-card-icon">
                                <i class="bi bi-speedometer2"></i>
                            </span>

                            <h3>Dashboard</h3>

                            <p>
                                Lihat ringkasan penjualan, order online,
                                pembayaran, stok, dan aktivitas usaha.
                            </p>

                            <a
                                href="{{ route('dashboard') }}"
                                class="btn-primary-custom w-100"
                            >
                                Buka Dashboard
                            </a>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="demo-card">
                            <span class="demo-card-icon">
                                <i class="bi bi-cart-check"></i>
                            </span>

                            <h3>Kasir POS</h3>

                            <p>
                                Simulasikan transaksi kasir, pilih produk,
                                pelanggan, dan metode pembayaran.
                            </p>

                            <a
                                href="{{ route('pos.index') }}"
                                class="btn-primary-custom w-100"
                            >
                                Buka Kasir
                            </a>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="demo-card">
                            <span class="demo-card-icon">
                                <i class="bi bi-phone"></i>
                            </span>

                            <h3>Menu Online</h3>

                            <p>
                                Lihat tampilan menu pelanggan dan proses
                                pemesanan melalui perangkat mobile.
                            </p>

                            <a
                                href="{{ route('public.menu') }}"
                                class="btn-primary-custom w-100"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                Buka Menu
                            </a>
                        </article>
                    </div>

                    <div class="col-sm-6 col-lg-3">
                        <article class="demo-card">
                            <span class="demo-card-icon">
                                <i class="bi bi-file-earmark-bar-graph"></i>
                            </span>

                            <h3>Laporan</h3>

                            <p>
                                Tinjau transaksi dan performa penjualan
                                berdasarkan periode serta pembayaran.
                            </p>

                            <a
                                href="{{ route('sales.report') }}"
                                class="btn-primary-custom w-100"
                            >
                                Buka Laporan
                            </a>
                        </article>
                    </div>
                </div>
            </div>
        </section>

        <section class="cta-section">
            <div class="container">
                <div class="cta-wrapper">
                    <h2>
                        Siap membantu usaha bekerja lebih cepat dan teratur
                    </h2>

                    <p>
                        Gunakan Kasir Online Cerdas untuk mengelola
                        transaksi, stok, order online, pelanggan,
                        dan laporan bisnis dari satu aplikasi.
                    </p>

                    <div class="cta-actions">
                        <a
                            href="{{ route('pos.index') }}"
                            class="btn-white"
                        >
                            <i class="bi bi-cart-check-fill"></i>
                            Coba Demo Kasir
                        </a>

                        <a
                            href="{{ route('public.menu') }}"
                            class="btn-outline-white"
                            target="_blank"
                            rel="noopener noreferrer"
                        >
                            <i class="bi bi-phone"></i>
                            Lihat Menu Online
                        </a>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <a
                        class="brand-logo footer-brand"
                        href="{{ route('product.demo') }}"
                    >
                        <span class="brand-icon">
                            <i class="bi bi-shop"></i>
                        </span>

                        <span>Kasir Online Cerdas</span>
                    </a>

                    <p class="footer-description">
                        Aplikasi POS dan order online untuk membantu
                        UMKM mengelola transaksi, stok, pelanggan,
                        pembayaran, serta laporan bisnis.
                    </p>
                </div>

                <div class="col-lg-6">
                    <div class="footer-links">
                        <a href="#fitur">Fitur</a>
                        <a href="#cocok-untuk">Cocok Untuk</a>
                        <a href="#paket">Paket</a>
                        <a href="#demo">Demo</a>
                        <a href="{{ route('dashboard') }}">Dashboard</a>
                    </div>
                </div>
            </div>

            <div class="footer-bottom text-center text-md-start">
                &copy; {{ date('Y') }} Kasir Online Cerdas.
                Semua hak dilindungi.
            </div>
        </div>
    </footer>

    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
    ></script>
</body>
</html>