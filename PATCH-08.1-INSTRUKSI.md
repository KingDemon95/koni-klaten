# Patch 08.1 — Koreksi Integrasi Dashboard dan Identitas Public

Patch koreksi untuk Patch 08.

## Perubahan
1. Dashboard Admin KONI tidak lagi menghitung role `atlet_pelatih` yang tidak selalu tersedia. Sistem menggunakan role terpisah `atlet` dan `pelatih`, sesuai role aktif aplikasi.
2. Nama singkat KONI pada header website Public ditampilkan dengan huruf kapital, konsisten dengan identitas default `KONI KLATEN` dan pengujian portal publik.

## Pemasangan
Ekstrak ZIP ini dari folder utama proyek `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-08.1.zip -d .
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test
```

Tidak ada migration baru, tidak ada perubahan database, dan tidak perlu menjalankan `migrate:fresh`.
