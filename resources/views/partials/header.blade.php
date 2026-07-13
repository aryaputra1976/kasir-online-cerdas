@php
    $storeName = $storeSetting?->store_name ?: 'Kasir Online Cerdas';
    $currentUser = auth()->user();
    $userName = $currentUser?->name ?: ($storeSetting?->owner_name ?: 'Admin Toko');
    $userRoleLabel = $currentUser?->role_label ?: 'Pengguna';
    $isOwner = $currentUser?->hasRole(\App\Models\User::ROLE_OWNER) ?? false;
    $operationalNotificationData = $operationalNotificationData ?? [
        'action_required_count' => 0,
        'unread_count' => 0,
        'badge_text' => '0',
        'notifications' => collect(),
    ];
    $operationalNotifications = collect($operationalNotificationData['notifications'] ?? []);
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

                                @if (($operationalNotificationData['unread_count'] ?? 0) > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-white"
                                        style="font-size: 10px;"
                                    >
                                        {{ $operationalNotificationData['badge_text'] }}
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
                                            ({{ number_format($operationalNotificationData['action_required_count'] ?? 0, 0, ',', '.') }})
                                        </span>
                                    </span>

                                    <form method="post" action="{{ route('operational-notifications.mark-all') }}">
                                        @csrf
                                        <button type="submit" class="border-0 bg-transparent p-0 fs-13 text-primary">
                                            Tandai semua sudah dibaca
                                        </button>
                                    </form>
                                </div>

                                <div class="max-h-217" data-simplebar>
                                    @forelse ($operationalNotifications as $notification)
                                        <div class="notification-menu {{ $notification['unread'] ? 'unseen' : '' }}">
                                            <form method="post" action="{{ route('operational-notifications.open', $notification['key']) }}">
                                                @csrf
                                                <button
                                                    type="submit"
                                                    class="dropdown-item border-0 bg-transparent text-start w-100"
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
                                                </button>
                                            </form>
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
                                                <h3>{{ $userName }}</h3>
                                            </div>
                                            <span class="fs-12 text-body">{{ $userRoleLabel }} • {{ $storeName }}</span>
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
                                        <h3 class="fw-medium">{{ $userName }}</h3>
                                        <span class="fs-12">{{ $userRoleLabel }} • {{ $storeName }}</span>
                                    </div>
                                </div>

                                @if ($isOwner)
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
                                @endif

                                <ul class="admin-link ps-0 mb-0 list-unstyled">
                                    <li>
                                        <form method="post" action="{{ route('logout') }}">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="dropdown-item d-flex align-items-center text-body border-0 bg-transparent w-100"
                                            >
                                                <i class="material-symbols-outlined">logout</i>
                                                <span class="ms-2">Keluar</span>
                                            </button>
                                        </form>
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
