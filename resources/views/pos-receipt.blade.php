<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        @php
            $storeName = $storeSetting?->store_name ?: 'Kasir Online Cerdas';
        @endphp

        <title>Struk {{ $sale->invoice_no }} - {{ $storeName }}</title>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                background: #f3f4f6;
                font-family: Arial, Helvetica, sans-serif;
                color: #111827;
            }

            .page-wrapper {
                min-height: 100vh;
                padding: 24px;
                display: flex;
                flex-direction: column;
                align-items: center;
                gap: 18px;
            }

            .toolbar {
                width: 100%;
                max-width: 420px;
                display: flex;
                justify-content: space-between;
                align-items: center;
                gap: 10px;
                flex-wrap: wrap;
            }

            .toolbar-group {
                display: flex;
                gap: 8px;
                flex-wrap: wrap;
            }

            .btn {
                border: 1px solid #d1d5db;
                background: #ffffff;
                color: #111827;
                padding: 9px 12px;
                border-radius: 8px;
                font-size: 14px;
                text-decoration: none;
                cursor: pointer;
            }

            .btn-primary {
                border-color: #605dff;
                background: #605dff;
                color: #ffffff;
            }

            .receipt-paper {
                width: 80mm;
                max-width: 100%;
                background: #ffffff;
                padding: 14px 12px;
                box-shadow: 0 14px 35px rgba(15, 23, 42, 0.16);
            }

            .receipt-center {
                text-align: center;
            }

            .receipt-logo {
                width: 52px;
                height: 52px;
                object-fit: contain;
                display: block;
                margin: 0 auto 6px;
            }

            .store-name {
                font-size: 16px;
                font-weight: 700;
                text-transform: uppercase;
                margin-bottom: 4px;
            }

            .store-info {
                font-size: 11px;
                line-height: 1.35;
                margin: 0;
            }

            .divider {
                border-top: 1px dashed #111827;
                margin: 10px 0;
            }

            .receipt-row {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                font-size: 12px;
                line-height: 1.4;
            }

            .receipt-row span:first-child {
                color: #374151;
            }

            .receipt-row strong {
                font-weight: 700;
                text-align: right;
            }

            .item {
                margin-bottom: 8px;
                font-size: 12px;
            }

            .item-name {
                font-weight: 700;
                margin-bottom: 3px;
            }

            .item-detail {
                display: flex;
                justify-content: space-between;
                gap: 8px;
                line-height: 1.4;
            }

            .item-detail strong {
                text-align: right;
            }

            .total-row {
                font-size: 13px;
                font-weight: 700;
            }

            .footer-note {
                font-size: 11px;
                line-height: 1.4;
                margin: 0;
            }

            .small-text {
                font-size: 10px;
                color: #4b5563;
            }

            @page {
                size: 80mm auto;
                margin: 0;
            }

            @media print {
                body {
                    background: #ffffff;
                }

                .page-wrapper {
                    min-height: auto;
                    padding: 0;
                    display: block;
                }

                .toolbar {
                    display: none !important;
                }

                .receipt-paper {
                    width: 80mm;
                    max-width: 80mm;
                    box-shadow: none;
                    padding: 10px;
                }
            }
        </style>
    </head>

    <body>
        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

            $storeLogo = $storeSetting?->logo_path
                ? asset('storage/' . $storeSetting->logo_path)
                : null;

            $storeAddress = $storeSetting?->address ?: 'Alamat toko belum diatur';
            $storePhone = $storeSetting?->phone ?: null;
            $storeEmail = $storeSetting?->email ?: null;
            $storeFooter = $storeSetting?->receipt_footer ?: 'Terima kasih sudah berbelanja.';
            $storePolicyText = $storeSetting?->receipt_policy_text;
            $showLogo = (bool) ($storeSetting?->receipt_show_logo ?? true);
            $showSku = (bool) ($storeSetting?->receipt_show_sku ?? true);
            $showPoweredBy = (bool) ($storeSetting?->receipt_show_powered_by ?? true);
        @endphp
        <div class="page-wrapper">
            <div class="toolbar">
                <div class="toolbar-group">
                    <a href="{{ route('pos.index') }}" class="btn">
                        Kembali ke POS
                    </a>

                    <a href="{{ route('sales.report') }}" class="btn">
                        Laporan
                    </a>
                </div>

                <button type="button" class="btn btn-primary" onclick="window.print()">
                    Cetak Struk
                </button>
            </div>

            @if (session('success'))
                <div class="toolbar">
                    <div style="width: 100%; background: #dcfce7; color: #15803d; padding: 10px 12px; border-radius: 8px; font-size: 14px;">
                        {{ session('success') }}
                    </div>
                </div>
            @endif

            <div class="receipt-paper">
                <div class="receipt-center">
                    @if ($showLogo && $storeLogo)
                        <img src="{{ $storeLogo }}" alt="Logo toko" class="receipt-logo">
                    @endif

                    <div class="store-name">{{ $storeName }}</div>

                    <p class="store-info">{{ $storeAddress }}</p>

                    @if ($storePhone)
                        <p class="store-info">WA/HP: {{ $storePhone }}</p>
                    @endif

                    @if ($storeEmail)
                        <p class="store-info">{{ $storeEmail }}</p>
                    @endif
                </div>

                <div class="divider"></div>

                <div class="receipt-row">
                    <span>Invoice</span>
                    <strong>{{ $sale->invoice_no }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Tanggal</span>
                    <strong>{{ $sale->sale_date->format('d/m/Y H:i') }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Pelanggan</span>
                    <strong>{{ $sale->customer_name ?: 'Customer Umum' }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Bayar</span>
                    <strong>{{ $sale->payment_method_label }}</strong>
                </div>

                <div class="divider"></div>

                @foreach ($sale->items as $item)
                    <div class="item">
                        <div class="item-name">{{ $item->product_name }}</div>

                        <div class="item-detail">
                            <span>
                                {{ number_format($item->quantity, 0, ',', '.') }}
                                {{ $item->unit }}
                                x
                                {{ $rupiah($item->unit_price) }}
                            </span>
                            <strong>{{ $rupiah($item->subtotal_amount) }}</strong>
                        </div>

                        @if ($showSku && $item->sku)
                            <div class="small-text">SKU: {{ $item->sku }}</div>
                        @endif
                    </div>
                @endforeach

                <div class="divider"></div>

                <div class="receipt-row">
                    <span>Subtotal</span>
                    <strong>{{ $rupiah($sale->subtotal_amount) }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Diskon</span>
                    <strong>{{ $rupiah($sale->discount_amount) }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Pajak</span>
                    <strong>{{ $rupiah($sale->tax_amount) }}</strong>
                </div>

                <div class="divider"></div>

                <div class="receipt-row total-row">
                    <span>Total</span>
                    <strong>{{ $rupiah($sale->total_amount) }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Dibayar</span>
                    <strong>{{ $rupiah($sale->paid_amount) }}</strong>
                </div>

                <div class="receipt-row">
                    <span>Kembalian</span>
                    <strong>{{ $rupiah($sale->change_amount) }}</strong>
                </div>

                @if ($sale->note)
                    <div class="divider"></div>

                    <div class="small-text">
                        Catatan: {{ $sale->note }}
                    </div>
                @endif

                <div class="divider"></div>

                <div class="receipt-center">
                    @if ($storeFooter)
                        <p class="footer-note">{{ $storeFooter }}</p>
                    @endif

                    @if ($storePolicyText)
                        <p class="footer-note">{{ $storePolicyText }}</p>
                    @endif

                    @if ($showPoweredBy)
                        <p class="small-text" style="margin-top: 8px;">
                            Dicetak dari {{ $storeName }}
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </body>
</html>

