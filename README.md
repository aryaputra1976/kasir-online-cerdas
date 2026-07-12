# Kasir Online Cerdas

Kasir Online Cerdas adalah aplikasi POS dan order online berbasis Laravel untuk UMKM Indonesia. Aplikasi ini membantu toko mengelola transaksi kasir, produk, stok, pelanggan, pembayaran, laporan, serta pemesanan online dalam satu dashboard.

## Fitur Utama

- Dashboard operasional toko
- Kasir POS dengan cetak struk
- Manajemen kategori, produk, dan pelanggan
- Order online publik dengan tracking pesanan
- Validasi pembayaran online
- Mutasi stok dan notifikasi stok menipis
- Laporan penjualan, laba rugi, stok, dan order online
- Pengaturan user, role, toko, metode pembayaran, dan template struk

## Role User

- `OWNER`: akses penuh ke seluruh modul
- `ADMIN`: akses operasional dan pengaturan utama
- `KASIR`: akses dashboard, POS, order online, dan pembayaran

## Stack

- PHP 8.2+
- Laravel 11
- MySQL atau MariaDB
- Blade
- Trezo admin template

## Instalasi Lokal

1. Clone repository ini.
2. Install dependency PHP dengan `composer install`.
3. Salin environment dengan `copy .env.example .env`.
4. Generate app key dengan `php artisan key:generate`.
5. Atur koneksi database pada `.env`.
6. Jalankan migrasi dengan `php artisan migrate`.
7. Jalankan server lokal dengan `php artisan serve`.

## Konfigurasi Penting

- Pastikan `APP_URL` sesuai domain atau host lokal Anda.
- Gunakan `APP_ENV=production` dan `APP_DEBUG=false` saat deploy.
- Session default memakai database, jadi tabel session harus ikut termigrasi.

## Catatan Asset Trezo

Folder `public/assets` saat ini diabaikan oleh `.gitignore`. Itu berarti asset template Trezo tidak otomatis ikut ke GitHub atau hasil clone baru.

Jika project ini dipakai untuk deploy pribadi, pastikan folder `public/assets` tersedia di server tujuan. Jika asset Trezo tidak boleh dipublikasikan karena lisensi, distribusikan asset tersebut melalui paket deployment privat atau proses copy manual setelah clone.

Setelah copy asset, jalankan pemeriksaan cepat:

```bash
php artisan app:check-assets
```

Panduan ringkas deploy asset tersedia di [docs/deployment-assets.md](/D:/kasir-online-cerdas/docs/deployment-assets.md:1).

## Akun & Akses

- Login publik tidak menyediakan registrasi mandiri.
- Akun user dibuat melalui panel admin oleh owner atau admin.
- Route admin sudah diproteksi dengan autentikasi dan role middleware.

## Testing

Menjalankan test:

```bash
php artisan test
```

## Roadmap Singkat

- Penyempurnaan README dengan screenshot produk
- Standarisasi asset deployment
- Peningkatan coverage test fitur utama
- Hardening keamanan dan audit produksi
