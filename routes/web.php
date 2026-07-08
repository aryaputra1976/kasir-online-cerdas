<?php

use App\Http\Controllers\CategoryController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\PosController;
use App\Http\Controllers\SalesReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BestSellingProductReportController;
use App\Http\Controllers\StockReportController;
use App\Http\Controllers\ProfitLossReportController;
use App\Http\Controllers\StoreSettingController;

/*
|--------------------------------------------------------------------------
| Kasir Online Cerdas Laravel
|--------------------------------------------------------------------------
|
| Route awal masih memakai view bawaan Trezo sebagai placeholder.
| Nanti setelah modul dibuat, route ini akan diarahkan ke Controller asli.
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});

/*
|--------------------------------------------------------------------------
| Auth Demo
|--------------------------------------------------------------------------
| Sementara masih memakai halaman login/register bawaan Trezo.
| Nanti akan diganti auth Laravel asli.
*/

Route::view('/login', 'login')->name('login');
Route::view('/register', 'register')->name('register');

/*
|--------------------------------------------------------------------------
| Dashboard
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Master Data
|--------------------------------------------------------------------------
*/

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

Route::view('/pelanggan', 'customers')->name('customers.index');
Route::view('/pelanggan/detail', 'customer-details')->name('customers.show');

/*
|--------------------------------------------------------------------------
| Transaksi
|--------------------------------------------------------------------------
*/

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

Route::view('/order-online', 'orders')->name('online-orders.index');
Route::view('/order-online/detail', 'order-details')->name('online-orders.show');
Route::view('/order-online/tracking', 'order-tracking')->name('online-orders.tracking');

Route::view('/pembayaran', 'invoice-list')->name('payments.index');
Route::view('/pembayaran/detail', 'invoice-details')->name('payments.show');

/*
|--------------------------------------------------------------------------
| Stok
|--------------------------------------------------------------------------
*/

Route::get('/stok-barang', [StockController::class, 'index'])->name('stocks.index');
Route::get('/mutasi-stok', [StockMovementController::class, 'index'])->name('stocks.movements');
Route::post('/mutasi-stok', [StockMovementController::class, 'store'])->name('stocks.movements.store');
Route::get('/stok-menipis', [StockController::class, 'low'])->name('stocks.low');

/*
|--------------------------------------------------------------------------
| Laporan
|--------------------------------------------------------------------------
*/

Route::view('/laporan', 'reports')->name('reports.index');
Route::get('/laporan/penjualan', [SalesReportController::class, 'index'])
    ->name('reports.sales');
Route::get('/laporan/penjualan/export', [SalesReportController::class, 'export'])
    ->name('reports.sales.export');
Route::get('/laporan/produk-terlaris', [BestSellingProductReportController::class, 'index'])->name('reports.best-products');
Route::get('/laporan/laba-rugi', [ProfitLossReportController::class, 'index'])
    ->name('reports.profit-loss');
Route::get('/laporan/stok', [StockReportController::class, 'index'])->name('reports.stock');


/*
|--------------------------------------------------------------------------
| Pengaturan
|--------------------------------------------------------------------------
*/

Route::get('/pengaturan/profil-toko', [StoreSettingController::class, 'edit'])
    ->name('settings.store');

Route::put('/pengaturan/profil-toko', [StoreSettingController::class, 'update'])
    ->name('settings.store.update');

Route::delete('/pengaturan/profil-toko/logo', [StoreSettingController::class, 'removeLogo'])
    ->name('settings.store.logo.destroy');

Route::view('/pengaturan/user-role', 'users-list')->name('settings.users');
Route::view('/pengaturan/tambah-user', 'add-user')->name('settings.users.create');
Route::view('/pengaturan/template-struk', 'invoice-details')->name('settings.receipt-template');

/*
|--------------------------------------------------------------------------
| Halaman Publik Order Online
|--------------------------------------------------------------------------
| Halaman ini untuk customer. Untuk sementara masih pakai placeholder Trezo.
| Nanti akan dibuat custom mobile-friendly.
*/

Route::view('/menu', 'landing-page')->name('public.menu');
Route::view('/order', 'landing-page')->name('public.order');
Route::view('/checkout', 'checkout')->name('public.checkout');

Route::get('/tracking/{token}', function (string $token) {
    return view('order-tracking', [
        'token' => $token,
    ]);
})->name('public.tracking');

/*
|--------------------------------------------------------------------------
| Demo Trezo
|--------------------------------------------------------------------------
| Route ini supaya halaman demo Trezo lama tetap bisa dibuka jika dibutuhkan.
| Contoh:
| /demo/pos-system
| /demo/products-list
| /demo/orders
| /demo/reports
*/

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