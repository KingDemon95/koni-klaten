# PATCH 14.1 — Koreksi tampilan feedback dan test Artisan

Patch koreksi untuk KONI Klaten setelah Patch 14.

## Penyebab yang diperbaiki

1. Lima layout portal berisi `@include(\\'partials.demo-feedback\\')` sehingga Blade memperlakukannya sebagai teks literal. Patch ini mengubahnya menjadi directive Blade yang benar: `@include('partials.demo-feedback')`.
2. Test registrasi perintah demo menggunakan `Artisan::has()`, method yang tidak tersedia pada Kernel Laravel proyek. Test diperbaiki dengan memeriksa `Artisan::all()`.

## File yang diperbarui

- `resources/views/layouts/admin.blade.php`
- `resources/views/layouts/staff.blade.php`
- `resources/views/layouts/cabor.blade.php`
- `resources/views/layouts/atlet.blade.php`
- `resources/views/layouts/pelatih.blade.php`
- `tests/Feature/DemoExperienceTest.php`

## Cara memasang

1. Ekstrak `koni-klaten-patch-14.1.zip` ke folder utama `~/koni-klaten`.
2. Izinkan keenam file yang sama ditimpa.
3. Jangan menimpa `.env`.

Jalankan:

```bash
cd ~/koni-klaten
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=DemoExperienceTest
```

Jika empat test baru lulus, jalankan seluruh suite:

```bash
docker compose exec laravel.test php artisan test
```

Target berdasarkan hasil Patch 13: **116 passed, 0 failed**.

## Catatan

- Tidak ada migration database.
- Tidak perlu rebuild Docker.
- Tidak perlu `npm run build` lagi karena patch ini hanya memperbaiki Blade dan test.
- Perintah `koni:demo-check` tetap didefinisikan di `routes/console.php`; test memeriksa daftar command terdaftar menggunakan API Kernel yang tersedia.
