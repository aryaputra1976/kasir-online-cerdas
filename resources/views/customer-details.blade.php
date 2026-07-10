<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Detail Pelanggan - Kasir Online Cerdas</title>

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

            .koc-sale-row {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px 18px;
                background-color: #ffffff;
            }

            .koc-sale-row:hover {
                background-color: #fafaff;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');

            $summaryCards = [
                [
                    'title' => 'Transaksi POS',
                    'value' => number_format($salesCount, 0, ',', '.'),
                    'note' => 'Riwayat transaksi kasir',
                    'icon' => 'point_of_sale',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'Order Online',
                    'value' => number_format($onlineOrderCount, 0, ',', '.'),
                    'note' => 'Riwayat order publik',
                    'icon' => 'shopping_cart',
                    'color' => 'bg-warning bg-opacity-10 text-warning',
                ],
                [
                    'title' => 'Total Omzet',
                    'value' => $rupiah($totalOmzet),
                    'note' => 'POS + order online',
                    'icon' => 'payments',
                    'color' => 'bg-success bg-opacity-10 text-success',
                ],
                [
                    'title' => 'Aktivitas Terakhir',
                    'value' => $lastActivityAt ? $lastActivityAt->format('d/m/Y') : '-',
                    'note' => $lastActivityAt ? $lastActivityAt->format('H:i') : 'Belum ada aktivitas',
                    'icon' => 'history',
                    'color' => 'bg-info bg-opacity-10 text-info',
                ],
            ];
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Detail Pelanggan</h3>
                            <p class="text-body mb-0">
                                Profil pelanggan, transaksi POS, dan order online yang terhubung.
                            </p>
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-primary text-white">
                                Edit Pelanggan
                            </a>

                            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                Kembali
                            </a>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex align-items-start mb-4">
                                        <div class="koc-summary-icon bg-primary bg-opacity-10 text-primary me-3">
                                            <i class="material-symbols-outlined">person</i>
                                        </div>

                                        <div>
                                            <h4 class="fs-18 fw-semibold mb-1">{{ $customer->name }}</h4>
                                            <span class="badge {{ $customer->status_badge_class }} p-2 fs-12 fw-normal">
                                                {{ $customer->status_label }}
                                            </span>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Kode Pelanggan</span>
                                        <strong>{{ $customer->customer_code }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Nomor HP</span>
                                        <strong>{{ $customer->phone ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Email</span>
                                        <strong>{{ $customer->email ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Kota</span>
                                        <strong>{{ $customer->city ?: '-' }}</strong>
                                    </div>

                                    <div class="mb-3">
                                        <span class="text-body d-block fs-13">Alamat</span>
                                        <strong>{{ $customer->address ?: '-' }}</strong>
                                    </div>

                                    <div>
                                        <span class="text-body d-block fs-13">Catatan</span>
                                        <strong>{{ $customer->note ?: '-' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-8">
                            <div class="row g-4">
                                @foreach ($summaryCards as $card)
                                    <div class="col-md-6">
                                        <div class="card bg-white border-0 rounded-3 h-100">
                                            <div class="card-body p-4">
                                                <div class="d-flex justify-content-between align-items-center gap-3">
                                                    <div>
                                                        <span class="text-body d-block mb-2">{{ $card['title'] }}</span>
                                                        <h3 class="fs-22 fw-semibold mb-1 koc-price">
                                                            {{ $card['value'] }}
                                                        </h3>
                                                        <p class="fs-13 text-body mb-0">{{ $card['note'] }}</p>
                                                    </div>

                                                    <div class="koc-summary-icon {{ $card['color'] }}">
                                                        <i class="material-symbols-outlined">{{ $card['icon'] }}</i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="card bg-white border-0 rounded-3 mt-4">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Ringkasan Omzet</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Pemisahan omzet berdasarkan transaksi POS dan order online.
                                            </p>
                                        </div>
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3">
                                                <span class="text-body fs-13 d-block mb-1">Omzet POS</span>
                                                <h4 class="mb-0 koc-price">{{ $rupiah($totalSalesOmzet) }}</h4>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="border rounded-3 p-3">
                                                <span class="text-body fs-13 d-block mb-1">Omzet Order Online</span>
                                                <h4 class="mb-0 koc-price">{{ $rupiah($totalOnlineOrderOmzet) }}</h4>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4 mb-4">
                        <div class="col-xl-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Riwayat Transaksi POS</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Transaksi kasir yang terhubung dengan pelanggan ini.
                                            </p>
                                        </div>

                                        <a href="{{ route('sales.report', ['q' => $customer->name]) }}" class="btn btn-outline-primary btn-sm">
                                            Laporan Penjualan
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($sales as $sale)
                                            <div class="koc-sale-row">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $sale->invoice_no }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-2">
                                                            {{ $sale->sale_date?->format('d/m/Y H:i') }}
                                                        </p>

                                                        <div class="d-flex flex-wrap gap-2">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $sale->payment_method_label }}
                                                            </span>

                                                            <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                                {{ $sale->status_label }}
                                                            </span>

                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ number_format($sale->items->sum('quantity'), 0, ',', '.') }} item
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="text-lg-end">
                                                        <span class="text-body d-block fs-13 mb-1">Total</span>
                                                        <h5 class="fw-semibold mb-2 koc-price">
                                                            {{ $rupiah($sale->total_amount) }}
                                                        </h5>

                                                        <a href="{{ route('pos.receipt', $sale) }}" class="btn btn-outline-primary btn-sm" target="_blank">
                                                            Lihat Struk
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">receipt_long</i>
                                                <h6 class="fw-semibold mb-1">Belum ada transaksi POS</h6>
                                                <p class="text-body mb-0 fs-13">
                                                    Riwayat POS akan muncul setelah pelanggan digunakan pada transaksi kasir.
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-4">
                                        {{ $sales->links('pagination::bootstrap-5') }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-6">
                            <div class="card bg-white border-0 rounded-3 h-100">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Riwayat Order Online</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Order dari halaman menu publik yang terhubung dengan pelanggan ini.
                                            </p>
                                        </div>

                                        <a href="{{ route('reports.online-orders.index', ['search' => $customer->phone ?: $customer->name]) }}" class="btn btn-outline-primary btn-sm">
                                            Laporan Order
                                        </a>
                                    </div>

                                    <div class="d-flex flex-column gap-3">
                                        @forelse ($onlineOrders as $order)
                                            <div class="koc-sale-row">
                                                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                                    <div>
                                                        <h6 class="fw-semibold fs-15 mb-1">
                                                            {{ $order->order_no }}
                                                        </h6>

                                                        <p class="text-body fs-13 mb-2">
                                                            {{ $order->created_at?->format('d/m/Y H:i') }}
                                                        </p>

                                                        <div class="d-flex flex-wrap gap-2">
                                                            <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                                {{ $order->payment_method_label }}
                                                            </span>

                                                            <span class="badge {{ $order->payment_status_class }} p-2 fs-12 fw-normal">
                                                                {{ $order->payment_status_label }}
                                                            </span>

                                                            <span class="badge {{ $order->status_class }} p-2 fs-12 fw-normal">
                                                                {{ $order->status_label }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="text-lg-end">
                                                        <span class="text-body d-block fs-13 mb-1">Total</span>
                                                        <h5 class="fw-semibold mb-2 koc-price">
                                                            {{ $rupiah($order->total_amount) }}
                                                        </h5>

                                                        <a href="{{ route('online-orders.show', $order) }}" class="btn btn-outline-primary btn-sm">
                                                            Detail
                                                        </a>

                                                        @if ($order->sale)
                                                            <a href="{{ route('pos.receipt', $order->sale) }}" class="btn btn-outline-success btn-sm" target="_blank">
                                                                Struk
                                                            </a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="text-center py-5 border rounded-3">
                                                <i class="material-symbols-outlined text-body fs-40 mb-2">shopping_cart</i>
                                                <h6 class="fw-semibold mb-1">Belum ada order online</h6>
                                                <p class="text-body mb-0 fs-13">
                                                    Order online akan muncul setelah pelanggan checkout dari menu publik.
                                                </p>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="mt-4">
                                        {{ $onlineOrders->links('pagination::bootstrap-5') }}
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