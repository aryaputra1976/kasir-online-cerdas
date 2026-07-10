<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Pelanggan - Kasir Online Cerdas</title>

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

            .koc-customer-card {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
            }

            .koc-customer-card:hover {
                background-color: #fafaff;
            }

            .koc-avatar {
                width: 46px;
                height: 46px;
                min-width: 46px;
                border-radius: 14px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
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
                    'title' => 'Total Pelanggan',
                    'value' => number_format($totalCustomers, 0, ',', '.'),
                    'note' => 'Semua data pelanggan',
                    'icon' => 'groups',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'Pelanggan Aktif',
                    'value' => number_format($activeCustomers, 0, ',', '.'),
                    'note' => 'Pelanggan yang masih aktif',
                    'icon' => 'verified_user',
                    'color' => 'bg-success bg-opacity-10 text-success',
                ],
                [
                    'title' => 'Pelanggan Bertransaksi',
                    'value' => number_format($customersWithTransactions, 0, ',', '.'),
                    'note' => 'Pelanggan dengan POS / order online',
                    'icon' => 'receipt_long',
                    'color' => 'bg-info bg-opacity-10 text-info',
                ],
                [
                    'title' => 'Omzet Pelanggan',
                    'value' => $rupiah($totalCustomerOmzet),
                    'note' => 'POS + order online terhubung',
                    'icon' => 'payments',
                    'color' => 'bg-warning bg-opacity-10 text-warning',
                ],
            ];
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Pelanggan</h3>
                            <p class="text-body mb-0">
                                Kelola data pelanggan, nomor HP, alamat, dan status pelanggan toko.
                            </p>
                        </div>

                        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
                            <ol class="breadcrumb align-items-center mb-0 lh-1">
                                <li class="breadcrumb-item">
                                    <a href="{{ route('dashboard') }}" class="d-flex align-items-center text-decoration-none">
                                        <i class="ri-home-4-line fs-18 text-primary me-1"></i>
                                        <span class="text-secondary fw-medium hover">Dashboard</span>
                                    </a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Master Data</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Pelanggan</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success border-0 rounded-3 mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row g-4 mb-4">
                        @foreach ($summaryCards as $card)
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white border-0 rounded-3 h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <span class="d-block text-body mb-2">{{ $card['title'] }}</span>
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

