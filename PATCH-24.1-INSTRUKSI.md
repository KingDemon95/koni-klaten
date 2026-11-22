# PATCH 24.1: Perbaikan default warna konten halaman publik

Patch ini melengkapi tiga key warna yang sudah digunakan oleh controller, editor, layout publik, dan regression test, tetapi belum tersedia pada `WebsiteAppearanceSetting::defaultConfig()`.

Tambahan default:
- `section_background_color`: `#FFFFFF`
- `section_heading_color`: `#0F172A`
- `section_text_color`: `#64748B`

Tidak ada migration, perubahan route, atau perubahan database.

## Pemasangan
Jalankan dari root project `~/koni-klaten`:

```bash
unzip -o PATCH-24.1-DEFAULT-COLORS-FIX.zip -d .
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=WebsiteAppearanceTest
```

Jika tes khusus lulus, jalankan regression test penuh:

```bash
docker compose exec laravel.test php artisan test
```
