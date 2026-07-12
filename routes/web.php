<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BestSellingProductReportController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OnlineOrderController;
use App\Http\Controllers\OnlineOrderReportController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentMethodSettingController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfitLossReportController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\PublicOrderTrackingController;
use App\Http\Controllers\ReceiptTemplateController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\StoreSettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserManagementController;
use App\Models\User;

/*
|--------------------------------------------------------------------------
| Kasir Online Cerdas Laravel
|--------------------------------------------------------------------------
|
| Route utama aplikasi Kasir Online Cerdas.
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Landing Page Produk
|--------------------------------------------------------------------------
|
| Halaman publik untuk memperkenalkan dan mendemokan aplikasi.
| Halaman ini tidak menggunakan layout admin Trezo.
|
*/

Route::view('/demo-produk', 'demo-produk')
    ->name('product.demo');

/*
|--------------------------------------------------------------------------
| Auth Demo
|--------------------------------------------------------------------------
| Sementara masih memakai halaman login/register bawaan Trezo.
| Nanti akan diganti auth Laravel asli.
*/

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store']);

    Route::get('/register', function () {
        return redirect()
            ->route('login')
            ->with('info', 'Pendaftaran akun publik tidak aktif. Hubungi owner atau admin.');
    })->name('register');
});

Route::middleware(['auth', 'active'])->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');

    Route::middleware('role:' . User::ROLE_OWNER . ',' . User::ROLE_ADMIN . ',' . User::ROLE_KASIR)
        ->group(function () {
            Route::prefix('pos')
                ->name('pos.')
                ->controller(PosController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/cart/add', 'addToCart')->name('cart.add');
                    Route::patch('/cart/{product}', 'updateCart')->name('cart.update');
                    Route::delete('/cart/{product}', 'removeCart')->name('cart.remove');
                    Route::delete('/cart', 'clearCart')->name('cart.clear');
                    Route::post('/checkout', 'checkout')->name('checkout');
                    Route::get('/struk/{sale}', 'receipt')->name('receipt');
                });

            Route::prefix('order-online')
                ->name('online-orders.')
                ->controller(OnlineOrderController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/{order}', 'show')->name('show');
                    Route::patch('/{order}/payment/confirm', 'confirmPayment')->name('payment.confirm');
                    Route::patch('/{order}/payment/reject', 'rejectPayment')->name('payment.reject');
                    Route::patch('/{order}/process', 'process')->name('process');
                    Route::patch('/{order}/complete', 'complete')->name('complete');
                    Route::patch('/{order}/cancel', 'cancel')->name('cancel');
                    Route::patch('/{order}/convert-sale', 'convertToSale')->name('convert-sale');
                });

            Route::prefix('pembayaran')
                ->name('payments.')
                ->controller(PaymentController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/{order}', 'show')->name('show');
                    Route::patch('/{order}/confirm', 'confirm')->name('confirm');
                    Route::patch('/{order}/reject', 'reject')->name('reject');
                });
        });

    Route::middleware('role:' . User::ROLE_OWNER . ',' . User::ROLE_ADMIN)
        ->group(function () {
            Route::prefix('kategori-produk')
                ->name('categories.')
                ->controller(CategoryController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::put('/{category}', 'update')->name('update');
                    Route::delete('/{category}', 'destroy')->name('destroy');
                });

            Route::prefix('produk')
                ->name('products.')
                ->controller(ProductController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::post('/', 'store')->name('store');
                    Route::put('/{product}', 'update')->name('update');
                    Route::delete('/{product}', 'destroy')->name('destroy');
                });

            Route::view('/produk/create', 'create-product')->name('products.create');
            Route::view('/produk/edit', 'edit-product')->name('products.edit');
            Route::view('/produk/detail', 'product-details')->name('products.show');

            Route::prefix('pelanggan')
                ->name('customers.')
                ->controller(CustomerController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/tambah', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{customer}', 'show')->name('show');
                    Route::get('/{customer}/edit', 'edit')->name('edit');
                    Route::put('/{customer}', 'update')->name('update');
                    Route::delete('/{customer}', 'destroy')->name('destroy');
                });

            Route::get('/stok-barang', [StockController::class, 'index'])->name('stocks.index');
            Route::get('/mutasi-stok', [StockMovementController::class, 'index'])->name('stocks.movements');
            Route::post('/mutasi-stok', [StockMovementController::class, 'store'])->name('stocks.movements.store');
            Route::get('/stok-menipis', [StockController::class, 'low'])->name('stocks.low');

            Route::get('/laporan/penjualan', [SalesReportController::class, 'index'])->name('sales.report');
            Route::get('/laporan/penjualan/export', [SalesReportController::class, 'export'])->name('sales.report.export');
            Route::get('/laporan/produk-terlaris', [BestSellingProductReportController::class, 'index'])->name('best-products.report');
            Route::get('/laporan/laba-rugi', [ProfitLossReportController::class, 'index'])->name('profit-loss.report');
            Route::get('/laporan/stok', [StockReportController::class, 'index'])->name('stock.report');
            Route::get('/laporan/order-online', [OnlineOrderReportController::class, 'index'])->name('reports.online-orders.index');
            Route::get('/laporan/order-online/export', [OnlineOrderReportController::class, 'export'])->name('reports.online-orders.export');

            Route::get('/pengaturan/profil-toko', [StoreSettingController::class, 'edit'])->name('settings.store');
            Route::put('/pengaturan/profil-toko', [StoreSettingController::class, 'update'])->name('settings.store.update');
            Route::delete('/pengaturan/profil-toko/logo', [StoreSettingController::class, 'removeLogo'])->name('settings.store.logo.destroy');

            Route::prefix('pengaturan/user-role')
                ->name('settings.users.')
                ->controller(UserManagementController::class)
                ->group(function () {
                    Route::get('/', 'index')->name('index');
                    Route::get('/tambah', 'create')->name('create');
                    Route::post('/', 'store')->name('store');
                    Route::get('/{user}/edit', 'edit')->name('edit');
                    Route::put('/{user}', 'update')->name('update');
                    Route::delete('/{user}', 'destroy')->name('destroy');
                });

            Route::get('/pengaturan/tambah-user', [UserManagementController::class, 'create'])->name('settings.users.create-legacy');
            Route::get('/pengaturan/template-struk', [ReceiptTemplateController::class, 'edit'])->name('settings.receipt-template');
            Route::put('/pengaturan/template-struk', [ReceiptTemplateController::class, 'update'])->name('settings.receipt-template.update');
            Route::get('/pengaturan/metode-pembayaran', [PaymentMethodSettingController::class, 'edit'])->name('settings.payment-methods');
            Route::put('/pengaturan/metode-pembayaran', [PaymentMethodSettingController::class, 'update'])->name('settings.payment-methods.update');
            Route::delete('/pengaturan/metode-pembayaran/qris', [PaymentMethodSettingController::class, 'removeQris'])->name('settings.payment-methods.qris.destroy');
        });
});

