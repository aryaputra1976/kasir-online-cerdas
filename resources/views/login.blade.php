<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - Kasir Online Cerdas</title>
    @include('partials.styles')
</head>
<body class="boxed-size bg-white">
    @include('partials.preloader')

    <div class="container">
        <div class="main-content d-flex flex-column p-0">
            <div class="m-auto m-1230">
                <div class="row align-items-center">
                    <div class="col-lg-6 d-none d-lg-block">
                        <img src="{{ asset('assets/images/login.jpg') }}" class="rounded-3" alt="Login aplikasi">
                    </div>

                    <div class="col-lg-6">
                        <div class="mw-480 ms-lg-auto">
                            <a href="{{ route('product.demo') }}" class="d-inline-block mb-4">
                                <img src="{{ asset('assets/images/logo.svg') }}" class="rounded-3 for-light-logo" alt="Kasir Online Cerdas">
                                <img src="{{ asset('assets/images/white-logo.svg') }}" class="rounded-3 for-dark-logo" alt="Kasir Online Cerdas">
                            </a>

                            <h3 class="fs-28 mb-2">Masuk ke Kasir Online Cerdas</h3>
                            <p class="fw-medium fs-16 mb-4">
                                Gunakan akun user yang sudah dibuat owner atau admin untuk mengakses dashboard, POS, dan pengaturan aplikasi.
                            </p>

                            @if (session('success'))
                                <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4" role="alert">
                                    {{ session('success') }}
                                </div>
                            @endif

                            @if (session('info'))
                                <div class="alert alert-info bg-info bg-opacity-10 border-0 text-info mb-4" role="alert">
                                    {{ session('info') }}
                                </div>
                            @endif

                            @if ($errors->any())
                                <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <form method="post" action="{{ route('login') }}">
                                @csrf

                                <div class="form-group mb-4">
                                    <label class="label text-secondary" for="email">Email</label>
                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        class="form-control h-55"
                                        value="{{ old('email') }}"
                                        placeholder="nama@tokoanda.com"
                                        required
                                        autofocus
                                    >
                                </div>

                                <div class="form-group mb-4">
                                    <label class="label text-secondary" for="password">Password</label>
                                    <input
                                        id="password"
                                        name="password"
                                        type="password"
                                        class="form-control h-55"
                                        placeholder="Masukkan password"
                                        required
                                    >
                                </div>

                                <div class="form-group mb-4">
                                    <div class="form-check">
                                        <input
                                            id="remember"
                                            name="remember"
                                            type="checkbox"
                                            class="form-check-input"
                                            value="1"
                                            @checked(old('remember'))
                                        >
                                        <label class="form-check-label text-secondary" for="remember">
                                            Tetap masuk di perangkat ini
                                        </label>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <button type="submit" class="btn btn-primary fw-medium py-2 px-3 w-100">
                                        <div class="d-flex align-items-center justify-content-center py-1">
                                            <i class="material-symbols-outlined text-white fs-20 me-2">login</i>
                                            <span>Masuk</span>
                                        </div>
                                    </button>
                                </div>

                                <div class="form-group">
                                    <p class="mb-0 text-secondary">
                                        Pendaftaran publik tidak tersedia.
                                        <a href="{{ route('register') }}" class="fw-medium text-primary text-decoration-none">Lihat informasi akses</a>
                                    </p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <button class="theme-settings-btn p-0 border-0 bg-transparent position-absolute" style="right: 30px; bottom: 30px;" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasScrolling" aria-controls="offcanvasScrolling">
        <i class="material-symbols-outlined bg-primary wh-35 lh-35 text-white rounded-1" data-bs-toggle="tooltip" data-bs-placement="left" data-bs-title="Pengaturan Tema">settings</i>
    </button>

    @include('partials.theme_settings')
    @include('partials.scripts')
</body>
</html>
