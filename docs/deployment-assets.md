# Deployment Asset Trezo

Project ini memakai asset template Trezo di folder `public/assets`, tetapi folder tersebut tidak diikutkan ke GitHub karena pertimbangan distribusi dan lisensi.

## Risiko

Jika repository ini di-clone ke server baru tanpa folder `public/assets`, tampilan login, dashboard, dan halaman admin lain bisa rusak atau tidak termuat sempurna.

## Yang Harus Disiapkan Saat Deploy

1. Siapkan salinan folder `public/assets` dari sumber privat yang sah.
2. Copy folder tersebut ke target deploy pada path `public/assets`.
3. Jalankan pemeriksaan dengan:

```bash
php artisan app:check-assets
```

4. Jika command gagal, lengkapi file atau folder yang dilaporkan.

## File Minimum yang Dicek

- `public/assets/css/style.css`
- `public/assets/js/custom/custom.js`
- `public/assets/images/logo.svg`

## Rekomendasi Tim

- Simpan asset Trezo dalam arsip deployment privat.
- Pastikan pipeline deploy atau checklist server selalu menyertakan langkah copy `public/assets`.
- Jalankan `php artisan app:check-assets` setelah proses copy asset selesai.
