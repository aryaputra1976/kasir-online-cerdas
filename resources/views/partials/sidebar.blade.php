@php
    $storeName = $storeSetting?->store_name ?: 'Kasir Online Cerdas';
    $storeLogo = $storeSetting?->logo_path
        ? asset('storage/' . $storeSetting->logo_path)
        : '/assets/images/logo-icon.png';

    $isDashboard = Request::is('dashboard');

    $isMasterData = Request::is('kategori-produk')
        || Request::is('produk')
        || Request::is('produk/*')
        || Request::is('pelanggan')
        || Request::is('pelanggan/*');

    $isTransaksi = Request::is('pos')
        || Request::is('order-online')
        || Request::is('order-online/*')
        || Request::is('pembayaran')
        || Request::is('pembayaran/*');

    $isStok = Request::is('stok-barang')
        || Request::is('mutasi-stok')
        || Request::is('stok-menipis');

    $isLaporan = Request::is('laporan')
        || Request::is('laporan/*');

    $isPengaturan = Request::is('pengaturan/*');
@endphp

<div class="sidebar-area" id="sidebar-area">
    <div class="logo position-relative">
        <a href="{{ route('dashboard') }}" class="d-block text-decoration-none position-relative">
            <img src="{{ $storeLogo }}" alt="logo-icon">
            <span class="logo-text fw-bold text-dark">{{ $storeName }}</span>
        </a>

        <button
            class="sidebar-burger-menu bg-transparent p-0 border-0 opacity-0 z-n1 position-absolute top-50 end-0 translate-middle-y"
            id="sidebar-burger-menu"
        >
            <i data-feather="x"></i>
        </button>
    </div>

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" data-simplebar>
        <ul class="menu-inner">

            {{-- Dashboard --}}
            <li class="menu-item {{ $isDashboard ? 'active' : '' }}">
                <a href="{{ route('dashboard') }}" class="menu-link {{ $isDashboard ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">dashboard</span>
                    <span class="title">Dashboard</span>
                </a>
            </li>

            {{-- Master Data --}}
            <li class="menu-item {{ $isMasterData ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ $isMasterData ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">inventory_2</span>
                    <span class="title">Master Data</span>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('categories.index') }}" class="menu-link {{ Request::is('kategori-produk') ? 'active' : '' }}">
                            Kategori Produk
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('products.index') }}" class="menu-link {{ Request::is('produk') || Request::is('produk/*') ? 'active' : '' }}">
                            Produk
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('customers.index') }}" class="menu-link {{ Request::is('pelanggan') || Request::is('pelanggan/*') ? 'active' : '' }}">
                            Pelanggan
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Transaksi --}}
            <li class="menu-item {{ $isTransaksi ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ $isTransaksi ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">point_of_sale</span>
                    <span class="title">Transaksi</span>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('pos.index') }}" class="menu-link {{ Request::is('pos') ? 'active' : '' }}">
                            Kasir POS
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('online-orders.index') }}" class="menu-link {{ Request::is('order-online') || Request::is('order-online/*') ? 'active' : '' }}">
                            Order Online
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('payments.index') }}" class="menu-link {{ Request::is('pembayaran') || Request::is('pembayaran/*') ? 'active' : '' }}">
                            Pembayaran
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Stok --}}
            <li class="menu-item {{ $isStok ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ $isStok ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">warehouse</span>
                    <span class="title">Stok</span>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('stocks.index') }}" class="menu-link {{ Request::is('stok-barang') ? 'active' : '' }}">
                            Stok Barang
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('stocks.movements') }}" class="menu-link {{ Request::is('mutasi-stok') ? 'active' : '' }}">
                            Mutasi Stok
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('stocks.low') }}" class="menu-link {{ Request::is('stok-menipis') ? 'active' : '' }}">
                            Stok Menipis
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Laporan --}}
            <li class="menu-item {{ $isLaporan ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ $isLaporan ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">assessment</span>
                    <span class="title">Laporan</span>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ url('/laporan/penjualan') }}"
                           class="menu-link {{ Request::is('laporan/penjualan*') ? 'active' : '' }}">
                            Penjualan
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ url('/laporan/produk-terlaris') }}"
                           class="menu-link {{ Request::is('laporan/produk-terlaris*') ? 'active' : '' }}">
                            Produk Terlaris
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ url('/laporan/laba-rugi') }}"
                           class="menu-link {{ Request::is('laporan/laba-rugi*') ? 'active' : '' }}">
                            Laba Rugi
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ url('/laporan/stok') }}"
                           class="menu-link {{ Request::is('laporan/stok*') ? 'active' : '' }}">
                            Stok
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ url('/laporan/order-online') }}"
                           class="menu-link {{ Request::is('laporan/order-online*') ? 'active' : '' }}">
                            Order Online
                        </a>
                    </li>
                </ul>
            </li>

            {{-- Pengaturan --}}
            <li class="menu-item {{ $isPengaturan ? 'open active' : '' }}">
                <a href="javascript:void(0);" class="menu-link menu-toggle {{ $isPengaturan ? 'active' : '' }}">
                    <span class="material-symbols-outlined menu-icon">settings</span>
                    <span class="title">Pengaturan</span>
                </a>

                <ul class="menu-sub">
                    <li class="menu-item">
                        <a href="{{ route('settings.store') }}" class="menu-link {{ Request::is('pengaturan/profil-toko') ? 'active' : '' }}">
                            Profil Toko
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('settings.payment-methods') }}" class="menu-link {{ Request::is('pengaturan/metode-pembayaran') ? 'active' : '' }}">
                            Metode Pembayaran
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('settings.users.index') }}" class="menu-link {{ Request::is('pengaturan/user-role') ? 'active' : '' }}">
                            User &amp; Role
                        </a>
                    </li>

                    <li class="menu-item">
                        <a href="{{ route('settings.receipt-template') }}" class="menu-link {{ Request::is('pengaturan/template-struk') ? 'active' : '' }}">
                            Template Struk
                        </a>
                    </li>
                </ul>
            </li>
        </ul>
    </aside>
</div>