<div class="row g-4 mb-4">
    <div class="col-xl-7">
        <div class="card bg-white border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Top Pelanggan</h3>
                        <p class="text-body mb-0 fs-13">
                            Pelanggan dengan omzet tertinggi dari POS dan order online.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse ($topCustomers as $topCustomer)
                        <div class="koc-customer-card">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                <div>
                                    <h6 class="fw-semibold fs-15 mb-1">
                                        {{ $topCustomer->name }}
                                    </h6>

                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                            {{ $topCustomer->customer_code }}
                                        </span>

                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-12 fw-normal">
                                            POS {{ number_format((int) $topCustomer->sales_count, 0, ',', '.') }}
                                        </span>

                                        <span class="badge bg-warning bg-opacity-10 text-warning p-2 fs-12 fw-normal">
                                            Online {{ number_format((int) $topCustomer->online_orders_count, 0, ',', '.') }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-lg-end">
                                    <span class="text-body fs-13 d-block mb-1">Total Omzet</span>
                                    <h5 class="mb-2 koc-price">
                                        {{ $rupiah($topCustomer->total_omzet) }}
                                    </h5>

                                    <a href="{{ route('customers.show', $topCustomer) }}" class="btn btn-outline-primary btn-sm">
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border rounded-3">
                            <i class="material-symbols-outlined text-body fs-40 mb-2">leaderboard</i>
                            <h6 class="fw-semibold mb-1">Belum ada top pelanggan</h6>
                            <p class="text-body mb-0 fs-13">
                                Data akan muncul setelah pelanggan memiliki transaksi atau order online.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-5">
        <div class="card bg-white border-0 rounded-3 h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                    <div>
                        <h3 class="mb-1">Pelanggan Terbaru</h3>
                        <p class="text-body mb-0 fs-13">
                            Data pelanggan yang terakhir ditambahkan.
                        </p>
                    </div>
                </div>

                <div class="d-flex flex-column gap-3">
                    @forelse ($newestCustomers as $newCustomer)
                        <div class="koc-customer-card">
                            <div class="d-flex justify-content-between align-items-start gap-3">
                                <div>
                                    <h6 class="fw-semibold fs-15 mb-1">
                                        {{ $newCustomer->name }}
                                    </h6>

                                    <span class="text-body fs-13 d-block">
                                        {{ $newCustomer->phone ?: 'Tanpa nomor HP' }}
                                    </span>

                                    <span class="text-body fs-13 d-block">
                                        {{ $newCustomer->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                </div>

                                <a href="{{ route('customers.show', $newCustomer) }}" class="btn btn-outline-primary btn-sm">
                                    Detail
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 border rounded-3">
                            <i class="material-symbols-outlined text-body fs-40 mb-2">person_add</i>
                            <h6 class="fw-semibold mb-1">Belum ada pelanggan</h6>
                            <p class="text-body mb-0 fs-13">
                                Pelanggan baru akan tampil di sini.
                            </p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
                    
                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Daftar Pelanggan</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Cari pelanggan berdasarkan nama, nomor HP, email, atau kota.
                                    </p>
                                </div>

                                <a href="{{ route('customers.create') }}" class="btn btn-primary text-white">
                                    <i class="material-symbols-outlined align-middle fs-18 me-1">person_add</i>
                                    Tambah Pelanggan
                                </a>
                            </div>

                            <form action="{{ route('customers.index') }}" method="get" class="koc-filter-card mb-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-xl-6 col-lg-6 col-md-12">
                                        <div class="position-relative table-src-form me-0">
                                            <input
                                                type="text"
                                                name="q"
                                                value="{{ $search }}"
                                                class="form-control"
                                                placeholder="Cari nama, HP, email, kota..."
                                            >
                                            <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6">
                                        <select name="status" class="form-select form-control">
                                            <option value="">Semua Status</option>
                                            <option value="active" @selected($status === 'active')>Aktif</option>
                                            <option value="inactive" @selected($status === 'inactive')>Nonaktif</option>
                                        </select>
                                    </div>

                                    <div class="col-xl-auto col-lg-auto col-md-auto">
                                        <button type="submit" class="btn btn-outline-primary w-100">
                                            Filter
                                        </button>
                                    </div>

                                    <div class="col-xl-auto col-lg-auto col-md-auto">
                                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary w-100">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="d-flex flex-column gap-3">
                                @forelse ($customers as $customer)
                                    <div class="koc-customer-card">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                            <div class="d-flex align-items-start">
                                                <div class="koc-avatar bg-primary bg-opacity-10 text-primary me-3">
                                                    <i class="material-symbols-outlined">person</i>
                                                </div>

                                                <div>
                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                        <h6 class="fw-semibold fs-16 mb-0">{{ $customer->name }}</h6>

                                                        <span class="badge {{ $customer->status_badge_class }} p-2 fs-12 fw-normal">
                                                            {{ $customer->status_label }}
                                                        </span>
                                                    </div>

                                                    <p class="text-body fs-13 mb-2">
                                                        {{ $customer->customer_code }}
                                                    </p>

                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            HP: {{ $customer->phone ?: '-' }}
                                                        </span>

                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            Email: {{ $customer->email ?: '-' }}
                                                        </span>

                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            Kota: {{ $customer->city ?: '-' }}
                                                        </span>
                                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-12 fw-normal">
                                                            POS: {{ number_format((int) $customer->sales_count, 0, ',', '.') }}
                                                        </span>

                                                        <span class="badge bg-warning bg-opacity-10 text-warning p-2 fs-12 fw-normal">
                                                            Online: {{ number_format((int) $customer->online_orders_count, 0, ',', '.') }}
                                                        </span>

                                                        <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                            Omzet: {{ $rupiah($customer->total_omzet) }}
                                                        </span>                                                        
                                                    </div>

                                                    @if ($customer->address)
                                                        <p class="text-body fs-13 mb-0 mt-2">
                                                            {{ $customer->address }}
                                                        </p>
                                                    @endif
                                                    <p class="text-body fs-13 mb-0 mt-2">
                                                        Aktivitas terakhir:
                                                        <strong>
                                                            {{ $customer->last_activity_at ? $customer->last_activity_at->format('d/m/Y H:i') : '-' }}
                                                        </strong>
                                                    </p>                                                    
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2">
                                                <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-primary btn-sm">
                                                    Detail
                                                </a>

                                                <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-secondary btn-sm">
                                                    Edit
                                                </a>

                                                <form action="{{ route('customers.destroy', $customer) }}" method="post" onsubmit="return confirm('Hapus pelanggan ini?')">
                                                    @csrf
                                                    @method('DELETE')

                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        Hapus
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5 border rounded-3">
                                        <i class="material-symbols-outlined text-body fs-40 mb-2">groups</i>
                                        <h6 class="fw-semibold mb-1">Belum ada pelanggan</h6>
                                        <p class="text-body mb-3 fs-13">
                                            Tambahkan pelanggan agar riwayat transaksi dan kontak lebih mudah dikelola.
                                        </p>
                                        <a href="{{ route('customers.create') }}" class="btn btn-primary text-white">
                                            Tambah Pelanggan
                                        </a>
                                    </div>
                                @endforelse
                            </div>

                            <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-4">
                                <span class="fs-13 fw-medium">
                                    Menampilkan {{ $customers->count() }} dari {{ $customers->total() }} pelanggan
                                </span>

                                <div>
                                    {{ $customers->links('pagination::bootstrap-5') }}
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