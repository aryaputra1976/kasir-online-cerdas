# Deployment Production

Panduan ini untuk shared hosting atau VPS yang menjalankan MySQL/MariaDB.

## Requirement

- PHP sesuai versi Laravel project dan ekstensi umum: `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `json`, `ctype`, `tokenizer`, `xml`, `curl`, `gd`.
- MySQL/MariaDB production.
- Composer tersedia di server atau proses build.
- Node/npm hanya diperlukan bila asset frontend dibuild di server.

## Langkah Aman

1. Upload atau clone source aplikasi.
2. Jalankan `composer install --no-dev --optimize-autoloader`.
3. Copy `.env.example` menjadi `.env`, lalu isi credential production.
4. Jalankan `php artisan key:generate` hanya untuk instalasi baru. Jangan mengganti `APP_KEY` pada aplikasi production yang sudah berjalan.
5. Pastikan `APP_URL` memakai HTTPS dan `APP_DEBUG=false`.
6. Backup database sebelum migration.
7. Aktifkan maintenance mode: `php artisan down`.
8. Jalankan `php artisan migrate --force`.
9. Pastikan permission `storage` dan `bootstrap/cache` writable.
10. Siapkan asset Trezo di `public/assets`.
11. Bila memakai build frontend, jalankan `npm ci` lalu `npm run build`.
12. Jalankan `php artisan config:cache`, `php artisan route:cache`, dan `php artisan view:cache`.
13. Jalankan `php artisan app:check-assets`.
14. Jalankan `php artisan app:audit-data-integrity`.
15. Jalankan `php artisan app:deployment-check`.
16. Restart queue bila dipakai: `php artisan queue:restart`.
17. Pasang scheduler cron: `* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1`.
18. Matikan maintenance mode: `php artisan up`.
19. Smoke test: login, buka dashboard, buat transaksi kecil, cek order online, cek upload bukti pembayaran.

## Jangan Dilakukan Di Production

- Jangan menjalankan `migrate:fresh`, `db:wipe`, atau command penghapus data.
- Jangan menjalankan `composer update` saat deploy.
- Jangan menjalankan `key:generate` pada aplikasi yang sudah berjalan.
- Jangan commit `.env`, backup database, credential, private upload, atau log.

## Rollback

1. Aktifkan maintenance mode.
2. Restore source release sebelumnya.
3. Restore database dari backup bila migration sudah mengubah schema/data.
4. Jalankan cache ulang: `php artisan config:cache`, `route:cache`, `view:cache`.
5. Jalankan smoke test.
6. Matikan maintenance mode.
