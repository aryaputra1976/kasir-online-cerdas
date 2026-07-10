<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Checkout - {{ $storeSetting->store_name }}</title>

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
                padding: 20px;
            }

            .container {
                max-width: 1080px;
                margin: 0 auto;
            }

            .header,
            .card {
                background: #ffffff;
                border-radius: 22px;
                padding: 24px;
                margin-bottom: 18px;
                box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
            }

            .header {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: center;
                flex-wrap: wrap;
            }

            .store-name {
                font-size: 28px;
                font-weight: 800;
                margin-bottom: 6px;
            }

            .muted {
                color: #64748b;
            }

            .layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 380px;
                gap: 18px;
                align-items: start;
            }

            .form-group {
                margin-bottom: 14px;
            }

            .form-control {
                width: 100%;
                min-height: 48px;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                padding: 10px 12px;
                font-size: 15px;
                background: #ffffff;
            }

            textarea.form-control {
                min-height: 110px;
            }

            .btn {
                border: 0;
                background: #605dff;
                color: #ffffff;
                padding: 12px 16px;
                border-radius: 12px;
                font-size: 15px;
                text-decoration: none;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .btn-outline {
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #111827;
            }

            .alert-danger {
                background: #fee2e2;
                color: #b91c1c;
                padding: 14px 16px;
                border-radius: 14px;
                margin-bottom: 14px;
            }

            .item {
                border-bottom: 1px solid #eef2f7;
                padding: 12px 0;
            }

            .item:last-child {
                border-bottom: 0;
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

            .payment-box {
                border: 1px dashed #cbd5e1;
                border-radius: 14px;
                padding: 14px;
                background: #f8fafc;
                margin-top: 12px;
            }

            .qris-img {
                width: 240px;
                max-width: 100%;
                display: block;
                margin: 12px auto;
                border-radius: 14px;
                border: 1px solid #e5e7eb;
            }

            .d-none {
                display: none !important;
            }

            @media (max-width: 860px) {
                .layout {
                    grid-template-columns: 1fr;
                }
            }

            @media (max-width: 640px) {
                .page {
                    padding: 12px;
                }

                .header,
                .card {
                    padding: 18px;
                }

                .store-name {
                    font-size: 24px;
                }
            }
        </style>
    </head>

    <body>
        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
            $selectedPaymentMethod = old('payment_method', array_key_first($paymentMethods));
        @endphp

        <div class="page">
            <div class="container">
                <div class="header">
                    <div>
                        <div class="store-name">Checkout Order</div>
                        <div class="muted">{{ $storeSetting->store_name }}</div>
                    </div>

                    <a href="{{ route('public.menu') }}" class="btn btn-outline">
                        Kembali ke Menu
                    </a>
                </div>

                @if ($errors->any())
                    <div class="alert-danger">
                        <strong>Checkout belum bisa diproses.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('public.checkout.store') }}" method="post">
                    @csrf

                    <div class="layout">
                        <main>
                            <div class="card">
                                <h2 style="margin-top: 0;">Data Customer</h2>

                                <div class="form-group">
                                    <label>Nama Customer</label>
                                    <input
                                        type="text"
                                        name="customer_name"
                                        value="{{ old('customer_name') }}"
                                        class="form-control"
                                        placeholder="Nama lengkap"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Nomor HP / WhatsApp</label>
                                    <input
                                        type="text"
                                        name="customer_phone"
                                        value="{{ old('customer_phone') }}"
                                        class="form-control"
                                        placeholder="08xxxxxxxxxx"
                                        required
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Email</label>
                                    <input
                                        type="email"
                                        name="customer_email"
                                        value="{{ old('customer_email') }}"
                                        class="form-control"
                                        placeholder="Opsional"
                                    >
                                </div>

                                <div class="form-group">
                                    <label>Alamat Pengantaran</label>
                                    <textarea
                                        name="customer_address"
                                        class="form-control"
                                        placeholder="Alamat lengkap pengantaran"
                                        required
                                    >{{ old('customer_address') }}</textarea>
                                </div>

                                <div class="form-group">
                                    <label>Catatan Order</label>
                                    <textarea
                                        name="note"
                                        class="form-control"
                                        placeholder="Contoh: tanpa es, antar jam 14.00, dll."
                                    >{{ old('note') }}</textarea>
                                </div>
                            </div>

                            <div class="card">
                                <h2 style="margin-top: 0;">Metode Pembayaran</h2>

                                <div class="form-group">
                                    <label>Pilih Metode Pembayaran</label>
                                    <select name="payment_method" id="payment_method" class="form-control" required>
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
                                            {{ $storeSetting->qris_note ?: 'Scan QRIS sesuai total transaksi setelah order dibuat.' }}
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
                                            {{ $storeSetting->transfer_note ?: 'Upload bukti transfer setelah order dibuat.' }}
                                        </p>
                                    </div>
                                @endif

                                @if ($storeSetting?->payment_edc_enabled)
                                    <div class="payment-box d-none" id="payment-box-edc">
                                        <strong>EDC / Kartu</strong>
                                        <p class="muted">
                                            {{ $storeSetting->edc_note ?: 'Pastikan transaksi EDC berhasil sebelum upload bukti pembayaran.' }}
                                        </p>
                                    </div>
                                @endif
                            </div>
                        </main>

                        <aside>
                            <div class="card">
                                <h2 style="margin-top: 0;">Ringkasan Order</h2>

                                @foreach ($cartItems as $cartItem)
                                    <div class="item">
                                        <strong>{{ $cartItem['product']->name }}</strong>
                                        <div class="muted">
                                            {{ $cartItem['quantity'] }}
                                            {{ $cartItem['product']->unit }}
                                            x
                                            {{ $rupiah($cartItem['product']->selling_price) }}
                                        </div>
                                        <div>
                                            Subtotal:
                                            <strong>{{ $rupiah($cartItem['subtotal']) }}</strong>
                                        </div>
                                    </div>
                                @endforeach

                                <div style="margin-top: 14px;">
                                    <div class="row">
                                        <span>Subtotal</span>
                                        <strong>{{ $rupiah($totals['subtotal']) }}</strong>
                                    </div>

                                    <div class="row">
                                        <span>Pajak</span>
                                        <strong>{{ $rupiah($totals['tax_amount']) }}</strong>
                                    </div>

                                    <div class="row">
                                        <span>Total</span>
                                        <strong>{{ $rupiah($totals['total']) }}</strong>
                                    </div>
                                </div>

                                <button type="submit" class="btn" style="width: 100%; margin-top: 16px;">
                                    Buat Order Sekarang
                                </button>
                            </div>
                        </aside>
                    </div>
                </form>
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

                    if (!paymentMethodSelect) {
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