/*
|--------------------------------------------------------------------------
| Halaman Publik Order Online
|--------------------------------------------------------------------------
|
| Halaman publik untuk pelanggan melihat menu, membuat pesanan,
| melakukan checkout, dan melacak status order.
|
*/

Route::get('/menu', [PublicOrderController::class, 'menu'])
    ->name('public.menu');

Route::get('/order', [PublicOrderController::class, 'checkout'])
    ->name('public.order');

Route::get('/checkout', [PublicOrderController::class, 'checkout'])
    ->name('public.checkout');

Route::post('/checkout', [PublicOrderController::class, 'store'])
    ->name('public.checkout.store');

Route::post('/menu/cart/{product}', [PublicOrderController::class, 'addToCart'])
    ->name('public.cart.add');

Route::patch('/menu/cart/{product}', [PublicOrderController::class, 'updateCart'])
    ->name('public.cart.update');

Route::delete('/menu/cart/{product}', [PublicOrderController::class, 'removeCart'])
    ->name('public.cart.remove');

Route::delete('/menu/cart', [PublicOrderController::class, 'clearCart'])
    ->name('public.cart.clear');

Route::get('/tracking/{token}', [PublicOrderTrackingController::class, 'show'])
    ->name('public.tracking');

Route::post(
    '/tracking/{token}/payment-proof',
    [PublicOrderTrackingController::class, 'uploadPaymentProof']
)->name('public.payment-proof.upload');

/*
|--------------------------------------------------------------------------
| Demo Trezo
|--------------------------------------------------------------------------
|
| Route ini supaya halaman demo Trezo lama tetap bisa dibuka jika dibutuhkan.
|
| Contoh:
| /demo/pos-system
| /demo/products-list
| /demo/orders
| /demo/reports
|
*/

if (app()->environment('local')) {
    Route::get('/demo/{page}', function (string $page) {
        $normalizedPage = str_replace(['/', '\\'], '.', $page);
        $demoView = '_trezo_demo.' . $normalizedPage;

        if (view()->exists($demoView)) {
            return view($demoView);
        }

        abort_unless(view()->exists($normalizedPage), 404);

        return view($normalizedPage);
    })
        ->where('page', '[A-Za-z0-9\-_\/]+')
        ->name('demo.show');
}
