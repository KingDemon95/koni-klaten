# Patch 20: Backup Off-site dan Monitoring Sistem

Patch ini melanjutkan backup lokal Patch 19. Patch 20 menambahkan sinkronisasi ke disk remote yang kompatibel dengan Laravel Filesystem, pemeriksaan checksum salinan remote, heartbeat scheduler, perintah pemeriksaan kesehatan, dan halaman monitoring yang hanya dapat dibuka Admin KONI.

## Perubahan

- `koni:backup:sync [backup]` mengirim file SQL dan manifest SHA-256 ke disk remote privat.
- Sinkronisasi memverifikasi checksum file remote sebelum menyatakan berhasil.
- Kegagalan remote tidak menghapus backup lokal.
- Jadwal sinkronisasi berjalan setiap hari pukul 02.15 WIB, setelah backup lokal pukul 02.00.
- `koni:scheduler-heartbeat` berjalan setiap menit agar dashboard dapat mendeteksi scheduler yang berhenti.
- `koni:system-health` memeriksa database, Redis, direktori backup lokal, usia dan integritas backup terakhir, status off-site, serta heartbeat scheduler.
- Halaman `/admin/kesehatan-sistem` hanya tersedia untuk Admin KONI.
- Aktivitas sinkronisasi remote masuk Audit Trail tanpa mencatat kredensial.

## Instalasi

Ekstrak ZIP ke root proyek `~/koni-klaten`, lalu jalankan:

```bash
docker compose exec laravel.test php artisan optimize:clear
```

Tidak ada migration database dan tidak ada perubahan frontend build.

## Aktifkan penyimpanan off-site

Patch mendukung disk S3-compatible melalui Laravel Filesystem. Source proyek saat ini sudah memiliki konfigurasi disk `s3`, tetapi adapter Flysystem S3 merupakan dependency opsional dan belum ada di `composer.lock`.

Jika memilih AWS S3, Cloudflare R2, Backblaze B2 S3 API, atau layanan S3-compatible lain, pasang adapter terlebih dahulu:

```bash
docker compose exec laravel.test composer require league/flysystem-aws-s3-v3:^3.0
```

Kemudian isi `.env` sesuai provider. Contoh struktur untuk Cloudflare R2:

```dotenv
KONI_BACKUP_REMOTE_DISK=s3
KONI_BACKUP_REMOTE_PREFIX=koni-klaten/backups
AWS_ACCESS_KEY_ID=ISI_ACCESS_KEY
AWS_SECRET_ACCESS_KEY=ISI_SECRET_KEY
AWS_DEFAULT_REGION=auto
AWS_BUCKET=NAMA_BUCKET
AWS_ENDPOINT=https://ACCOUNT_ID.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=false
```

Untuk AWS S3, gunakan region bucket yang sebenarnya dan kosongkan `AWS_ENDPOINT`. Jangan menaruh kredensial pada source code, ZIP patch, screenshot, atau percakapan. Gunakan bucket privat dan access key dengan izin minimum untuk membaca, menulis, memeriksa, dan menghapus objek pada prefix backup. Atur lifecycle policy di provider agar salinan remote mengikuti retensi yang diinginkan; Patch 20 hanya menjalankan retensi lokal Patch 19.

Setelah konfigurasi:

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan koni:backup:sync
docker compose exec laravel.test php artisan koni:system-health
```

Jika `KONI_BACKUP_REMOTE_DISK` kosong, sinkronisasi off-site tetap nonaktif dan backup lokal tetap berjalan normal.

## Monitoring dan scheduler

Untuk demo di WSL, jalankan pada terminal terpisah dan biarkan aktif:

```bash
docker compose exec laravel.test php artisan schedule:work
```

Tunggu setidaknya satu menit, lalu jalankan:

```bash
docker compose exec laravel.test php artisan koni:system-health
```

Halaman monitoring tersedia pada menu Admin KONI: **Kesehatan Sistem**. Ketua, Sekretaris, Admin Cabor, Staff, Atlet, dan Pelatih tidak memiliki akses ke halaman tersebut karena memuat status infrastruktur.

Untuk VPS, jalankan Laravel Scheduler melalui cron setiap menit atau service supervisor yang persisten. Jangan mengandalkan terminal SSH interaktif sebagai proses scheduler produksi.

## Pengujian

```bash
docker compose exec laravel.test php artisan test --filter=SystemHealthTest
docker compose exec laravel.test php artisan test
```

Target pengujian Patch 20: 9 pengujian baru, sehingga total target setelah baseline 162 adalah 171 test passed. Target ini harus dibuktikan di lingkungan Docker proyek.

## Catatan keamanan

- File SQL dan manifest remote menggunakan visibility `private`.
- Patch tidak menampilkan APP_KEY, password database, access key, secret key, atau endpoint credential di halaman monitoring.
- Sinkronisasi hanya mengunggah backup yang sudah lolos verifikasi lokal.
- Restore tetap melalui mekanisme Patch 19 dan tidak tersedia melalui halaman web.
- Backup off-site tidak menggantikan backup lokal.
- Untuk pemulihan bencana, lakukan uji restore berkala pada database staging terpisah.
