<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Detail Order {{ $order->order_no }} - Kasir Online Cerdas</title>
        @include('partials.styles')

        <style>
            .koc-info-card {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px;
                background: #ffffff;
            }

            .koc-item-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 14px;
                background: #ffffff;
            }

            .koc-proof-image {
                width: 100%;
                max-width: 360px;
                border-radius: 16px;
                border: 1px solid #eef0f7;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }

            .koc-sale-box {
                border: 1px dashed #d7dbec;
                border-radius: 14px;
                padding: 14px;
                background: #fbfcff;
            }

            .koc-compact-box {
                border: 1px solid #eef0f7;
                border-radius: 12px;
                padding: 12px;
                background: #fbfcff;
            }

            .koc-status-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                gap: 16px;
                padding-bottom: 14px;
                margin-bottom: 14px;
                border-bottom: 1px solid #eef0f7;
            }

            @media (max-width: 575.98px) {
                .koc-status-row {
                    align-items: flex-start;
                    flex-direction: column;
                    gap: 8px;
                }

                .koc-status-row small {
                    text-align: left !important;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

            $isCashOrder = $order->payment_method === \App\Models\Sale::PAYMENT_CASH;
            $isPaidOrder = $order->payment_status === \App\Models\OnlineOrder::PAYMENT_PAID;
            $canRunOrderAction = $isCashOrder || $isPaidOrder;
            $currentUser = auth()->user();
            $canManagePayment = $currentUser?->hasAnyRole([
                \App\Models\User::ROLE_OWNER,
                \App\Models\User::ROLE_ADMIN,
            ]) ?? false;
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Detail Order Online</h3>
                            <p class="text-body mb-0">
                                Detail order, item, pembayaran, stok, proses pesanan, dan status laporan penjualan.
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('online-orders.index') }}" class="btn btn-outline-secondary">
                                Order Online
                            </a>

                            @if ($canManagePayment)
                                <a href="{{ route('payments.index') }}" class="btn btn-outline-primary">
                                    Pembayaran
                                </a>

                                <a href="{{ route('sales.report') }}" class="btn btn-outline-success">
                                    Laporan Penjualan
                                </a>
                            @endif
                        </div>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <strong>Proses belum bisa dilanjutkan.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-xl-7">
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">{{ $order->order_no }}</h3>
                                            <p class="text-body mb-0">
                                                Dibuat: {{ $order->created_at->format('d/m/Y H:i') }}
                                            </p>
                                        </div>

                                        <div class="d-flex flex-wrap gap-2">
                                            <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->payment_status_label }}
                                            </span>

                                            <span class="badge {{ $order->stock_status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->stock_status_label }}
                                            </span>

                                            <span class="badge {{ $order->status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->status_label }}
                                            </span>

                                            <span class="badge {{ $order->sale_conversion_status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->sale_conversion_status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="row g-3 mb-4">
                                        <div class="col-md-6">
                                            <div class="koc-info-card h-100">
                                                <span class="text-body fs-13 d-block mb-1">Customer</span>
                                                <h5 class="mb-1">{{ $order->customer_name }}</h5>
                                                <p class="mb-1">{{ $order->customer_phone ?: '-' }}</p>
                                                <p class="text-body mb-0 fs-13">
                                                    {{ $order->customer_email ?: '-' }}
                                                </p>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="koc-info-card h-100">
                                                <span class="text-body fs-13 d-block mb-1">Alamat</span>
                                                <p class="mb-0">{{ $order->customer_address ?: '-' }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($order->note)
                                        <div class="koc-info-card mb-4">
                                            <span class="text-body fs-13 d-block mb-1">Catatan Order</span>
                                            <p class="mb-0">{{ $order->note }}</p>
                                        </div>
                                    @endif

                                    <h4 class="mb-3">Item Order</h4>

                                    <div class="d-flex flex-column gap-2">
                                        @foreach ($order->items as $item)
                                            <div class="koc-item-row">
                                                <div class="row g-3 align-items-center">
                                                    <div class="col-md-5">
                                                        <h6 class="fw-semibold mb-1">{{ $item->product_name }}</h6>
                                                        <p class="text-body fs-13 mb-0">
                                                            SKU: {{ $item->sku ?: '-' }}
                                                        </p>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <span class="text-body fs-13 d-block">Qty</span>
                                                        <strong>
                                                            {{ number_format($item->quantity, 0, ',', '.') }}
                                                            {{ $item->unit }}
                                                        </strong>
                                                    </div>

                                                    <div class="col-md-2">
                                                        <span class="text-body fs-13 d-block">Harga</span>
                                                        <strong>{{ $rupiah($item->unit_price) }}</strong>
                                                    </div>

                                                    <div class="col-md-3 text-md-end">
                                                        <span class="text-body fs-13 d-block">Subtotal</span>
                                                        <strong>{{ $rupiah($item->subtotal_amount) }}</strong>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-5">
                            {{-- Card 1: Ringkasan Pembayaran --}}
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <div>
                                            <h3 class="mb-1">Ringkasan Pembayaran</h3>
                                            <p class="text-body fs-13 mb-0">Rincian nilai dan metode pembayaran order.</p>
                                        </div>

                                        <span class="material-symbols-outlined text-primary fs-2">
                                            payments
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-body">Subtotal</span>
                                        <strong>{{ $rupiah($order->subtotal_amount) }}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-body">Diskon</span>
                                        <strong>{{ $rupiah($order->discount_amount) }}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-body">Pajak</span>
                                        <strong>{{ $rupiah($order->tax_amount) }}</strong>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-body">Ongkir</span>
                                        <strong>{{ $rupiah($order->shipping_amount) }}</strong>
                                    </div>

                                    <hr>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="fw-semibold">Total</span>
                                        <h4 class="fw-bold mb-0 koc-price">
                                            {{ $rupiah($order->total_amount) }}
                                        </h4>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-sm-6">
                                            <div class="koc-compact-box h-100">
                                                <span class="text-body fs-13 d-block mb-1">
                                                    Metode Pembayaran
                                                </span>
                                                <strong>{{ $order->payment_method_label }}</strong>
                                            </div>
                                        </div>

                                        <div class="col-sm-6">
                                            <div class="koc-compact-box h-100">
                                                <span class="text-body fs-13 d-block mb-1">
                                                    Status Pembayaran
                                                </span>
                                                <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                                    {{ $order->payment_status_label }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($isCashOrder)
                                        <div class="alert alert-light border rounded-3 fs-13 mt-3 mb-0">
                                            Tunai/COD tidak memerlukan upload bukti pembayaran.
                                        </div>
                                    @elseif ($order->payment_confirmed_at)
                                        <p class="text-body fs-13 mt-3 mb-0">
                                            Dikonfirmasi pada {{ $order->payment_confirmed_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>

                            {{-- Card 2: Status Order --}}
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <div>
                                            <h3 class="mb-1">Status Order</h3>
                                            <p class="text-body fs-13 mb-0">Perkembangan stok, order, dan laporan penjualan.</p>
                                        </div>

                                        <span class="material-symbols-outlined text-info fs-2">
                                            fact_check
                                        </span>
                                    </div>

                                    <div class="koc-status-row">
                                        <div>
                                            <span class="text-body fs-13 d-block mb-1">Status Stok</span>
                                            <span class="badge {{ $order->stock_status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->stock_status_label }}
                                            </span>
                                        </div>

                                        @if ($order->stock_deducted_at)
                                            <small class="text-body text-end">
                                                {{ $order->stock_deducted_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </div>

                                    <div class="koc-status-row">
                                        <div>
                                            <span class="text-body fs-13 d-block mb-1">Status Pesanan</span>
                                            <span class="badge {{ $order->status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->status_label }}
                                            </span>
                                        </div>

                                        <small class="text-body text-end">
                                            @if ($order->completed_at)
                                                Selesai {{ $order->completed_at->format('d/m/Y H:i') }}
                                            @elseif ($order->processed_at)
                                                Diproses {{ $order->processed_at->format('d/m/Y H:i') }}
                                            @else
                                                Dibuat {{ $order->created_at->format('d/m/Y H:i') }}
                                            @endif
                                        </small>
                                    </div>

                                    <div class="koc-status-row border-bottom-0 pb-0 mb-0">
                                        <div>
                                            <span class="text-body fs-13 d-block mb-1">
                                                Status Laporan Penjualan
                                            </span>
                                            <span class="badge {{ $order->sale_conversion_status_class }} p-2 fs-12 fw-normal">
                                                {{ $order->sale_conversion_status_label }}
                                            </span>
                                        </div>

                                        @if ($order->converted_to_sale_at)
                                            <small class="text-body text-end">
                                                {{ $order->converted_to_sale_at->format('d/m/Y H:i') }}
                                            </small>
                                        @endif
                                    </div>

                                    @if ($order->sale)
                                        <div class="koc-sale-box mt-3">
                                            <div class="d-flex justify-content-between gap-3 mb-2">
                                                <span class="text-body fs-13">Invoice Penjualan</span>
                                                <strong>{{ $order->sale->invoice_no }}</strong>
                                            </div>

                                            <a
                                                href="{{ route('pos.receipt', $order->sale) }}"
                                                target="_blank"
                                                class="btn btn-outline-primary w-100"
                                            >
                                                Lihat Struk Penjualan
                                            </a>
                                        </div>
                                    @endif

                                    @if ($order->payment_note || $order->admin_payment_note)
                                        <hr>

                                        @if ($order->payment_note)
                                            <div class="mb-3">
                                                <span class="text-body fs-13 d-block mb-1">Catatan Customer</span>
                                                <p class="mb-0">{{ $order->payment_note }}</p>
                                            </div>
                                        @endif

                                        @if ($order->admin_payment_note)
                                            <div>
                                                <span class="text-body fs-13 d-block mb-1">Catatan Admin</span>
                                                <p class="mb-0">{{ $order->admin_payment_note }}</p>
                                            </div>
                                        @endif
                                    @endif

                                    @if (! $isCashOrder)
                                        <hr>

                                        <span class="text-body fs-13 d-block mb-2">Bukti Pembayaran</span>

                                        @if ($order->payment_proof_path)
                                            <a
                                                href="{{ asset('storage/' . $order->payment_proof_path) }}"
                                                target="_blank"
                                                class="d-inline-block"
                                            >
                                                <img
                                                    src="{{ asset('storage/' . $order->payment_proof_path) }}"
                                                    alt="Bukti pembayaran"
                                                    class="koc-proof-image"
                                                >
                                            </a>
                                        @else
                                            <div class="alert alert-light border rounded-3 mb-0">
                                                Belum ada bukti pembayaran.
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>

                            {{-- Card 3: Aksi Order --}}
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-center justify-content-between gap-3 mb-3">
                                        <div>
                                            <h3 class="mb-1">Aksi Order</h3>
                                            <p class="text-body fs-13 mb-0">Pilih tindakan sesuai status order saat ini.</p>
                                        </div>

                                        <span class="material-symbols-outlined text-warning fs-2">
                                            touch_app
                                        </span>
                                    </div>

                                    @if ($canManagePayment && $order->canConfirmPayment())
                                        <div class="d-flex flex-column gap-2 mb-3">
                                            <form
                                                action="{{ route('payments.confirm', $order) }}"
                                                method="post"
                                                onsubmit="return confirm('Konfirmasi pembayaran order ini dan kurangi stok otomatis?')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-success text-white w-100">
                                                    Konfirmasi Pembayaran
                                                </button>
                                            </form>

                                            <form
                                                action="{{ route('payments.reject', $order) }}"
                                                method="post"
                                                onsubmit="return confirm('Tolak pembayaran order ini?')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <textarea
                                                    name="admin_payment_note"
                                                    rows="3"
                                                    class="form-control mb-2"
                                                    placeholder="Alasan penolakan pembayaran"
                                                    required
                                                ></textarea>

                                                <button type="submit" class="btn btn-outline-danger w-100">
                                                    Tolak Pembayaran
                                                </button>
                                            </form>
                                        </div>
                                    @endif

                                    @if ($canManagePayment && $order->canConvertToSale())
                                        <form
                                            action="{{ route('online-orders.convert-sale', $order) }}"
                                            method="post"
                                            class="mb-3"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-primary text-white w-100">
                                                Masukkan ke Laporan Penjualan
                                            </button>
                                        </form>
                                    @endif

                                    <div class="d-flex flex-column gap-2">
                                        @if ($order->canConfirmCod())
                                            <form
                                                id="confirmCodOrderForm"
                                                action="{{ route('online-orders.confirm-cod', $order) }}"
                                                method="post"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="button"
                                                    class="btn btn-info text-white w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#confirmCodOrderModal"
                                                >
                                                    <span class="material-symbols-outlined align-middle fs-18 me-1">
                                                        fact_check
                                                    </span>
                                                    Konfirmasi Pesanan COD
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canRunOrderAction && $order->canProcess())
                                            <form
                                                id="processOrderForm"
                                                action="{{ route('online-orders.process', $order) }}"
                                                method="post"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="button"
                                                    class="btn btn-warning text-white w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#processOrderModal"
                                                >
                                                    <span class="material-symbols-outlined align-middle fs-18 me-1">
                                                        pending_actions
                                                    </span>
                                                    Proses Order
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canRunOrderAction && $order->canComplete())
                                            <form
                                                id="completeOrderForm"
                                                action="{{ route('online-orders.complete', $order) }}"
                                                method="post"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="button"
                                                    class="btn btn-success text-white w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#completeOrderModal"
                                                >
                                                    <span class="material-symbols-outlined align-middle fs-18 me-1">
                                                        task_alt
                                                    </span>
                                                    Selesaikan Order
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canManagePayment && $order->canCancel())
                                            <form
                                                id="cancelOrderForm"
                                                action="{{ route('online-orders.cancel', $order) }}"
                                                method="post"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button
                                                    type="button"
                                                    class="btn btn-outline-danger w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#cancelOrderModal"
                                                >
                                                    <span class="material-symbols-outlined align-middle fs-18 me-1">
                                                        cancel
                                                    </span>
                                                    Batalkan Order
                                                </button>
                                            </form>
                                        @endif

                                        @if (
                                            ! ($canRunOrderAction && $order->canProcess())
                                            && ! ($canRunOrderAction && $order->canComplete())
                                            && ! $order->canConfirmCod()
                                            && ! ($canManagePayment && $order->canCancel())
                                            && ! ($canManagePayment && $order->canConfirmPayment())
                                            && ! ($canManagePayment && $order->canConvertToSale())
                                        )
                                            <div class="alert alert-light border rounded-3 mb-0">
                                                Tidak ada aksi order yang tersedia untuk status saat ini.
                                            </div>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="fs-13 text-body">
                                        Link tracking customer:
                                        <a
                                            href="{{ route('public.tracking', $order->tracking_token) }}"
                                            target="_blank"
                                        >
                                            Buka Tracking
                                        </a>
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

        {{-- Modal Konfirmasi Pesanan COD --}}
        @if ($order->canConfirmCod())
            <div
                class="modal fade"
                id="confirmCodOrderModal"
                tabindex="-1"
                aria-labelledby="confirmCodOrderModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div
                                    class="rounded-circle bg-info bg-opacity-10 text-info
                                        d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 76px; height: 76px;"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 40px;">
                                        fact_check
                                    </span>
                                </div>

                                <h4 id="confirmCodOrderModalLabel" class="fw-bold mb-2">
                                    Konfirmasi Pesanan COD?
                                </h4>

                                <p class="text-body mb-0">
                                    Pastikan pelanggan telah mengonfirmasi pesanan melalui WhatsApp
                                    atau telepon. Stok belum akan dikurangi pada tahap ini.
                                </p>
                            </div>

                            <div class="border rounded-3 p-3 mb-4 bg-light">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Nomor Order</span>
                                    <strong class="text-end">{{ $order->order_no }}</strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Customer</span>
                                    <strong class="text-end">{{ $order->customer_name }}</strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">WhatsApp</span>
                                    <strong class="text-end">{{ $order->customer_phone ?: '-' }}</strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Metode Pembayaran</span>
                                    <strong class="text-end">{{ $order->payment_method_label }}</strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-body">Total</span>
                                    <strong>{{ $rupiah($order->total_amount) }}</strong>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light border flex-fill"
                                    data-bs-dismiss="modal"
                                >
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    form="confirmCodOrderForm"
                                    class="btn btn-info text-white flex-fill"
                                >
                                    Ya, Konfirmasi Pesanan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Modal Proses Order --}}
        @if ($canRunOrderAction && $order->canProcess())
            <div
                class="modal fade"
                id="processOrderModal"
                tabindex="-1"
                aria-labelledby="processOrderModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div
                                    class="rounded-circle bg-warning bg-opacity-10 text-warning
                                        d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 76px; height: 76px;"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 40px;">
                                        inventory
                                    </span>
                                </div>

                                <h4 id="processOrderModalLabel" class="fw-bold mb-2">
                                    Proses Order?
                                </h4>

                                <p class="text-body mb-0">
                                    Order
                                    <strong>{{ $order->order_no }}</strong>
                                    akan mulai diproses.
                                </p>
                            </div>

                            <div class="alert alert-warning border-0 rounded-3 mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="material-symbols-outlined flex-shrink-0">
                                        warning
                                    </span>

                                    <div>
                                        <strong>Stok produk akan diperbarui.</strong>

                                        <div class="fs-13 mt-1">
                                            Stok otomatis dikurangi jika sebelumnya belum pernah
                                            dikurangi untuk order ini.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded-3 p-3 mb-4 bg-light">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Customer</span>
                                    <strong class="text-end">
                                        {{ $order->customer_name }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Jumlah Item</span>
                                    <strong>
                                        {{ number_format($order->items->sum('quantity'), 0, ',', '.') }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-body">Total</span>
                                    <strong>{{ $rupiah($order->total_amount) }}</strong>
                                </div>
                            </div>

                            @if ($isCashOrder)
                                <div class="form-check border rounded-3 p-3 ps-5 mb-4 bg-light">
                                    <input
                                        class="form-check-input"
                                        type="checkbox"
                                        name="cod_payment_received"
                                        id="codPaymentReceived"
                                        value="1"
                                        form="completeOrderForm"
                                        required
                                    >
                                    <label class="form-check-label fw-semibold" for="codPaymentReceived">
                                        Saya memastikan pembayaran Tunai / COD sudah diterima.
                                    </label>
                                </div>
                            @endif

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light border flex-fill"
                                    data-bs-dismiss="modal"
                                >
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    form="processOrderForm"
                                    class="btn btn-warning text-white flex-fill"
                                >
                                    Ya, Proses Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        {{-- Modal Selesaikan Order --}}
        @if ($canRunOrderAction && $order->canComplete())
            <div
                class="modal fade"
                id="completeOrderModal"
                tabindex="-1"
                aria-labelledby="completeOrderModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div
                                    class="rounded-circle bg-success bg-opacity-10 text-success
                                        d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 76px; height: 76px;"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 40px;">
                                        task_alt
                                    </span>
                                </div>

                                <h4 id="completeOrderModalLabel" class="fw-bold mb-2">
                                    Selesaikan Order?
                                </h4>

                                <p class="text-body mb-0">
                                    Order
                                    <strong>{{ $order->order_no }}</strong>
                                    akan ditandai selesai.
                                </p>
                            </div>

                            <div class="alert alert-success border-0 rounded-3 mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="material-symbols-outlined flex-shrink-0">
                                        receipt_long
                                    </span>

                                    <div>
                                        <strong>Transaksi penjualan akan dibuat otomatis.</strong>

                                        <div class="fs-13 mt-1">
                                            Order akan masuk ke laporan penjualan.
                                            Untuk Tunai/COD, pembayaran akan ditandai sudah dibayar.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border rounded-3 p-3 mb-4 bg-light">
                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Customer</span>
                                    <strong class="text-end">
                                        {{ $order->customer_name }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-2">
                                    <span class="text-body">Metode Pembayaran</span>
                                    <strong class="text-end">
                                        {{ $order->payment_method_label }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-body">Total</span>
                                    <strong>{{ $rupiah($order->total_amount) }}</strong>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light border flex-fill"
                                    data-bs-dismiss="modal"
                                >
                                    Batal
                                </button>

                                <button
                                    type="submit"
                                    form="completeOrderForm"
                                    class="btn btn-success text-white flex-fill"
                                >
                                    Ya, Selesaikan Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif


        {{-- Modal Batalkan Order --}}
        @if ($canManagePayment && $order->canCancel())
            <div
                class="modal fade"
                id="cancelOrderModal"
                tabindex="-1"
                aria-labelledby="cancelOrderModalLabel"
                aria-hidden="true"
            >
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content border-0 rounded-4 shadow-lg">
                        <div class="modal-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <div
                                    class="rounded-circle bg-danger bg-opacity-10 text-danger
                                        d-inline-flex align-items-center justify-content-center mb-3"
                                    style="width: 76px; height: 76px;"
                                >
                                    <span class="material-symbols-outlined" style="font-size: 40px;">
                                        cancel
                                    </span>
                                </div>

                                <h4 id="cancelOrderModalLabel" class="fw-bold mb-2">
                                    Batalkan Order?
                                </h4>

                                <p class="text-body mb-0">
                                    Order
                                    <strong>{{ $order->order_no }}</strong>
                                    akan dibatalkan.
                                </p>
                            </div>

                            <div class="alert alert-danger border-0 rounded-3 mb-4">
                                <div class="d-flex align-items-start gap-2">
                                    <span class="material-symbols-outlined flex-shrink-0">
                                        error
                                    </span>

                                    <div>
                                        <strong>Periksa kembali sebelum melanjutkan.</strong>

                                        <div class="fs-13 mt-1">
                                            Order yang sudah dibatalkan tidak dapat diproses atau
                                            diselesaikan kembali.
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex flex-column flex-sm-row gap-2">
                                <button
                                    type="button"
                                    class="btn btn-light border flex-fill"
                                    data-bs-dismiss="modal"
                                >
                                    Kembali
                                </button>

                                <button
                                    type="submit"
                                    form="cancelOrderForm"
                                    class="btn btn-danger text-white flex-fill"
                                >
                                    Ya, Batalkan Order
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @include('partials.theme_settings')
        @include('partials.scripts')
    </body>
</html>
