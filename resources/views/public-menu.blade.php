<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Menu - {{ $storeSetting->store_name }}</title>

        <style>
            * {
                box-sizing: border-box;
            }

            body {
                margin: 0;
                font-family: Arial, Helvetica, sans-serif;
                background: #f3f4f6;
                color: #111827;
            }

            .page {
                min-height: 100vh;
                padding: 20px;
            }

            .container {
                max-width: 1180px;
                margin: 0 auto;
            }

            .header {
                background: #ffffff;
                border-radius: 22px;
                padding: 24px;
                margin-bottom: 18px;
                box-shadow: 0 12px 32px rgba(15, 23, 42, 0.08);
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: center;
                flex-wrap: wrap;
            }

            .store-name {
                font-size: 28px;
                font-weight: 800;
                margin-bottom: 6px;
            }

            .muted {
                color: #64748b;
            }

            .btn {
                border: 0;
                background: #605dff;
                color: #ffffff;
                padding: 12px 16px;
                border-radius: 12px;
                font-size: 15px;
                text-decoration: none;
                cursor: pointer;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 6px;
            }

            .btn-outline {
                border: 1px solid #cbd5e1;
                background: #ffffff;
                color: #111827;
            }

            .btn-danger {
                background: #ef4444;
            }

            .alert-success {
                background: #dcfce7;
                color: #15803d;
                padding: 14px 16px;
                border-radius: 14px;
                margin-bottom: 14px;
            }

            .alert-danger {
                background: #fee2e2;
                color: #b91c1c;
                padding: 14px 16px;
                border-radius: 14px;
                margin-bottom: 14px;
            }

            .layout {
                display: grid;
                grid-template-columns: minmax(0, 1fr) 360px;
                gap: 18px;
                align-items: start;
            }

            .card {
                background: #ffffff;
                border-radius: 18px;
                padding: 20px;
                margin-bottom: 18px;
                box-shadow: 0 10px 28px rgba(15, 23, 42, 0.06);
            }

            .filter {
                display: grid;
                grid-template-columns: 1fr 240px auto;
                gap: 10px;
            }

            .form-control {
                width: 100%;
                min-height: 46px;
                border: 1px solid #d1d5db;
                border-radius: 12px;
                padding: 10px 12px;
                font-size: 15px;
                background: #ffffff;
            }

            .product-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                gap: 14px;
            }

            .product-card {
                border: 1px solid #eef2f7;
                border-radius: 18px;
                padding: 16px;
                background: #ffffff;
            }

            .product-title {
                font-size: 17px;
                font-weight: 700;
                margin: 0 0 6px;
            }

            .badge {
                display: inline-flex;
                padding: 6px 10px;
                border-radius: 999px;
                font-size: 12px;
                background: #eef2ff;
                color: #4f46e5;
                margin-right: 4px;
            }

            .badge-success {
                background: #dcfce7;
                color: #15803d;
            }

            .price {
                font-size: 20px;
                font-weight: 800;
                margin: 14px 0;
            }

            .qty-row {
                display: grid;
                grid-template-columns: 90px 1fr;
                gap: 8px;
            }

            .cart {
                position: sticky;
                top: 16px;
            }

            .cart-item {
                border-bottom: 1px solid #eef2f7;
                padding: 12px 0;
            }

            .cart-item:last-child {
                border-bottom: 0;
            }

            .cart-actions {
                display: grid;
                grid-template-columns: 90px 1fr auto;
                gap: 8px;
                margin-top: 8px;
            }

            .summary-row {
                display: flex;
                justify-content: space-between;
                gap: 12px;
                padding: 8px 0;
                border-bottom: 1px solid #eef2f7;
            }

            .summary-row:last-child {
                border-bottom: 0;
            }

            .pagination {
                display: flex;
                gap: 8px;
                justify-content: center;
                margin-top: 16px;
            }

            @media (max-width: 1024px) {
                .layout {
                    grid-template-columns: 1fr;
                }

                .cart {
                    position: static;
                }

                .product-grid {
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                }
            }

            @media (max-width: 640px) {
                .page {
                    padding: 12px;
                }

                .product-grid,
                .filter {
                    grid-template-columns: 1fr;
                }

                .header {
                    padding: 18px;
                }

                .store-name {
                    font-size: 24px;
                }
            }
        </style>
    </head>

    <body>
        @php
            $rupiah = fn ($value) => 'Rp ' . number_format((float) $value, 0, ',', '.');
        @endphp

        <div class="page">
            <div class="container">
                <div class="header">
                    <div>
                        <div class="store-name">{{ $storeSetting->store_name }}</div>
                        <div class="muted">
                            {{ $storeSetting->address ?: 'Menu order online' }}
                        </div>
                    </div>

                    <div>
                        <a href="{{ route('public.checkout') }}" class="btn">
                            Keranjang · {{ number_format($totals['total_items'], 0, ',', '.') }} item
                        </a>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert-success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert-danger">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert-danger">
                        <strong>Data belum bisa diproses.</strong>
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="layout">
                    <main>
                        <div class="card">
                            <form action="{{ route('public.menu') }}" method="get" class="filter">
                                <input
                                    type="text"
                                    name="q"
                                    value="{{ $search }}"
                                    class="form-control"
                                    placeholder="Cari produk..."
                                >

                                <select name="category_id" class="form-control">
                                    <option value="">Semua Kategori</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" @selected((int) $categoryId === $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <button type="submit" class="btn">
                                    Cari
                                </button>
                            </form>
                        </div>

                        <div class="product-grid">
                            @forelse ($products as $product)
                                <div class="product-card">
                                    <h3 class="product-title">{{ $product->name }}</h3>

                                    <div>
                                        <span class="badge">{{ $product->category?->name ?: 'Tanpa Kategori' }}</span>
                                        <span class="badge badge-success">
                                            Stok {{ number_format($product->available_stock, 0, ',', '.') }} {{ $product->unit }}
                                        </span>
                                    </div>

                                    @if ($product->sku)
                                        <p class="muted">SKU: {{ $product->sku }}</p>
                                    @endif

                                    <div class="price">{{ $rupiah($product->selling_price) }}</div>

                                    @if ((int) $product->available_stock > 0)
                                        <form action="{{ route('public.cart.add', $product) }}" method="post">
                                            @csrf

                                            <div class="qty-row">
                                                <input
                                                    type="number"
                                                    name="quantity"
                                                    value="1"
                                                    min="1"
                                                    max="{{ $product->available_stock }}"
                                                    class="form-control"
                                                >

                                                <button type="submit" class="btn">
                                                    Tambah
                                                </button>
                                            </div>
                                        </form>
                                    @else
                                        <button type="button" class="btn btn-outline" disabled>
                                            Stok Kosong
                                        </button>
                                    @endif
                                </div>
                            @empty
                                <div class="card">
                                    <h3>Produk tidak ditemukan</h3>
                                    <p class="muted">Ubah kata kunci atau kategori pencarian.</p>
                                </div>
                            @endforelse
                        </div>

                        @if ($products->hasPages())
                            <div class="pagination">
                                @if ($products->previousPageUrl())
                                    <a href="{{ $products->previousPageUrl() }}" class="btn btn-outline">Sebelumnya</a>
                                @endif

                                @if ($products->nextPageUrl())
                                    <a href="{{ $products->nextPageUrl() }}" class="btn btn-outline">Berikutnya</a>
                                @endif
                            </div>
                        @endif
                    </main>

                    <aside class="cart">
                        <div class="card">
                            <h2 style="margin-top: 0;">Keranjang</h2>

                            @forelse ($cartItems as $cartItem)
                                @php
                                    $cartProduct = $cartItem['product'];
                                @endphp

                                <div class="cart-item">
                                    <strong>{{ $cartProduct->name }}</strong>
                                    <div class="muted">
                                        {{ $cartItem['quantity'] }} {{ $cartProduct->unit }}
                                        x
                                        {{ $rupiah($cartProduct->selling_price) }}
                                    </div>

                                    <div>
                                        Subtotal:
                                        <strong>{{ $rupiah($cartItem['subtotal']) }}</strong>
                                    </div>

                                    <div class="cart-actions">
                                        <form
                                            action="{{ route('public.cart.update', $cartProduct) }}"
                                            method="post"
                                            style="display: contents;"
                                        >
                                            @csrf
                                            @method('PATCH')

                                            <input
                                                type="number"
                                                name="quantity"
                                                value="{{ $cartItem['quantity'] }}"
                                                min="0"
                                                max="{{ $cartProduct->current_stock }}"
                                                class="form-control"
                                            >

                                            <button type="submit" class="btn btn-outline">
                                                Update
                                            </button>
                                        </form>

                                        <form action="{{ route('public.cart.remove', $cartProduct) }}" method="post">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-danger">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @empty
                                <p class="muted">Keranjang masih kosong.</p>
                            @endforelse

                            <div style="margin-top: 14px;">
                                <div class="summary-row">
                                    <span>Subtotal</span>
                                    <strong>{{ $rupiah($totals['subtotal']) }}</strong>
                                </div>

                                <div class="summary-row">
                                    <span>Pajak</span>
                                    <strong>{{ $rupiah($totals['tax_amount']) }}</strong>
                                </div>

                                <div class="summary-row">
                                    <span>Total</span>
                                    <strong>{{ $rupiah($totals['total']) }}</strong>
                                </div>
                            </div>

                            @if ($cartItems->isNotEmpty())
                                <div style="display: grid; gap: 8px; margin-top: 16px;">
                                    <a href="{{ route('public.checkout') }}" class="btn">
                                        Checkout
                                    </a>

                                    <form action="{{ route('public.cart.clear') }}" method="post">
                                        @csrf
                                        @method('DELETE')

                                        <button type="submit" class="btn btn-outline" style="width: 100%;">
                                            Kosongkan Keranjang
                                        </button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </aside>
                </div>
            </div>
        </div>
    </body>
</html>