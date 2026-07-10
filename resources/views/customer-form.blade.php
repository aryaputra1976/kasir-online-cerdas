<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ $mode === 'edit' ? 'Edit Pelanggan' : 'Tambah Pelanggan' }} - Kasir Online Cerdas</title>

        @include('partials.styles')
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">
                                {{ $mode === 'edit' ? 'Edit Pelanggan' : 'Tambah Pelanggan' }}
                            </h3>
                            <p class="text-body mb-0">
                                Lengkapi data pelanggan untuk kebutuhan transaksi dan pencatatan kontak.
                            </p>
                        </div>

                        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
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

                    <div class="card bg-white border-0 rounded-3 mb-4">
                        <div class="card-body p-4">
                            <form
                                action="{{ $mode === 'edit' ? route('customers.update', $customer) : route('customers.store') }}"
                                method="post"
                            >
                                @csrf

                                @if ($mode === 'edit')
                                    @method('PUT')
                                @endif

                                <div class="row g-3">
                                    <div class="col-lg-6">
                                        <label class="form-label fs-13 fw-medium">Nama Pelanggan <span class="text-danger">*</span></label>
                                        <input
                                            type="text"
                                            name="name"
                                            value="{{ old('name', $customer->name) }}"
                                            class="form-control"
                                            placeholder="Contoh: Rayhan Arvin"
                                            required
                                        >
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fs-13 fw-medium">Nomor HP / WhatsApp</label>
                                        <input
                                            type="text"
                                            name="phone"
                                            value="{{ old('phone', $customer->phone) }}"
                                            class="form-control"
                                            placeholder="Contoh: 081234567890"
                                        >
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fs-13 fw-medium">Email</label>
                                        <input
                                            type="email"
                                            name="email"
                                            value="{{ old('email', $customer->email) }}"
                                            class="form-control"
                                            placeholder="Contoh: pelanggan@email.com"
                                        >
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fs-13 fw-medium">Kota</label>
                                        <input
                                            type="text"
                                            name="city"
                                            value="{{ old('city', $customer->city) }}"
                                            class="form-control"
                                            placeholder="Contoh: Tolitoli"
                                        >
                                    </div>

                                    <div class="col-lg-12">
                                        <label class="form-label fs-13 fw-medium">Alamat</label>
                                        <textarea
                                            name="address"
                                            rows="3"
                                            class="form-control"
                                            placeholder="Alamat lengkap pelanggan"
                                        >{{ old('address', $customer->address) }}</textarea>
                                    </div>

                                    <div class="col-lg-6">
                                        <label class="form-label fs-13 fw-medium">Status</label>
                                        <select name="is_active" class="form-select form-control">
                                            <option value="1" @selected((string) old('is_active', $customer->is_active ? '1' : '0') === '1')>
                                                Aktif
                                            </option>
                                            <option value="0" @selected((string) old('is_active', $customer->is_active ? '1' : '0') === '0')>
                                                Nonaktif
                                            </option>
                                        </select>
                                    </div>

                                    <div class="col-lg-12">
                                        <label class="form-label fs-13 fw-medium">Catatan</label>
                                        <textarea
                                            name="note"
                                            rows="3"
                                            class="form-control"
                                            placeholder="Catatan internal pelanggan"
                                        >{{ old('note', $customer->note) }}</textarea>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end gap-2 mt-4">
                                    <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                                        Batal
                                    </a>

                                    <button type="submit" class="btn btn-primary text-white">
                                        {{ $mode === 'edit' ? 'Simpan Perubahan' : 'Simpan Pelanggan' }}
                                    </button>
                                </div>
                            </form>
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
