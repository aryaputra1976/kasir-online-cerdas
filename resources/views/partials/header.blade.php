@php
    $storeName = $storeSetting?->store_name ?: 'Kasir Online Cerdas';
    $ownerName = $storeSetting?->owner_name ?: 'Admin Toko';

    $newOnlineOrderCount = \App\Models\OnlineOrder::query()
        ->where('status', 'NEW')
        ->count();

    $waitingPaymentConfirmationCount = \App\Models\OnlineOrder::query()
        ->where('payment_status', 'WAITING_CONFIRMATION')
        ->count();

    $completedNotConvertedCount = \App\Models\OnlineOrder::query()
        ->where('status', 'COMPLETED')
        ->where('payment_status', 'PAID')
        ->whereNull('sale_id')
        ->count();

    $lowStockNotificationCount = \App\Models\Product::query()
        ->whereColumn('stock', '<=', 'minimum_stock')
        ->count();

    $emptyStockNotificationCount = \App\Models\Product::query()
        ->where('stock', '<=', 0)
        ->count();

    $operationalNotificationTotal =
        $newOnlineOrderCount
        + $waitingPaymentConfirmationCount
        + $completedNotConvertedCount
        + $lowStockNotificationCount
        + $emptyStockNotificationCount;

    $notificationBadgeText = $operationalNotificationTotal > 99
        ? '99+'
        : (string) $operationalNotificationTotal;

    $operationalNotifications = collect([
        [
            'show' => $newOnlineOrderCount > 0,
            'title' => 'Order online baru',
            'description' => 'Ada ' . number_format($newOnlineOrderCount, 0, ',', '.') . ' order online baru yang perlu diproses.',
            'url' => route('online-orders.index'),
            'icon' => 'shopping_cart',
            'iconClass' => 'text-warning',
            'status' => 'Perlu diproses',
            'unseen' => true,
        ],
        [
            'show' => $waitingPaymentConfirmationCount > 0,
            'title' => 'Pembayaran menunggu konfirmasi',
            'description' => 'Ada ' . number_format($waitingPaymentConfirmationCount, 0, ',', '.') . ' pembayaran online yang perlu divalidasi.',
            'url' => route('payments.index'),
            'icon' => 'fact_check',
            'iconClass' => 'text-primary',
            'status' => 'Perlu validasi',
            'unseen' => true,
        ],
        [
            'show' => $completedNotConvertedCount > 0,
            'title' => 'Order belum masuk penjualan',
            'description' => 'Ada ' . number_format($completedNotConvertedCount, 0, ',', '.') . ' order selesai yang belum masuk transaksi penjualan.',
            'url' => route('reports.online-orders.index'),
            'icon' => 'sync_problem',
            'iconClass' => 'text-warning',
            'status' => 'Perlu dicek',
            'unseen' => true,
        ],
        [
            'show' => $lowStockNotificationCount > 0,
            'title' => 'Stok menipis',
            'description' => 'Ada ' . number_format($lowStockNotificationCount, 0, ',', '.') . ' produk dengan stok kurang atau sama dengan minimum.',
            'url' => route('stocks.low'),
            'icon' => 'inventory_2',
            'iconClass' => 'text-danger',
            'status' => 'Perlu restock',
            'unseen' => false,
        ],
        [
            'show' => $emptyStockNotificationCount > 0,
            'title' => 'Stok kosong',
            'description' => 'Ada ' . number_format($emptyStockNotificationCount, 0, ',', '.') . ' produk dengan stok kosong.',
            'url' => route('stocks.low'),
            'icon' => 'production_quantity_limits',
            'iconClass' => 'text-danger',
            'status' => 'Stok kosong',
            'unseen' => false,
        ],
    ])->filter(fn ($item) => $item['show'])->values();
@endphp

<header
    class="header-area bg-white mb-4 rounded-bottom-15"
    id="header-area"
