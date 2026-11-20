# PATCH 20 — Backup Off-site dan Monitoring Sistem

Patch 20 dibangun berdasarkan source KONI Klaten yang diunggah pengguna dan melanjutkan modul backup Patch 19.

## Isi patch

- Sinkronisasi file backup SQL dan manifest SHA-256 ke disk remote privat.
- Verifikasi hash salinan remote setelah upload.
- Kegagalan sinkronisasi tidak menghapus backup lokal.
- Sinkronisasi terjadwal pukul 02.15 WIB.
- Heartbeat scheduler setiap menit.
- Perintah `koni:system-health`.
- Halaman monitoring `/admin/kesehatan-sistem`, khusus Admin KONI.
- Audit Trail untuk sinkronisasi off-site.
- 9 pengujian fitur dan keamanan.

## Pemasangan

Dari WSL Ubuntu:

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-20.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Tidak ada migration database dan tidak ada npm build.

## Pemeriksaan awal

```bash
docker compose exec laravel.test php artisan list --raw | grep -E '^koni:(backup:sync|scheduler-heartbeat|system-health)$'
docker compose exec laravel.test php artisan schedule:list
docker compose exec laravel.test php artisan koni:system-health
```

## Aktifkan off-site (opsional)

Disk `s3` sudah tersedia di konfigurasi proyek, tetapi adapter S3 belum termasuk dalam dependency saat source diunggah. Setelah memilih provider S3-compatible, jalankan:

```bash
docker compose exec laravel.test composer require league/flysystem-aws-s3-v3:^3.0
```

Isi `.env` berdasarkan dokumentasi provider, kemudian set:

```dotenv
KONI_BACKUP_REMOTE_DISK=s3
KONI_BACKUP_REMOTE_PREFIX=koni-klaten/backups
```

Untuk Cloudflare R2, set `AWS_ENDPOINT` ke endpoint akun R2, `AWS_DEFAULT_REGION=auto`, `AWS_BUCKET` ke nama bucket, dan gunakan access key dengan izin minimum pada prefix backup. Untuk AWS S3, gunakan region bucket dan kosongkan endpoint. Jangan kirim kredensial kepada pihak lain atau memasukkannya ke ZIP.

Lalu jalankan:

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan koni:backup:sync
docker compose exec laravel.test php artisan koni:system-health
```

Jika `KONI_BACKUP_REMOTE_DISK` kosong, fitur off-site tetap nonaktif dan backup lokal Patch 19 tetap berfungsi.

## Scheduler demo WSL

Jalankan di terminal WSL terpisah dan biarkan aktif:

```bash
docker compose exec laravel.test php artisan schedule:work
```

Setelah satu sampai dua menit, periksa heartbeat:

```bash
docker compose exec laravel.test php artisan koni:system-health
```

Untuk VPS, gunakan cron setiap menit atau proses supervisor persisten. `schedule:work` pada terminal SSH bukan konfigurasi produksi yang andal.

## Pengujian

```bash
docker compose exec laravel.test php artisan test --filter=SystemHealthTest
docker compose exec laravel.test php artisan test
```

Target: 9 test baru dan total 171 test berdasarkan baseline 162 dari Patch 19. Jalankan PHPUnit di lingkungan Docker proyek untuk membuktikan hasil.

## Batasan

- Retensi lokal tetap dikelola Patch 19.
- Atur lifecycle policy pada provider untuk menghapus backup remote yang sudah kedaluwarsa.
- Restore tetap melalui CLI Patch 19, bukan melalui web.
- Uji restore pada database staging terpisah secara berkala.
