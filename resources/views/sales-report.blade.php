<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Laporan Penjualan - Kasir Online Cerdas</title>

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

            .koc-sale-icon {
                width: 46px;
                height: 46px;
                min-width: 46px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-sale-list {
                display: flex;
                flex-direction: column;
                gap: 12px;
            }

            .koc-sale-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-sale-row:hover {
                background-color: #fafaff;
            }

            .koc-sale-meta {
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

            @media (max-width: 767.98px) {
                .koc-sale-row {
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

            $paymentMethods = [
                'CASH' => 'Tunai',
                'QRIS' => 'QRIS',
                'TRANSFER' => 'Transfer',
                'EDC' => 'EDC / Kartu',
            ];
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Laporan Penjualan</h3>
                            <p class="text-body mb-0">
                                Riwayat transaksi POS, omzet, metode pembayaran, dan detail item penjualan.
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
                                    <span class="fw-medium">Penjualan</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center gap-3">
                                        <div>
                                            <span class="d-block text-body mb-2">Total Omzet</span>
                                            <h3 class="fs-22 fw-semibold mb-0 koc-price">
                                                {{ $rupiah($totalOmzet) }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-success bg-opacity-10 text-success">
                                            <i class="material-symbols-outlined">payments</i>
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
                                            <span class="d-block text-body mb-2">Total Transaksi</span>
                                            <h3 class="fs-22 fw-semibold mb-0">
                                                {{ number_format($totalTransactions, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary">
                                            <i class="material-symbols-outlined">receipt_long</i>
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
                                            <span class="d-block text-body mb-2">Item Terjual</span>
                                            <h3 class="fs-22 fw-semibold mb-0">
                                                {{ number_format($totalItemsSold, 0, ',', '.') }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-info bg-opacity-10 text-info">
                                            <i class="material-symbols-outlined">shopping_bag</i>
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
                                            <span class="d-block text-body mb-2">Total Diskon</span>
                                            <h3 class="fs-22 fw-semibold mb-0 koc-price">
                                                {{ $rupiah($totalDiscount) }}
                                            </h3>
                                        </div>

                                        <div class="koc-summary-icon bg-warning bg-opacity-10 text-warning">
                                            <i class="material-symbols-outlined">sell</i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="koc-filter-card mb-4">
                                <form action="{{ route('reports.sales') }}" method="get">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-xl-3 col-lg-4 col-md-6">
                                            <div class="position-relative table-src-form me-0">
                                                <input
                                                    type="text"
                                                    name="q"
                                                    value="{{ $search }}"
                                                    class="form-control"
                                                    placeholder="Cari invoice, pelanggan, produk..."
                                                >
                                                <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                            </div>
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <select name="payment_method" class="form-select form-control">
                                                <option value="">Semua Pembayaran</option>
                                                @foreach ($paymentMethods as $value => $label)
                                                    <option value="{{ $value }}" @selected($paymentMethod === $value)>
                                                        {{ $label }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control">
                                        </div>

                                        <div class="col-xl-2 col-lg-3 col-md-6">
                                            <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control">
                                        </div>

                                        <div class="col-xl-auto col-lg-auto col-md-auto">
                                            <button type="submit" class="btn btn-outline-primary w-100">
                                                Filter
                                            </button>
                                        </div>

                                        @if ($search || $paymentMethod || $dateFrom || $dateTo)
                                            <div class="col-xl-auto col-lg-auto col-md-auto">
                                                <a href="{{ route('reports.sales') }}" class="btn btn-outline-secondary w-100">
                                                    Reset
                                                </a>
                                            </div>
                                        @endif

                                        <div class="col-xl-auto col-lg-auto col-md-auto ms-xl-auto ms-lg-auto">
                                            <a href="{{ route('pos.index') }}" class="btn btn-primary text-white px-4 w-100">
                                                Buka Kasir POS
                                            </a>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <div class="koc-sale-list">
                                @forelse ($sales as $sale)
                                    <div class="koc-sale-row">
                                        <div class="row align-items-center g-3">
                                            <div class="col-xl-4 col-lg-5 col-md-12">
                                                <div class="d-flex align-items-start">
                                                    <div class="koc-sale-icon bg-primary bg-opacity-10 text-primary me-3">
                                                        <i class="material-symbols-outlined fs-22">receipt_long</i>
                                                    </div>

                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $sale->invoice_no }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-0">
                                                            {{ $sale->customer_name ?: 'Customer Umum' }}
                                                        </p>

                                                        <div class="koc-sale-meta">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $sale->sale_date->format('d/m/Y H:i') }}
                                                            </span>

                                                            <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal">
                                                                {{ $sale->payment_method_label }}
                                                            </span>

                                                            <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                                {{ $sale->status_label }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Item</span>
                                                <h6 class="fw-semibold fs-18 mb-0">
                                                    {{ number_format($sale->items->sum('quantity'), 0, ',', '.') }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Total</span>
                                                <h6 class="fw-semibold fs-16 mb-0 koc-price">
                                                    {{ $rupiah($sale->total_amount) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-2 col-md-4">
                                                <span class="d-block fs-12 text-body mb-1">Bayar</span>
                                                <h6 class="fw-semibold fs-16 mb-0 koc-price">
                                                    {{ $rupiah($sale->paid_amount) }}
                                                </h6>
                                            </div>

                                            <div class="col-xl-2 col-lg-1 col-md-12">
                                                <div class="d-flex justify-content-xl-end gap-2">
                                                    <button
                                                        type="button"
                                                        class="btn btn-outline-primary btn-sm"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#saleDetailModal{{ $sale->id }}"
                                                    >
                                                        Detail
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($sale->note)
                                            <div class="mt-3 pt-3 border-top">
                                                <span class="fs-12 text-body d-block mb-1">Catatan</span>
                                                <p class="mb-0 fs-13">{{ $sale->note }}</p>
                                            </div>
                                        @endif
                                    </div>
                                @empty
                                    <div class="text-center py-5 bg-white rounded-3 border">
                                        <div class="mb-3">
                                            <i class="material-symbols-outlined text-body fs-40">receipt_long</i>
                                        </div>

                                        <h6 class="fw-semibold mb-1">Belum ada transaksi penjualan</h6>

                                        <p class="text-body mb-3">
                                            Data penjualan akan muncul setelah transaksi POS disimpan.
                                        </p>

                                        <a href="{{ route('pos.index') }}" class="btn btn-primary text-white">
                                            Buka Kasir POS
                                        </a>
                                    </div>
                                @endforelse

                                <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-3">
                                    <span class="fs-13 fw-medium">
                                        Menampilkan {{ $sales->count() }} dari {{ $sales->total() }} transaksi
                                    </span>

                                    <div>
                                        {{ $sales->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @foreach ($sales as $sale)
                        <div class="modal fade" id="saleDetailModal{{ $sale->id }}" tabindex="-1" aria-labelledby="saleDetailModalLabel{{ $sale->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content border-0 rounded-3">
                                    <div class="modal-header border-bottom">
                                        <div>
                                            <h5 class="modal-title" id="saleDetailModalLabel{{ $sale->id }}">
                                                Detail Transaksi
                                            </h5>
                                            <span class="fs-13 text-body">{{ $sale->invoice_no }}</span>
                                        </div>

                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                    </div>

                                    <div class="modal-body">
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <span class="fs-12 text-body d-block mb-1">Pelanggan</span>
                                                    <h6 class="mb-0">{{ $sale->customer_name ?: 'Customer Umum' }}</h6>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <span class="fs-12 text-body d-block mb-1">Tanggal</span>
                                                    <h6 class="mb-0">{{ $sale->sale_date->format('d/m/Y H:i') }}</h6>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <span class="fs-12 text-body d-block mb-1">Metode Pembayaran</span>
                                                    <h6 class="mb-0">{{ $sale->payment_method_label }}</h6>
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="border rounded-3 p-3 h-100">
                                                    <span class="fs-12 text-body d-block mb-1">Status</span>
                                                    <h6 class="mb-0">{{ $sale->status_label }}</h6>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="table-responsive mb-4">
                                            <table class="table align-middle">
                                                <thead>
                                                    <tr>
                                                        <th>Produk</th>
                                                        <th>SKU</th>
                                                        <th class="text-center">Qty</th>
                                                        <th class="text-end">Harga</th>
                                                        <th class="text-end">Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sale->items as $item)
                                                        <tr>
                                                            <td>
                                                                <h6 class="fs-14 fw-semibold mb-0">{{ $item->product_name }}</h6>
                                                                <span class="fs-12 text-body">{{ $item->unit }}</span>
                                                            </td>
                                                            <td>{{ $item->sku ?: '-' }}</td>
                                                            <td class="text-center">{{ number_format($item->quantity, 0, ',', '.') }}</td>
                                                            <td class="text-end">{{ $rupiah($item->unit_price) }}</td>
                                                            <td class="text-end fw-semibold">{{ $rupiah($item->subtotal_amount) }}</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="row justify-content-end">
                                            <div class="col-md-6 col-lg-5">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">Subtotal</span>
                                                    <strong>{{ $rupiah($sale->subtotal_amount) }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">Diskon</span>
                                                    <strong>{{ $rupiah($sale->discount_amount) }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">Pajak</span>
                                                    <strong>{{ $rupiah($sale->tax_amount) }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between border-top pt-2 mb-2">
                                                    <span class="fw-semibold">Total</span>
                                                    <h5 class="fw-bold mb-0">{{ $rupiah($sale->total_amount) }}</h5>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">Bayar</span>
                                                    <strong>{{ $rupiah($sale->paid_amount) }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between">
                                                    <span class="text-body">Kembalian</span>
                                                    <strong>{{ $rupiah($sale->change_amount) }}</strong>
                                                </div>
                                            </div>
                                        </div>

                                        @if ($sale->note)
                                            <div class="alert alert-light border mt-4 mb-0">
                                                <span class="fs-12 text-body d-block mb-1">Catatan</span>
                                                {{ $sale->note }}
                                            </div>
                                        @endif
                                    </div>

                                    <div class="modal-footer border-top">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                            Tutup
                                        </button>

                                        <a
                                            href="{{ route('pos.receipt', $sale) }}"
                                            target="_blank"
                                            class="btn btn-primary text-white"
                                        >
                                            Cetak Struk
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="flex-grow-1"></div>

                @include('partials.footer')
            </div>
        </div>

        @include('partials.theme_settings')
        @include('partials.scripts')
    </body>
</html>