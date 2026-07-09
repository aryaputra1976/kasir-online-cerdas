<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tracking Order {{ $order->order_no }}</title>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
                background: #f3f4f6;
                color: #111827;
            }

            .page {
                min-height: 100vh;
                padding: 24px;
            }

            .container {
                max-width: 920px;
                margin: 0 auto;
            }

            .card {
                background: #ffffff;
                border-radius: 18px;
                padding: 24px;
                margin-bottom: 18px;
                box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
            }

            .header {
                text-align: center;
            }

            .store-name {
                font-size: 26px;
                font-weight: 700;
                margin-bottom: 6px;
            }

            .muted {
                color: #64748b;
            }

            .badge {
                display: inline-flex;
                padding: 8px 12px;
                border-radius: 999px;
                font-size: 13px;
                font-weight: 600;
                background: #eef2ff;
                color: #4f46e5;
            }

            .badge-success {
                background: #dcfce7;
                color: #15803d;
            }

            .badge-warning {
                background: #fef3c7;
                color: #b45309;
            }

            .badge-danger {
                background: #fee2e2;
                color: #b91c1c;
            }

            .grid {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 16px;
            }

            .row {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 8px 0;
                border-bottom: 1px solid #eef2f7;
            }

            .row:last-child {
                border-bottom: 0;
            }

            .item {
                border: 1px solid #eef2f7;
                border-radius: 14px;
                padding: 14px;
                margin-bottom: 10px;
            }

            .form-control {
                width: 100%;
                min-height: 46px;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                padding: 10px 12px;
                font-size: 15px;
                background: #ffffff;
            }

            textarea.form-control {
                min-height: 90px;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .btn {
                border: 0;
                border-radius: 12px;
                padding: 12px 16px;
                font-size: 15px;
                cursor: pointer;
                background: #605dff;
                color: #ffffff;
                width: 100%;
            }

            .btn-outline {
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #111827;
                text-decoration: none;
                display: inline-flex;
                justify-content: center;
                align-items: center;
            }

            .alert-success {
                background: #dcfce7;
                color: #15803d;
                padding: 14px 16px;
                border-radius: 12px;
                margin-bottom: 18px;
            }

            .alert-danger {
                background: #fee2e2;
                color: #b91c1c;
                padding: 14px 16px;
                border-radius: 12px;
                margin-bottom: 18px;
            }

            .payment-box {
                border: 1px dashed #cbd5e1;
                border-radius: 14px;
                padding: 14px;
                background: #f8fafc;
                margin-top: 12px;
            }

            .payment-box-success {
                border-color: #bbf7d0;
                background: #f0fdf4;
            }

            .payment-box-warning {
                border-color: #fde68a;
                background: #fffbeb;
            }

            .payment-box-danger {
                border-color: #fecaca;
                background: #fef2f2;
            }

            .qris-img {
                width: 240px;
                max-width: 100%;
                display: block;
                margin: 12px auto;
                border-radius: 14px;
                border: 1px solid #e5e7eb;
            }

            .proof-img {
                width: 240px;
                max-width: 100%;
                border-radius: 14px;
                border: 1px solid #e5e7eb;
            }

            .d-none {
                display: none !important;
            }

            .action-row {
                display: flex;
                gap: 10px;
                flex-wrap: wrap;
                margin-top: 16px;
            }

            .action-row a {
                flex: 1;
            }

            @media (max-width: 768px) {
                .page {
                    padding: 14px;
                }

                .grid {
                    grid-template-columns: 1fr;
                }

                .card {
                    padding: 18px;
                }
            }
        </style>
    </head>

    <body>
        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

            $storeName = $storeSetting?->store_name ?: 'Kasir Online Cerdas';

            $isCashOrder = $order->payment_method === \App\Models\Sale::PAYMENT_CASH;
            $isUnpaid = $order->payment_status === \App\Models\OnlineOrder::PAYMENT_UNPAID;
            $isWaitingConfirmation = $order->payment_status === \App\Models\OnlineOrder::PAYMENT_WAITING_CONFIRMATION;
            $isPaid = $order->payment_status === \App\Models\OnlineOrder::PAYMENT_PAID;
            $isRejected = $order->payment_status === \App\Models\OnlineOrder::PAYMENT_REJECTED;

            $paymentBadgeClass = match ($order->payment_status) {
                \App\Models\OnlineOrder::PAYMENT_PAID => 'badge-success',
                \App\Models\OnlineOrder::PAYMENT_WAITING_CONFIRMATION => 'badge-warning',
                \App\Models\OnlineOrder::PAYMENT_REJECTED => 'badge-danger',
                default => '',
            };

            $selectedPaymentMethod = old('payment_method', $order->payment_method ?? '');
        @endphp

        <div class="page">
            <div class="container">
                <div class="card header">
                    <div class="store-name">{{ $storeName }}</div>
                    <div class="muted">Tracking Order Online</div>
                </div>

                @if (session('success'))
                    <div class="alert-success">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert-danger">
                        <strong>Data belum bisa dikirim.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="grid">
                    <div class="card">
                        <h2 style="margin-top: 0;">{{ $order->order_no }}</h2>

                        <p class="muted">
                            Customer: <strong>{{ $order->customer_name }}</strong>
                        </p>

                        <p>
                            <span class="badge {{ $paymentBadgeClass }}">
                                {{ $order->payment_status_label }}
                            </span>
                        </p>

                        <div class="row">
                            <span>Status Order</span>
                            <strong>{{ $order->status_label }}</strong>
                        </div>

                        <div class="row">
                            <span>Metode Pembayaran</span>
                            <strong>{{ $order->payment_method_label }}</strong>
                        </div>

                        <div class="row">
                            <span>Total</span>
                            <strong>{{ $rupiah($order->total_amount) }}</strong>
                        </div>
                    </div>

                    <div class="card">
                        <h3 style="margin-top: 0;">Ringkasan Pembayaran</h3>

                        <div class="row">
                            <span>Subtotal</span>
                            <strong>{{ $rupiah($order->subtotal_amount) }}</strong>
                        </div>

                        <div class="row">
                            <span>Diskon</span>
                            <strong>{{ $rupiah($order->discount_amount) }}</strong>
                        </div>

                        <div class="row">
                            <span>Pajak</span>
                            <strong>{{ $rupiah($order->tax_amount) }}</strong>
                        </div>

                        <div class="row">
                            <span>Ongkir</span>
                            <strong>{{ $rupiah($order->shipping_amount) }}</strong>
                        </div>

                        <div class="row">
                            <span>Total</span>
                            <strong>{{ $rupiah($order->total_amount) }}</strong>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <h3 style="margin-top: 0;">Item Order</h3>

                    @foreach ($order->items as $item)
                        <div class="item">
                            <strong>{{ $item->product_name }}</strong>

                            <div class="muted">
                                {{ number_format($item->quantity, 0, ',', '.') }}
                                {{ $item->unit }}
                                x
                                {{ $rupiah($item->unit_price) }}
                            </div>

                            <div>
                                Subtotal: <strong>{{ $rupiah($item->subtotal_amount) }}</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="card">
                    <h3 style="margin-top: 0;">Pembayaran Order</h3>

                    @if ($isCashOrder && $isUnpaid)
                        <div class="payment-box payment-box-warning">
                            <strong>Pembayaran Tunai / COD</strong>

                            <p class="muted">
                                Pembayaran dilakukan secara tunai saat pesanan diterima.
                                Anda tidak perlu upload bukti pembayaran.
                            </p>

                            <div class="row">
                                <span>Metode Pembayaran</span>
                                <strong>{{ $order->payment_method_label }}</strong>
                            </div>

                            <div class="row">
                                <span>Status Pembayaran</span>
                                <strong>{{ $order->payment_status_label }}</strong>
                            </div>

                            <div class="row">
                                <span>Total Tagihan</span>
                                <strong>{{ $rupiah($order->total_amount) }}</strong>
                            </div>
                        </div>

                    @elseif ($isPaid)
                        <div class="payment-box payment-box-success">
                            <strong>Pembayaran Sudah Dikonfirmasi</strong>

                            <p class="muted">
                                Pembayaran Anda sudah diterima dan dikonfirmasi oleh admin.
                            </p>

                            <div class="row">
                                <span>Metode Pembayaran</span>
                                <strong>{{ $order->payment_method_label }}</strong>
                            </div>

                            <div class="row">
                                <span>Total Dibayar</span>
                                <strong>{{ $rupiah($order->total_amount) }}</strong>
                            </div>

                            @if ($order->payment_confirmed_at)
                                <div class="row">
                                    <span>Dikonfirmasi</span>
                                    <strong>{{ $order->payment_confirmed_at->format('d/m/Y H:i') }}</strong>
                                </div>
                            @endif
                        </div>

                    @elseif ($isWaitingConfirmation)
                        <div class="payment-box">
                            <strong>Bukti Pembayaran Sedang Diperiksa</strong>

                            <p class="muted">
                                Bukti pembayaran sudah berhasil dikirim.
                                Silakan tunggu konfirmasi admin.
                            </p>

                            <div class="row">
                                <span>Metode Pembayaran</span>
                                <strong>{{ $order->payment_method_label }}</strong>
                            </div>

                            @if ($order->payment_note)
                                <div class="row">
                                    <span>Catatan</span>
                                    <strong>{{ $order->payment_note }}</strong>
                                </div>
                            @endif

                            @if ($order->payment_proof_path)
                                <div style="margin-top: 14px;">
                                    <span class="muted">Bukti pembayaran:</span>

                                    <a
                                        href="{{ asset('storage/' . $order->payment_proof_path) }}"
                                        target="_blank"
                                        style="display: block; margin-top: 8px;"
                                    >
                                        <img
                                            src="{{ asset('storage/' . $order->payment_proof_path) }}"
                                            alt="Bukti pembayaran"
                                            class="proof-img"
                                        >
                                    </a>
                                </div>
                            @endif
                        </div>

                    @else
                        @if ($isRejected)
                            <div class="payment-box payment-box-danger">
                                <strong>Pembayaran Ditolak</strong>

                                <p class="muted">
                                    Bukti pembayaran sebelumnya ditolak.
                                    Silakan kirim ulang bukti pembayaran yang benar.
                                </p>

                                @if ($order->admin_payment_note)
                                    <p class="muted">
                                        Catatan admin: {{ $order->admin_payment_note }}
                                    </p>
                                @endif
                            </div>
                        @endif

                        <form
                            action="{{ route('public.payment-proof.upload', $order->tracking_token) }}"
                            method="post"
                            enctype="multipart/form-data"
                        >
                            @csrf

                            <div class="form-group">
                                <label>Metode Pembayaran</label>

                                <select name="payment_method" id="payment_method" class="form-control" required>
                                    <option value="" disabled @selected($selectedPaymentMethod === '')>
                                        Pilih metode pembayaran
                                    </option>

                                    @foreach ($paymentMethods as $methodValue => $methodLabel)
                                        <option value="{{ $methodValue }}" @selected($selectedPaymentMethod === $methodValue)>
                                            {{ $methodLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="payment-box d-none" id="payment-box-cash">
                                <strong>Tunai / COD</strong>
                                <p class="muted">
                                    Pembayaran dilakukan secara tunai saat pesanan diterima.
                                    Bukti pembayaran tidak wajib diupload untuk metode ini.
                                </p>
                            </div>

                            @if ($storeSetting?->payment_qris_enabled)
                                <div class="payment-box d-none" id="payment-box-qris">
                                    <strong>QRIS</strong>

                                    @if ($storeSetting->qris_image_path)
                                        <img
                                            src="{{ asset('storage/' . $storeSetting->qris_image_path) }}"
                                            alt="QRIS"
                                            class="qris-img"
                                        >
                                    @endif

                                    <p class="muted">
                                        {{ $storeSetting->qris_note ?: 'Scan QRIS sesuai total transaksi.' }}
                                    </p>
                                </div>
                            @endif

                            @if ($storeSetting?->payment_transfer_enabled)
                                <div class="payment-box d-none" id="payment-box-transfer">
                                    <strong>Transfer Bank</strong>

                                    <p class="muted" style="margin-bottom: 4px;">
                                        Bank: {{ $storeSetting->bank_name ?: '-' }}
                                    </p>

                                    <p class="muted" style="margin-bottom: 4px;">
                                        No. Rekening: {{ $storeSetting->bank_account_number ?: '-' }}
                                    </p>

                                    <p class="muted" style="margin-bottom: 4px;">
                                        Atas Nama: {{ $storeSetting->bank_account_name ?: '-' }}
                                    </p>

                                    <p class="muted">
                                        {{ $storeSetting->transfer_note ?: 'Upload bukti transfer setelah melakukan pembayaran.' }}
                                    </p>
                                </div>
                            @endif

                            @if ($storeSetting?->payment_edc_enabled)
                                <div class="payment-box d-none" id="payment-box-edc">
                                    <strong>EDC / Kartu</strong>

                                    <p class="muted">
                                        {{ $storeSetting->edc_note ?: 'Pastikan transaksi EDC berhasil sebelum mengirim bukti pembayaran.' }}
                                    </p>
                                </div>
                            @endif

                            <div class="form-group" style="margin-top: 14px;">
                                <label>Bukti Pembayaran</label>

                                <input
                                    type="file"
                                    name="payment_proof"
                                    class="form-control"
                                    accept="image/png,image/jpeg,image/webp"
                                >

                                <p class="muted">
                                    Wajib untuk QRIS, Transfer, atau EDC. Maksimal 2 MB.
                                    Tidak wajib untuk Tunai / COD.
                                </p>
                            </div>

                            <div class="form-group">
                                <label>Catatan Pembayaran</label>

                                <textarea
                                    name="payment_note"
                                    class="form-control"
                                    placeholder="Contoh: Sudah transfer dari rekening atas nama ..."
                                >{{ old('payment_note', $order->payment_note) }}</textarea>
                            </div>

                            <button type="submit" class="btn">
                                Kirim Bukti Pembayaran
                            </button>
                        </form>
                    @endif
                </div>

                <div class="action-row">
                    <a href="{{ route('public.menu') }}" class="btn btn-outline">
                        Kembali ke Menu
                    </a>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const paymentMethodSelect = document.getElementById('payment_method');

                const boxes = {
                    CASH: document.getElementById('payment-box-cash'),
                    QRIS: document.getElementById('payment-box-qris'),
                    TRANSFER: document.getElementById('payment-box-transfer'),
                    EDC: document.getElementById('payment-box-edc'),
                };

                const refreshPaymentBox = function () {
                    Object.values(boxes).forEach(function (box) {
                        if (box) {
                            box.classList.add('d-none');
                        }
                    });

                    if (! paymentMethodSelect) {
                        return;
                    }

                    const selected = paymentMethodSelect.value;

                    if (boxes[selected]) {
                        boxes[selected].classList.remove('d-none');
                    }
                };

                if (paymentMethodSelect) {
                    paymentMethodSelect.addEventListener('change', refreshPaymentBox);
                    refreshPaymentBox();
                }
            });
        </script>
    </body>
</html>