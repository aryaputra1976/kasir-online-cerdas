<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $mode === 'edit' ? 'Edit User' : 'Tambah User' }} - Kasir Online Cerdas</title>

        @include('partials.styles')
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $selectedRole = old('role', $user->role ?: \App\Models\User::ROLE_KASIR);
            $selectedStatus = (string) old('is_active', $user->exists ? ($user->is_active ? '1' : '0') : '1');
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">
                                {{ $mode === 'edit' ? 'Edit User' : 'Tambah User' }}
                            </h3>
                            <p class="text-body mb-0">
                                Kelola akun pengguna, role akses, dan status aktif aplikasi.
                            </p>
                        </div>

                        <a href="{{ route('settings.users.index') }}" class="btn btn-outline-secondary">
                            Kembali
                        </a>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger border-0 rounded-3 mb-4">
                            <strong>Data belum valid.</strong>
                            <ul class="mb-0 mt-2">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="row g-4">
                        <div class="col-xl-8">
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <form
                                        action="{{ $mode === 'edit' ? route('settings.users.update', $user) : route('settings.users.store') }}"
                                        method="post"
                                    >
                                        @csrf

                                        @if ($mode === 'edit')
                                            @method('PUT')
                                        @endif

                                        <div class="row g-3">
                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Nama User <span class="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="text"
                                                    name="name"
                                                    value="{{ old('name', $user->name) }}"
                                                    class="form-control"
                                                    placeholder="Contoh: Admin Toko"
                                                    required
                                                >
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Email <span class="text-danger">*</span>
                                                </label>
                                                <input
                                                    type="email"
                                                    name="email"
                                                    value="{{ old('email', $user->email) }}"
                                                    class="form-control"
                                                    placeholder="admin@email.com"
                                                    required
                                                >
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">Nomor HP</label>
                                                <input
                                                    type="text"
                                                    name="phone"
                                                    value="{{ old('phone', $user->phone) }}"
                                                    class="form-control"
                                                    placeholder="081234567890"
                                                >
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Role <span class="text-danger">*</span>
                                                </label>
                                                <select name="role" class="form-select form-control" required>
                                                    @foreach ($roleOptions as $roleValue => $roleLabel)
                                                        <option value="{{ $roleValue }}" @selected($selectedRole === $roleValue)>
                                                            {{ $roleLabel }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Status <span class="text-danger">*</span>
                                                </label>
                                                <select name="is_active" class="form-select form-control" required>
                                                    <option value="1" @selected($selectedStatus === '1')>
                                                        Aktif
                                                    </option>
                                                    <option value="0" @selected($selectedStatus === '0')>
                                                        Nonaktif
                                                    </option>
                                                </select>
                                            </div>

                                            <div class="col-lg-6"></div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Password
                                                    @if ($mode === 'create')
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <input
                                                    type="password"
                                                    name="password"
                                                    class="form-control"
                                                    placeholder="{{ $mode === 'edit' ? 'Kosongkan jika tidak diubah' : 'Minimal 8 karakter' }}"
                                                    @required($mode === 'create')
                                                >
                                            </div>

                                            <div class="col-lg-6">
                                                <label class="form-label fs-13 fw-medium">
                                                    Konfirmasi Password
                                                    @if ($mode === 'create')
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                <input
                                                    type="password"
                                                    name="password_confirmation"
                                                    class="form-control"
                                                    placeholder="Ulangi password"
                                                    @required($mode === 'create')
                                                >
                                            </div>
                                        </div>

                                        <div class="d-flex justify-content-end flex-wrap gap-2 mt-4">
                                            <a href="{{ route('settings.users.index') }}" class="btn btn-outline-secondary">
                                                Batal
                                            </a>

                                            <button type="submit" class="btn btn-primary text-white">
                                                {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan User' }}
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 mb-4">
                                <div class="card-body p-4">
                                    <h3 class="mb-3">Panduan Role</h3>

                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="badge bg-primary bg-opacity-10 text-primary p-2 fs-12 fw-normal mb-2">
                                            Owner
                                        </span>
                                        <p class="text-body fs-13 mb-0">
                                            Pemilik aplikasi atau toko. Disarankan untuk akses penuh.
                                        </p>
                                    </div>

                                    <div class="border rounded-3 p-3 mb-3">
                                        <span class="badge bg-info bg-opacity-10 text-info p-2 fs-12 fw-normal mb-2">
                                            Admin
                                        </span>
                                        <p class="text-body fs-13 mb-0">
                                            Mengelola master data, laporan, order online, dan pengaturan operasional.
                                        </p>
                                    </div>

                                    <div class="border rounded-3 p-3">
                                        <span class="badge bg-warning bg-opacity-10 text-warning p-2 fs-12 fw-normal mb-2">
                                            Kasir
                                        </span>
                                        <p class="text-body fs-13 mb-0">
                                            Fokus untuk transaksi POS, pembayaran, dan proses order harian.
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="card bg-white border-0 rounded-3">
                                <div class="card-body p-4">
                                    <h3 class="mb-3">Catatan</h3>
                                    <p class="text-body fs-13 mb-0">
                                        Tahap ini baru mengelola data user dan role. Pembatasan akses per role bisa dibuat pada tahap berikutnya agar menu dan aksi mengikuti role user.
                                    </p>
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