>
    <div class="row align-items-center">
        <div class="col-lg-5 col-sm-6">
            <div class="left-header-content">
                <ul
                    class="d-flex align-items-center ps-0 mb-0 list-unstyled justify-content-center justify-content-sm-start"
                >
                    <li>
                        <button
                            class="header-burger-menu bg-transparent p-0 border-0"
                            id="header-burger-menu"
                            type="button"
                        >
                            <span class="material-symbols-outlined">menu</span>
                        </button>
                    </li>

                    <li>
                        <form
                            class="src-form position-relative"
                            action="{{ route('products.index') }}"
                            method="get"
                        >
                            <input
                                type="text"
                                name="q"
                                class="form-control"
                                placeholder="Cari produk, transaksi, pelanggan..."
                            />

                            <button
                                type="submit"
                                class="src-btn position-absolute top-50 end-0 translate-middle-y bg-transparent p-0 border-0"
                            >
                                <span class="material-symbols-outlined">search</span>
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>

        <div class="col-lg-7 col-sm-6">
            <div class="right-header-content mt-2 mt-sm-0">
                <ul
                    class="d-flex align-items-center justify-content-center justify-content-sm-end ps-0 mb-0 list-unstyled"
                >
                    <li class="header-right-item d-none d-xl-block">
                        <a
                            href="{{ route('pos.index') }}"
                            class="btn btn-primary text-white btn-sm"
                        >
                            <i class="material-symbols-outlined align-middle fs-18 me-1">point_of_sale</i>
                            Kasir POS
                        </a>
                    </li>

                    <li class="header-right-item d-none d-xl-block">
                        <a
                            href="{{ route('online-orders.index') }}"
                            class="btn btn-outline-primary btn-sm"
                        >
                            <i class="material-symbols-outlined align-middle fs-18 me-1">shopping_cart</i>
                            Order Online
                        </a>
                    </li>

                    <li class="header-right-item">
                        <div class="light-dark">
                            <button
                                class="switch-toggle settings-btn dark-btn p-0 bg-transparent"
                                id="switch-toggle"
                                type="button"
                                aria-label="Ganti mode terang atau gelap"
                            >
                                <span class="dark">
                                    <i class="material-symbols-outlined">light_mode</i>
                                </span>
                                <span class="light">
                                    <i class="material-symbols-outlined">dark_mode</i>
                                </span>
                            </button>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <button
                            class="fullscreen-btn bg-transparent p-0 border-0"
                            id="fullscreen-button"
                            type="button"
                            aria-label="Layar penuh"
                        >
                            <i class="material-symbols-outlined text-body">fullscreen</i>
                        </button>
                    </li>

                    <li class="header-right-item">
                        <div class="dropdown notifications noti">
                            <button
                                class="btn btn-secondary border-0 p-0 position-relative"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Notifikasi"
                            >
                                <span class="material-symbols-outlined">notifications</span>

                                @if ($operationalNotificationTotal > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"
                                        style="font-size: 10px;"
                                    >
                                        {{ $notificationBadgeText }}
                                    </span>
                                @endif
                            </button>

                            <div
                                class="dropdown-menu dropdown-lg p-0 border-0 dropdown-menu-end"
                            >
                                <div
                                    class="d-flex justify-content-between align-items-center title"
                                >
                                    <span class="fw-semibold fs-15 text-secondary">
                                        Notifikasi Operasional
                                        <span class="fw-normal text-body fs-14">
                                            ({{ number_format($operationalNotificationTotal, 0, ',', '.') }})
                                        </span>
                                    </span>

                                    <span class="fs-13 text-body">
                                        Real-time
                                    </span>
                                </div>

                                <div class="max-h-217" data-simplebar>
                                    @forelse ($operationalNotifications as $notification)
                                        <div class="notification-menu {{ $notification['unseen'] ? 'unseen' : '' }}">
                                            <a
                                                href="{{ $notification['url'] }}"
                                                class="dropdown-item"
                                            >
                                                <div class="d-flex align-items-center">
                                                    <div class="flex-shrink-0">
                                                        <i class="material-symbols-outlined {{ $notification['iconClass'] }}">
                                                            {{ $notification['icon'] }}
                                                        </i>
                                                    </div>

                                                    <div class="flex-grow-1 ms-3">
                                                        <p>
                                                            <span class="fw-semibold">
                                                                {{ $notification['title'] }}
                                                            </span>
                                                            <br>
                                                            {{ $notification['description'] }}
                                                        </p>

                                                        <span class="fs-13">
                                                            {{ $notification['status'] }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </a>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 px-3">
                                            <i class="material-symbols-outlined text-success fs-40 mb-2">
                                                check_circle
                                            </i>

                                            <h6 class="fw-semibold mb-1">
                                                Semua aman
                                            </h6>

                                            <p class="text-body mb-0 fs-13">
                                                Tidak ada order, pembayaran, atau stok yang perlu tindakan saat ini.
                                            </p>
                                        </div>
                                    @endforelse
                                </div>

                                <a
                                    href="{{ route('reports.online-orders.index') }}"
                                    class="dropdown-item text-center text-primary d-block view-all fw-medium rounded-bottom-3"
                                >
                                    <span>Lihat Laporan Order Online</span>
                                </a>
                            </div>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <div class="dropdown admin-profile">
                            <div
                                class="d-xxl-flex align-items-center bg-transparent border-0 text-start p-0 cursor dropdown-toggle"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                            >
                                <div class="flex-shrink-0">
                                    <div
                                        class="rounded-circle wh-40 administrator bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                    >
                                        <i class="material-symbols-outlined text-primary">person</i>
                                    </div>
                                </div>

                                <div class="flex-grow-1 ms-2">
                                    <div
                                        class="d-flex align-items-center justify-content-between"
                                    >
                                        <div class="d-none d-xxl-block">
                                            <div class="d-flex align-content-center">
                                                <h3>{{ $ownerName }}</h3>
                                            </div>
                                            <span class="fs-12 text-body">{{ $storeName }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div
                                class="dropdown-menu border-0 bg-white dropdown-menu-end"
                            >
                                <div class="d-flex align-items-center info">
                                    <div class="flex-shrink-0">
                                        <div
                                            class="rounded-circle wh-30 administrator bg-primary bg-opacity-10 d-flex align-items-center justify-content-center"
                                        >
                                            <i class="material-symbols-outlined text-primary fs-18">person</i>
                                        </div>
                                    </div>

                                    <div class="flex-grow-1 ms-2">
                                        <h3 class="fw-medium">{{ $ownerName }}</h3>
                                        <span class="fs-12">{{ $storeName }}</span>
                                    </div>
                                </div>

                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <a
                                            class="dropdown-item d-flex align-items-center text-body"
                                            href="{{ route('settings.store') }}"
                                        >
                                            <i class="material-symbols-outlined">store</i>
                                            <span class="ms-2">Profil Toko</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item d-flex align-items-center text-body"
                                            href="{{ route('settings.users.index') }}"
                                        >
                                            <i class="material-symbols-outlined">group</i>
                                            <span class="ms-2">User & Role</span>
                                        </a>
                                    </li>

                                    <li>
                                        <a
                                            class="dropdown-item d-flex align-items-center text-body"
                                            href="{{ route('settings.receipt-template') }}"
                                        >
                                            <i class="material-symbols-outlined">receipt_long</i>
                                            <span class="ms-2">Template Struk</span>
                                        </a>
                                    </li>
                                </ul>

                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <a
                                            class="dropdown-item d-flex align-items-center text-body"
                                            href="{{ route('login') }}"
                                        >
                                            <i class="material-symbols-outlined">logout</i>
                                            <span class="ms-2">Keluar</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>

                    <li class="header-right-item">
                        <button
                            class="theme-settings-btn p-0 border-0 bg-transparent"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#offcanvasScrolling"
                            aria-controls="offcanvasScrolling"
                        >
                            <i
                                class="material-symbols-outlined"
                                data-bs-toggle="tooltip"
                                data-bs-placement="left"
                                data-bs-title="Pengaturan Tema"
                            >settings</i>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</header>