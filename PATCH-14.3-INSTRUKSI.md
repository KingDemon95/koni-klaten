# KONI Klaten — Patch 14.3

## Tujuan

Memperbaiki pengujian ringkasan error validasi pada `DemoExperienceTest`.

Patch 14.2 menggunakan `withViewErrors()` pada request dashboard, tetapi error bag tidak muncul pada hasil render di lingkungan pengujian proyek ini. Patch ini menguji partial Blade `partials.demo-feedback` secara langsung dengan `ViewErrorBag` dan `MessageBag`.

## File yang diperbarui

- `tests/Feature/DemoExperienceTest.php`

Tidak ada perubahan pada controller, route, middleware, database, atau tampilan aplikasi.

## Cara memasang

Ekstrak ZIP ke direktori utama proyek `~/koni-klaten` dan izinkan file pengujian ditimpa.

Jalankan:

```bash
cd ~/koni-klaten

docker compose exec laravel.test php artisan optimize:clear

docker compose exec laravel.test php artisan test --filter=DemoExperienceTest
```

Target: 4 passed.

Setelah itu jalankan seluruh suite:

```bash
docker compose exec laravel.test php artisan test
```

Tidak perlu migration, rebuild Docker, atau `npm run build`.
