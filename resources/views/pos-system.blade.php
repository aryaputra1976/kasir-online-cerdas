<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Kasir POS - Kasir Online Cerdas</title>

        @include('partials.styles')

        <style>
            .koc-product-card {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 16px;
                height: 100%;
                background-color: #ffffff;
            }

            .koc-product-card:hover {
                background-color: #fafaff;
            }

            .koc-product-icon {
                width: 44px;
                height: 44px;
                min-width: 44px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                border-radius: 14px;
            }

            .koc-cart-item {
                border: 1px solid #eef0f7;
                border-radius: 14px;
                padding: 14px;
                background-color: #ffffff;
            }

            .koc-filter-card .form-control,
            .koc-filter-card .form-select {
                min-height: 48px;
            }

            .koc-price {
                letter-spacing: -0.2px;
            }

            .koc-cart-panel {
                position: sticky;
                top: 95px;
            }

            @media (max-width: 1199.98px) {
                .koc-cart-panel {
                    position: static;
                }
            }
        </style>
    </head>

    <body class="boxed-size">
        @include('partials.preloader')
        @include('partials.sidebar')

        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        @endphp

        <div class="container-fluid">
            <div class="main-content d-flex flex-column">
                @include('partials.header')

                <div class="main-content-container overflow-hidden">
                    <div class="d-flex justify-content-between align-items-start align-items-lg-center flex-wrap gap-3 mb-4">
                        <div>
                            <h3 class="mb-1">Kasir POS</h3>
                            <p class="text-body mb-0">
                                Transaksi kasir langsung, stok otomatis berkurang, dan mutasi stok tercatat.
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
                                    <span class="fw-medium">Transaksi</span>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">
                                    <span class="fw-medium">Kasir POS</span>
                                </li>
                            </ol>
                        </nav>
                    </div>

                    @if (session('success'))
                        <div class="alert alert-success bg-success bg-opacity-10 border-0 text-success mb-4" role="alert">
                            <i class="material-symbols-outlined align-middle fs-18 me-1">check_circle</i>
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4" role="alert">
                            <i class="material-symbols-outlined align-middle fs-18 me-1">error</i>
                            {{ session('error') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger bg-danger bg-opacity-10 border-0 text-danger mb-4" role="alert">
                            <div class="fw-semibold mb-1">
                                <i class="material-symbols-outlined align-middle fs-18 me-1">error</i>
                                Transaksi belum bisa diproses.
                            </div>

                            <ul class="mb-0 ps-4">
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
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Pilih Produk</h3>
                                            <p class="text-body mb-0 fs-13">
                                                Produk aktif dengan stok tersedia dapat ditambahkan ke keranjang.
                                            </p>
                                        </div>

                                        <a href="{{ route('products.index') }}" class="btn btn-outline-primary">
                                            Kelola Produk
                                        </a>
                                    </div>

                                    <div class="koc-filter-card mb-4">
                                        <form action="{{ route('pos.index') }}" method="get">
                                            <div class="row g-2 align-items-center">
                                                <div class="col-lg-6 col-md-6">
                                                    <div class="position-relative table-src-form me-0">
                                                        <input
                                                            type="text"
                                                            name="q"
                                                            value="{{ $search }}"
                                                            class="form-control"
                                                            placeholder="Cari produk, SKU, barcode..."
                                                        >
                                                        <i class="material-symbols-outlined position-absolute top-50 start-0 translate-middle-y">search</i>
                                                    </div>
                                                </div>

                                                <div class="col-lg-4 col-md-6">
                                                    <select name="category_id" class="form-select form-control">
                                                        <option value="">Semua Kategori</option>
                                                        @foreach ($categories as $category)
                                                            <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>
                                                                {{ $category->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>

                                                <div class="col-lg-2 col-md-12">
                                                    <button type="submit" class="btn btn-outline-primary w-100">
                                                        Filter
                                                    </button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>

                                    <div class="row g-3">
                                        @forelse ($products as $product)
                                            <div class="col-xxl-4 col-md-6">
                                                <div class="koc-product-card">
                                                    <div class="d-flex align-items-start mb-3">
                                                        <div class="koc-product-icon bg-primary bg-opacity-10 text-primary me-3">
                                                            <i class="material-symbols-outlined fs-22">inventory_2</i>
                                                        </div>

                                                        <div>
                                                            <h6 class="fw-semibold fs-15 mb-1">{{ $product->name }}</h6>
                                                            <span class="fs-12 text-body">
                                                                {{ $product->category?->name ?? 'Tanpa Kategori' }}
                                                            </span>
                                                        </div>
                                                    </div>

                                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                                        <span class="badge bg-light text-body border p-2 fs-12 fw-normal">
                                                            SKU: {{ $product->sku }}
                                                        </span>

                                                        @if ($product->stock > 0)
                                                            <span class="badge bg-success bg-opacity-10 text-success p-2 fs-12 fw-normal">
                                                                Stok {{ $product->stock }} {{ $product->unit }}
                                                            </span>
                                                        @else
                                                            <span class="badge bg-danger bg-opacity-10 text-danger p-2 fs-12 fw-normal">
                                                                Stok Kosong
                                                            </span>
                                                        @endif
                                                    </div>

                                                    <h5 class="fw-bold mb-3 koc-price">
                                                        {{ $rupiah($product->selling_price) }}
                                                    </h5>

                                                    <form action="{{ route('pos.cart.add') }}" method="post">
                                                        @csrf

                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <input type="hidden" name="quantity" value="1">

                                                        <button
                                                            type="submit"
                                                            class="btn btn-primary text-white w-100"
                                                            @disabled($product->stock <= 0)
                                                        >
                                                            <i class="material-symbols-outlined align-middle fs-18 me-1">add_shopping_cart</i>
                                                            Tambah
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        @empty
                                            <div class="col-12">
                                                <div class="text-center py-5 bg-white rounded-3 border">
                                                    <div class="mb-3">
                                                        <i class="material-symbols-outlined text-body fs-40">inventory_2</i>
                                                    </div>

                                                    <h6 class="fw-semibold mb-1">Produk tidak ditemukan</h6>

                                                    <p class="text-body mb-3">
                                                        Tambahkan produk aktif atau ubah kata kunci pencarian.
                                                    </p>

                                                    <a href="{{ route('products.index') }}" class="btn btn-primary text-white">
                                                        Kelola Produk
                                                    </a>
                                                </div>
                                            </div>
                                        @endforelse
                                    </div>

                                    <div class="d-flex justify-content-center justify-content-sm-between align-items-center text-center flex-wrap gap-2 showing-wrap mt-4">
                                        <span class="fs-13 fw-medium">
                                            Menampilkan {{ $products->count() }} dari {{ $products->total() }} produk
                                        </span>

                                        <div>
                                            {{ $products->links('pagination::bootstrap-5') }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-4">
                            <div class="card bg-white border-0 rounded-3 mb-4 koc-cart-panel">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                                        <div>
                                            <h3 class="mb-1">Keranjang</h3>
                                            <p class="text-body mb-0 fs-13">
                                                {{ $totals['cart_count'] }} produk, {{ $totals['total_items'] }} item
                                            </p>
                                        </div>

                                        @if (! empty($cart))
                                            <form action="{{ route('pos.cart.clear') }}" method="post" onsubmit="return confirm('Kosongkan semua keranjang?')">
                                                @csrf
                                                @method('DELETE')

                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    Kosongkan
                                                </button>
                                            </form>
                                        @endif
                                    </div>

                                    @if (empty($cart))
                                        <div class="text-center py-5 bg-white rounded-3 border">
                                            <div class="mb-3">
                                                <i class="material-symbols-outlined text-body fs-40">shopping_cart</i>
                                            </div>

                                            <h6 class="fw-semibold mb-1">Keranjang masih kosong</h6>

                                            <p class="text-body mb-0">
                                                Klik tombol tambah pada produk untuk memulai transaksi.
                                            </p>
                                        </div>
                                    @else
                                        <div class="d-flex flex-column gap-3 mb-4">
                                            @foreach ($cart as $item)
                                                <div class="koc-cart-item">
                                                    <div class="d-flex justify-content-between align-items-start gap-3">
                                                        <div>
                                                            <h6 class="fw-semibold fs-14 mb-1">{{ $item['name'] }}</h6>
                                                            <span class="fs-12 text-body">
                                                                {{ $item['sku'] }} · {{ $rupiah($item['selling_price']) }}
                                                            </span>
                                                        </div>

                                                        <form action="{{ route('pos.cart.remove', $item['product_id']) }}" method="post">
                                                            @csrf
                                                            @method('DELETE')

                                                            <button type="submit" class="border-0 bg-transparent p-0">
                                                                <i class="material-symbols-outlined fs-18 text-danger">delete</i>
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <div class="d-flex justify-content-between align-items-center gap-3 mt-3">
                                                        <form action="{{ route('pos.cart.update', $item['product_id']) }}" method="post" class="d-flex align-items-center gap-2">
                                                            @csrf
                                                            @method('PATCH')

                                                            <input
                                                                type="number"
                                                                name="quantity"
                                                                value="{{ $item['quantity'] }}"
                                                                min="1"
                                                                max="{{ $item['stock'] }}"
                                                                class="form-control"
                                                                style="width: 90px;"
                                                            >

                                                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                                                Update
                                                            </button>
                                                        </form>

                                                        <strong class="koc-price">
                                                            {{ $rupiah($item['selling_price'] * $item['quantity']) }}
                                                        </strong>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>

                                        <form action="{{ route('pos.checkout') }}" method="post">
                                            @csrf

                                            <div class="border-top pt-3 mb-3">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">Subtotal</span>
                                                    <strong id="pos-subtotal-text">{{ $rupiah($totals['subtotal']) }}</strong>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Diskon</label>
                                                    <input
                                                        type="number"
                                                        name="discount_amount"
                                                        id="discount_amount"
                                                        value="{{ old('discount_amount', 0) }}"
                                                        min="0"
                                                        class="form-control"
                                                    >
                                                    <div class="fs-13 text-body mt-1">
                                                        Diskon akan mengurangi dasar pengenaan pajak.
                                                    </div>
                                                </div>

                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="text-body">
                                                        Pajak Default
                                                        @if (($taxPercentage ?? 0) > 0)
                                                            ({{ number_format((float) $taxPercentage, 2, ',', '.') }}%)
                                                        @endif
                                                    </span>
                                                    <strong id="pos-tax-text">{{ $rupiah($totals['tax_amount']) }}</strong>
                                                </div>

                                                <div class="d-flex justify-content-between mb-3">
                                                    <span class="text-body">Estimasi Total</span>
                                                    <h5 class="fw-bold mb-0" id="pos-total-text">{{ $rupiah($totals['total']) }}</h5>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Nama Pelanggan</label>
                                                <input
                                                    type="text"
                                                    name="customer_name"
                                                    value="{{ old('customer_name') }}"
                                                    class="form-control"
                                                    placeholder="Customer Umum"
                                                >
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Metode Pembayaran <span class="text-danger">*</span></label>
                                                <select name="payment_method" class="form-select" required>
                                                    <option value="CASH" @selected(old('payment_method', 'CASH') === 'CASH')>Tunai</option>
                                                    <option value="QRIS" @selected(old('payment_method') === 'QRIS')>QRIS</option>
                                                    <option value="TRANSFER" @selected(old('payment_method') === 'TRANSFER')>Transfer</option>
                                                    <option value="EDC" @selected(old('payment_method') === 'EDC')>EDC / Kartu</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Jumlah Bayar <span class="text-danger">*</span></label>
                                                <input
                                                    type="number"
                                                    name="paid_amount"
                                                    id="paid_amount"
                                                    value="{{ old('paid_amount', (int) $totals['total']) }}"
                                                    min="0"
                                                    class="form-control"
                                                    required
                                                >
                                            </div>

                                            <div class="mb-4">
                                                <label class="form-label">Catatan</label>
                                                <textarea
                                                    name="note"
                                                    rows="3"
                                                    class="form-control"
                                                    placeholder="Opsional"
                                                >{{ old('note') }}</textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary text-white w-100">
                                                <i class="material-symbols-outlined align-middle fs-18 me-1">payments</i>
                                                Simpan Transaksi
                                            </button>
                                        </form>
                                    @endif
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

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const subtotal = {{ (float) $totals['subtotal'] }};
                const taxPercentage = {{ (float) ($taxPercentage ?? 0) }};

                const discountInput = document.getElementById('discount_amount');
                const paidAmountInput = document.getElementById('paid_amount');
                const taxText = document.getElementById('pos-tax-text');
                const totalText = document.getElementById('pos-total-text');

                const formatRupiah = function (value) {
                    return new Intl.NumberFormat('id-ID', {
                        style: 'currency',
                        currency: 'IDR',
                        maximumFractionDigits: 0
                    }).format(value);
                };

                const calculateTotal = function () {
                    if (!discountInput || !taxText || !totalText) {
                        return;
                    }

                    let discount = parseFloat(discountInput.value || 0);

                    if (isNaN(discount) || discount < 0) {
                        discount = 0;
                    }

                    if (discount > subtotal) {
                        discount = subtotal;
                        discountInput.value = subtotal;
                    }

                    const taxableAmount = Math.max(0, subtotal - discount);
                    const taxAmount = Math.round(taxableAmount * (taxPercentage / 100));
                    const total = Math.max(0, taxableAmount + taxAmount);

                    taxText.textContent = formatRupiah(taxAmount);
                    totalText.textContent = formatRupiah(total);

                    if (paidAmountInput && (!paidAmountInput.dataset.userChanged || paidAmountInput.dataset.userChanged === 'false')) {
                        paidAmountInput.value = total;
                    }
                };

                if (paidAmountInput) {
                    paidAmountInput.addEventListener('input', function () {
                        paidAmountInput.dataset.userChanged = 'true';
                    });
                }

                if (discountInput) {
                    discountInput.addEventListener('input', calculateTotal);
                    calculateTotal();
                }
            });
        </script>
        </body>
        </html>
    </body>
</html>