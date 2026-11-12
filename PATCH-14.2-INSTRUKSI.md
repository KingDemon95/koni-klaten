# PATCH 14.2 — Perbaikan test ringkasan validasi

Patch kecil lanjutan untuk memperbaiki satu pengujian DemoExperienceTest yang masih gagal setelah Patch 14.1.

## Perubahan

- Hanya memperbarui `tests/Feature/DemoExperienceTest.php`.
- Pengujian validasi kini memakai helper Laravel `withViewErrors()` untuk mengisi error bag yang dibagikan ke view.
- Pengujian dibuat hanya melakukan satu request, agar tidak bergantung pada perubahan session di antara dua request dalam test yang sama.
- Tidak mengubah Blade, controller, route, database, atau fitur aplikasi.

## Cara memasang

Ekstrak ZIP ini ke folder utama `~/koni-klaten` dan izinkan file test yang sama ditimpa.

Kemudian jalankan:

```bash
cd ~/koni-klaten
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=DemoExperienceTest
```

Jika empat test lulus, lanjutkan:

```bash
docker compose exec laravel.test php artisan test
```

Patch ini hanya mengubah file pengujian. Tidak perlu migration, rebuild Docker, atau `npm run build`.
