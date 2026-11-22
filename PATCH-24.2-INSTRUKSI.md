# PATCH 24.2 - Public View Composer Fix

## Tujuan
Memperbaiki error HTTP 500 `Undefined array key "hero"` pada halaman publik dan preview tampilan website.

Penyebab: data konfigurasi website sebelumnya hanya dibagikan melalui View Composer `layouts.public`. Blade merender isi view halaman sebelum layout, sehingga variabel seperti `$websitePageSections` belum tersedia ketika halaman anak mengaksesnya.

## File yang diperbarui
- `app/Providers/AppServiceProvider.php`

Composer sekarang berjalan pada setiap view halaman publik yang memakai konfigurasi visual dan pada layout publik. Data disimpan pada request agar query tidak dijalankan dua kali ketika composer halaman dan layout sama-sama berjalan.

## Cara memasang
Jalankan dari root proyek `~/koni-klaten`:

```bash
unzip -o PATCH-24.2-PUBLIC-VIEW-COMPOSER-FIX.zip -d .
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=WebsiteAppearanceTest
```

Jika seluruh test tersebut lulus, jalankan regression suite:

```bash
docker compose exec laravel.test php artisan test
```

## Catatan
- Tidak ada migration.
- Tidak ada perubahan database.
- Tidak ada perubahan route.
- Tidak mengubah konfigurasi dashboard internal.
- Patch ini ditujukan untuk dipasang setelah Patch 24 dan Patch 24.1.
