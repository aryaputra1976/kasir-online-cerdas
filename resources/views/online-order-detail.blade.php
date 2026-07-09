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

                            <a href="{{ route('payments.index') }}" class="btn btn-outline-primary">
                                Pembayaran
                            </a>

                            <a href="{{ route('reports.sales') }}" class="btn btn-outline-success">
                                Laporan Penjualan
                            </a>
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
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <h3 class="mb-3">Ringkasan Pembayaran</h3>

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

                                    <div class="d-flex justify-content-between mb-3">
                                        <span class="fw-semibold">Total</span>
                                        <h4 class="fw-bold mb-0 koc-price">
                                            {{ $rupiah($order->total_amount) }}
                                        </h4>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block">Metode Pembayaran</span>
                                        <strong>{{ $order->payment_method_label }}</strong>

                                        @if ($isCashOrder)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Order Tunai / COD tidak memerlukan upload bukti pembayaran.
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block">Status Pembayaran</span>
                                        <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                            {{ $order->payment_status_label }}
                                        </span>

                                        @if ($order->payment_confirmed_at)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Dikonfirmasi pada {{ $order->payment_confirmed_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block">Status Stok</span>
                                        <span class="badge {{ $order->stock_status_class }} p-2 fs-12 fw-normal">
                                            {{ $order->stock_status_label }}
                                        </span>

                                        @if ($order->stock_deducted_at)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Dikurangi pada {{ $order->stock_deducted_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block">Status Order</span>
                                        <span class="badge {{ $order->status_class }} p-2 fs-12 fw-normal">
                                            {{ $order->status_label }}
                                        </span>

                                        @if ($order->processed_at)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Diproses pada {{ $order->processed_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif

                                        @if ($order->completed_at)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Selesai pada {{ $order->completed_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block">Status Laporan Penjualan</span>
                                        <span class="badge {{ $order->sale_conversion_status_class }} p-2 fs-12 fw-normal">
                                            {{ $order->sale_conversion_status_label }}
                                        </span>

                                        @if ($order->converted_to_sale_at)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Masuk laporan pada {{ $order->converted_to_sale_at->format('d/m/Y H:i') }}
                                            </p>
                                        @endif

                                        @if ($order->sale)
                                            <p class="text-body fs-13 mb-0 mt-1">
                                                Invoice penjualan: <strong>{{ $order->sale->invoice_no }}</strong>
                                            </p>

                                            <a
                                                href="{{ route('pos.receipt', $order->sale) }}"
                                                target="_blank"
                                                class="btn btn-outline-primary w-100 mt-2"
                                            >
                                                Lihat Struk Penjualan
                                            </a>
                                        @endif
                                    </div>

                                    @if ($order->canConvertToSale())
                                        <form
                                            action="{{ route('online-orders.convert-sale', $order) }}"
                                            method="post"
                                            class="mb-3"
                                            onsubmit="return confirm('Masukkan order ini ke laporan penjualan?')"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <button type="submit" class="btn btn-primary text-white w-100">
                                                Masukkan ke Laporan Penjualan
                                            </button>
                                        </form>
                                    @endif

                                    @if ($order->payment_note)
                                        <div class="mb-3">
                                            <span class="text-body fs-13 d-block">Catatan Customer</span>
                                            <p class="mb-0">{{ $order->payment_note }}</p>
                                        </div>
                                    @endif

                                    @if ($order->admin_payment_note)
                                        <div class="mb-3">
                                            <span class="text-body fs-13 d-block">Catatan Admin</span>
                                            <p class="mb-0">{{ $order->admin_payment_note }}</p>
                                        </div>
                                    @endif

                                    <div class="mb-3">
                                        <span class="text-body fs-13 d-block mb-2">Bukti Pembayaran</span>

                                        @if ($isCashOrder)
                                            <p class="text-body mb-0">
                                                Tidak diperlukan untuk Tunai / COD.
                                            </p>
                                        @elseif ($order->payment_proof_path)
                                            <a href="{{ asset('storage/' . $order->payment_proof_path) }}" target="_blank">
                                                <img
                                                    src="{{ asset('storage/' . $order->payment_proof_path) }}"
                                                    alt="Bukti pembayaran"
                                                    class="koc-proof-image"
                                                >
                                            </a>
                                        @else
                                            <p class="text-body mb-0">
                                                Belum ada bukti pembayaran.
                                            </p>
                                        @endif
                                    </div>

                                    @if ($order->canConfirmPayment())
                                        <div class="d-flex flex-column gap-2 mt-4">
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

                                    <hr>

                                    <h5 class="mb-3">Proses Order</h5>

                                    <div class="d-flex flex-column gap-2">
                                        @if ($canRunOrderAction && $order->canProcess())
                                            <form
                                                action="{{ route('online-orders.process', $order) }}"
                                                method="post"
                                                onsubmit="return confirm('Proses order ini dan kurangi stok jika belum dikurangi?')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-warning text-white w-100">
                                                    Proses Order
                                                </button>
                                            </form>
                                        @endif

                                        @if ($canRunOrderAction && $order->canComplete())
                                            <form
                                                action="{{ route('online-orders.complete', $order) }}"
                                                method="post"
                                                onsubmit="return confirm('Selesaikan order ini dan masukkan ke laporan penjualan?')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-success text-white w-100">
                                                    Selesaikan Order
                                                </button>
                                            </form>
                                        @endif

                                        @if ($order->canCancel())
                                            <form
                                                action="{{ route('online-orders.cancel', $order) }}"
                                                method="post"
                                                onsubmit="return confirm('Batalkan order ini?')"
                                            >
                                                @csrf
                                                @method('PATCH')

                                                <button type="submit" class="btn btn-outline-danger w-100">
                                                    Batalkan Order
                                                </button>
                                            </form>
                                        @endif

                                        @if (! ($canRunOrderAction && $order->canProcess()) && ! ($canRunOrderAction && $order->canComplete()) && ! $order->canCancel())
                                            <div class="alert alert-light border rounded-3 mb-0">
                                                Tidak ada aksi proses order yang tersedia untuk status saat ini.
                                            </div>
                                        @endif
                                    </div>

                                    <hr>

                                    <div class="fs-13 text-body">
                                        Link tracking customer:
                                        <a href="{{ route('public.tracking', $order->tracking_token) }}" target="_blank">
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

        @include('partials.theme_settings')
        @include('partials.scripts')
    </body>
</html>