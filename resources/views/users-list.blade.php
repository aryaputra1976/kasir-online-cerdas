<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>User & Role - Kasir Online Cerdas</title>

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

            .koc-user-card {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 18px 20px;
                background-color: #ffffff;
                transition: 0.2s ease;
            }

            .koc-user-card:hover {
                background-color: #fafaff;
            }

            .koc-user-avatar {
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

            .koc-user-meta {
                min-width: 130px;
            }

            @media (max-width: 767.98px) {
                .koc-user-actions {
                    width: 100%;
                }

                .koc-user-actions .btn,
                .koc-user-actions form {
                    width: 100%;
                }

                .koc-user-actions form button {
                    width: 100%;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $summaryCards = [
                [
                    'title' => 'Total User',
                    'value' => number_format($totalUsers, 0, ',', '.'),
                    'note' => 'Semua akun pengguna',
                    'icon' => 'groups',
                    'color' => 'bg-primary bg-opacity-10 text-primary',
                ],
                [
                    'title' => 'User Aktif',
                    'value' => number_format($activeUsers, 0, ',', '.'),
                    'note' => 'Akun yang bisa digunakan',
                    'icon' => 'verified_user',
                    'color' => 'bg-success bg-opacity-10 text-success',
                ],
                [
                    'title' => 'Owner / Admin',
                    'value' => number_format($ownerUsers + $adminUsers, 0, ',', '.'),
                    'note' => 'Pengelola utama aplikasi',
                    'icon' => 'admin_panel_settings',
                    'color' => 'bg-info bg-opacity-10 text-info',
                ],
                [
                    'title' => 'Kasir',
                    'value' => number_format($kasirUsers, 0, ',', '.'),
                    'note' => 'Akun operator kasir',
                    'icon' => 'point_of_sale',
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
                            <h3 class="mb-1">User &amp; Role</h3>
                            <p class="text-body mb-0">
                                Kelola akun pengguna aplikasi, role, dan status aktif user.
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
                                    <span class="fw-medium">Pengaturan</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">User &amp; Role</span>
                                </li>
                            </ol>
                        </nav>
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

                    <div class="row g-4 mb-4">
                        @foreach ($summaryCards as $card)
                            <div class="col-xl-3 col-md-6">
                                <div class="card bg-white border-0 rounded-3 h-100">
                                    <div class="card-body p-4">
                                        <div class="d-flex justify-content-between align-items-center gap-3">
                                            <div>
                                                <span class="d-block text-body mb-2">{{ $card['title'] }}</span>
                                                <h3 class="fs-22 fw-semibold mb-1">
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

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                <div>
                                    <h3 class="mb-1">Daftar User</h3>
                                    <p class="text-body mb-0 fs-13">
                                        Cari user berdasarkan nama, email, nomor HP, role, atau status.
                                    </p>
                                </div>

                                <a href="{{ route('settings.users.create') }}" class="btn btn-primary text-white">
                                    <i class="material-symbols-outlined align-middle fs-18 me-1">person_add</i>
                                    Tambah User
                                </a>
                            </div>

                            <form action="{{ route('settings.users.index') }}" method="get" class="koc-filter-card mb-4">
                                <div class="row g-2 align-items-center">
                                    <div class="col-xl-5 col-lg-5 col-md-12">
                                        <div class="position-relative table-src-form me-0">
                                            <input
                                                type="text"
                                                name="q"
                                                value="{{ $search }}"
                                                class="form-control"
                                                placeholder="Cari nama, email, nomor HP..."
                                            >
                                            <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-lg-3 col-md-6">
                                        <select name="role" class="form-select form-control">
                                            <option value="">Semua Role</option>
                                            @foreach ($roleOptions as $roleValue => $roleLabel)
                                                <option value="{{ $roleValue }}" @selected($role === $roleValue)>
                                                    {{ $roleLabel }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-xl-2 col-lg-2 col-md-6">
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
                                        <a href="{{ route('settings.users.index') }}" class="btn btn-outline-secondary w-100">
                                            Reset
                                        </a>
                                    </div>
                                </div>
                            </form>

                            <div class="d-flex flex-column gap-3">
                                @forelse ($users as $user)
                                    <div class="koc-user-card">
                                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                                            <div class="d-flex align-items-start">
                                                <div class="koc-user-avatar bg-primary bg-opacity-10 text-primary me-3">
                                                    <i class="material-symbols-outlined">person</i>
                                                </div>

                                                <div>
                                                    <div class="d-flex align-items-center flex-wrap gap-2 mb-1">
                                                        <h6 class="fw-semibold fs-16 mb-0">
                                                            {{ $user->name }}
                                                        </h6>

                                                        <span class="badge {{ $user->role_badge_class }} p-2 fs-12 fw-normal">
                                                            {{ $user->role_label }}
                                                        </span>

                                                        <span class="badge {{ $user->status_badge_class }} p-2 fs-12 fw-normal">
                                                            {{ $user->status_label }}
                                                        </span>
                                                    </div>

                                                    <p class="text-body fs-13 mb-2">
                                                        ID: #{{ str_pad((string) $user->id, 4, '0', STR_PAD_LEFT) }}
                                                    </p>

                                                    <div class="d-flex flex-wrap gap-2">
                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            Email: {{ $user->email }}
                                                        </span>

                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            HP: {{ $user->phone ?: '-' }}
                                                        </span>

                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            Dibuat: {{ $user->created_at?->format('d/m/Y H:i') ?: '-' }}
                                                        </span>
                                                    </div>

                                                    <div class="row g-3 mt-2">
                                                        <div class="col-sm-4">
                                                            <div class="koc-user-meta">
                                                                <span class="text-body fs-13 d-block">Role</span>
                                                                <strong>{{ $user->role_label }}</strong>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <div class="koc-user-meta">
                                                                <span class="text-body fs-13 d-block">Status</span>
                                                                <strong>{{ $user->status_label }}</strong>
                                                            </div>
                                                        </div>

                                                        <div class="col-sm-4">
                                                            <div class="koc-user-meta">
                                                                <span class="text-body fs-13 d-block">Update Terakhir</span>
                                                                <strong>{{ $user->updated_at?->format('d/m/Y') ?: '-' }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap gap-2 koc-user-actions">
                                                <a href="{{ route('settings.users.edit', $user) }}" class="btn btn-outline-primary btn-sm">
                                                    Edit
                                                </a>

                                                <form
                                                    action="{{ route('settings.users.destroy', $user) }}"
                                                    method="post"
                                                    onsubmit="return confirm('Hapus user ini?')"
                                                >
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
                                        <h6 class="fw-semibold mb-1">Belum ada user</h6>
                                        <p class="text-body mb-3 fs-13">
                                            Tambahkan user agar akses aplikasi bisa dikelola.
                                        </p>
                                        <a href="{{ route('settings.users.create') }}" class="btn btn-primary text-white">
                                            Tambah User
                                        </a>
                                    </div>
                                @endforelse
                            </div>

                            <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-4">
                                <span class="fs-13 fw-medium">
                                    Menampilkan {{ $users->count() }} dari {{ $users->total() }} user
                                </span>

                                <div>
                                    {{ $users->links('pagination::bootstrap-5') }}
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