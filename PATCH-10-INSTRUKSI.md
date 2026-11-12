# Patch 10 — Finalisasi Website KONI Klaten

Patch ini menambahkan penyempurnaan finalisasi tanpa mengubah struktur tabel database.

## Isi patch

- Memperbaiki breakpoint menu navigasi Public agar menu tetap tersedia pada tablet dan layar laptop kecil.
- Menetapkan zona waktu default `Asia/Jakarta` melalui `APP_TIMEZONE`.
- Menambahkan header keamanan dasar pada respons web.
- Menambahkan sitemap dinamis yang hanya memuat halaman publik, cabor aktif, agenda terbit, berita terbit, dan dokumentasi terbit.
- Memperbarui `robots.txt` agar panel login dan panel internal tidak diarahkan untuk diindeks crawler.
- Menambahkan halaman error yang ramah pengguna untuk HTTP 403, 404, 419, 429, 500, dan 503.
- Memperbarui README dan menambahkan panduan deployment produksi.
- Menambahkan 7 pengujian finalisasi.

## Pemasangan

Ekstrak dari root repository:

```bash
unzip -o koni-klaten-patch-10.zip -d .
```

Jika file `.env` sudah memiliki `APP_TIMEZONE=UTC`, ubah nilainya menjadi:

```dotenv
APP_TIMEZONE=Asia/Jakarta
```

Jika `APP_TIMEZONE` belum ada di `.env`, nilai default aplikasi sudah `Asia/Jakarta`. Jangan menimpa file `.env` dengan `.env.example`.

Kemudian jalankan:

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test
```

Tidak ada migration, tidak perlu `migrate:fresh`, dan tidak perlu build asset karena patch ini tidak mengubah CSS/JS build pipeline.

## Target

84 pengujian sebelumnya + 7 pengujian finalisasi = 91 pengujian. Pengujian lengkap harus dijalankan di Docker proyek untuk memastikan hasil pada environment aktual.

## Catatan deployment

Sebelum produksi, ikuti `docs/DEPLOYMENT.md`. Pastikan domain resmi, HTTPS, `APP_DEBUG=false`, database, storage persisten, backup, dan akun Admin KONI sudah disiapkan. Jangan membuka port database/Redis ke internet.
