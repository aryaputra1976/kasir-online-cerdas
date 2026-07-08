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
                                class="btn btn-secondary border-0 p-0 position-relative badge"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false"
                                aria-label="Notifikasi"
                            >
                                <span class="material-symbols-outlined">notifications</span>
                            </button>

                            <div
                                class="dropdown-menu dropdown-lg p-0 border-0 dropdown-menu-end"
                            >
                                <div
                                    class="d-flex justify-content-between align-items-center title"
                                >
                                    <span class="fw-semibold fs-15 text-secondary">
                                        Notifikasi
                                        <span class="fw-normal text-body fs-14">(03)</span>
                                    </span>

                                    <button
                                        class="p-0 m-0 bg-transparent border-0 fs-14 text-primary"
                                        type="button"
                                    >
                                        Tandai Dibaca
                                    </button>
                                </div>

                                <div class="max-h-217" data-simplebar>
                                    <div class="notification-menu unseen">
                                        <a
                                            href="{{ route('online-orders.index') }}"
                                            class="dropdown-item"
                                        >
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="material-symbols-outlined text-warning">shopping_cart</i>
                                                </div>

                                                <div class="flex-grow-1 ms-3">
                                                    <p>
                                                        Ada
                                                        <span class="fw-semibold">order online baru</span>
                                                        yang menunggu konfirmasi.
                                                    </p>
                                                    <span class="fs-13">5 menit lalu</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="notification-menu">
                                        <a
                                            href="{{ route('stocks.low') }}"
                                            class="dropdown-item"
                                        >
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="material-symbols-outlined text-danger">inventory_2</i>
                                                </div>

                                                <div class="flex-grow-1 ms-3">
                                                    <p>
                                                        Beberapa produk masuk daftar
                                                        <span class="fw-semibold">stok menipis</span>.
                                                    </p>
                                                    <span class="fs-13">20 menit lalu</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>

                                    <div class="notification-menu">
                                        <a
                                            href="{{ route('payments.index') }}"
                                            class="dropdown-item"
                                        >
                                            <div class="d-flex align-items-center">
                                                <div class="flex-shrink-0">
                                                    <i class="material-symbols-outlined text-success">payments</i>
                                                </div>

                                                <div class="flex-grow-1 ms-3">
                                                    <p>
                                                        Pembayaran QRIS berhasil dicatat
                                                        <span class="fw-semibold">Rp 185.000</span>.
                                                    </p>
                                                    <span class="fs-13">1 jam lalu</span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>

                                <a
                                    href="{{ route('online-orders.index') }}"
                                    class="dropdown-item text-center text-primary d-block view-all fw-medium rounded-bottom-3"
                                >
                                    <span>Lihat Semua Order</span>
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
                                                <h3>Admin Toko</h3>
                                            </div>
                                            <span class="fs-12 text-body">Kasir Online Cerdas</span>
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
                                        <h3 class="fw-medium">Admin Toko</h3>
                                        <span class="fs-12">Owner / Kasir</span>
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
                                            href="{{ route('settings.users') }}"
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