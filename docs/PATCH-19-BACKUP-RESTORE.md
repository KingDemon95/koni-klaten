# Patch 19: Backup dan Pemulihan Database KONI Klaten

Patch ini menambahkan backup MySQL melalui Artisan. File backup dan manifest SHA-256 tersimpan di `storage/app/private/backups`, bukan di direktori publik. Patch tidak menyediakan endpoint unduh atau restore melalui browser.

## Prasyarat

- Koneksi aplikasi menggunakan MySQL.
- Container PHP memiliki executable `mysqldump` dan `mysql`.
- User database memiliki hak baca untuk backup dan hak yang diperlukan untuk membuat ulang tabel saat restore.
- Volume `storage` harus persisten dan masuk ke prosedur backup server.

## Perintah operasional

Buat backup sekarang:

```bash
docker compose exec laravel.test php artisan koni:backup
```

Lihat daftar backup dan status integritas:

```bash
docker compose exec laravel.test php artisan koni:backup:list
```

Verifikasi semua backup:

```bash
docker compose exec laravel.test php artisan koni:backup:verify
```

Verifikasi satu file:

```bash
docker compose exec laravel.test php artisan koni:backup:verify koni-db-YYYYMMDD-HHMMSS-xxxxxxxx.sql
```

Pulihkan satu file. Pada environment non-production, Artisan meminta konfirmasi:

```bash
docker compose exec -it laravel.test php artisan koni:backup:restore koni-db-YYYYMMDD-HHMMSS-xxxxxxxx.sql
```

Pada environment production, operator harus menambahkan `--force` secara eksplisit:

```bash
docker compose exec -it laravel.test php artisan koni:backup:restore koni-db-YYYYMMDD-HHMMSS-xxxxxxxx.sql --force
```

**Peringatan restore:** proses ini menjalankan SQL backup pada database aktif yang dikonfigurasi oleh aplikasi. Dump menghapus dan membuat ulang tabel yang ada di dalam dump. Buat backup kondisi database saat ini sebelum restore. Lakukan restore pada maintenance window dan pastikan file berasal dari sumber tepercaya. Restore tidak menghapus tabel tambahan yang tidak ada di dalam file dump.

## Retensi

Default Patch 19 menyimpan backup selama 30 hari. Atur `.env`:

```dotenv
KONI_BACKUP_RETENTION_DAYS=30
```

Untuk menyimpan 7 hari:

```dotenv
KONI_BACKUP_RETENTION_DAYS=7
```

Atau atur retensi saat menjalankan backup:

```bash
docker compose exec laravel.test php artisan koni:backup --retention=7
```

Nilai yang diterima: 1 sampai 3650 hari. Backup kedaluwarsa dan manifest-nya akan dihapus ketika perintah backup berjalan.

## Jadwal otomatis

Patch 19 mendaftarkan:
- Backup database setiap hari pada pukul `02:00` waktu aplikasi.
- Verifikasi seluruh file backup setiap Senin pukul `03:00`.

Ubah waktu backup melalui `.env`:

```dotenv
KONI_BACKUP_SCHEDULE_TIME=02:00
```

Scheduler Laravel harus berjalan setiap menit. Pada server Linux production, tambahkan cron berikut dengan path proyek yang benar:

```cron
* * * * * cd /path/ke/koni-klaten && docker compose exec -T laravel.test php artisan schedule:run >> /dev/null 2>&1
```

Ganti `/path/ke/koni-klaten` dengan direktori proyek pada server. Pastikan cron berjalan menggunakan user yang dapat mengakses Docker dan direktori proyek. Untuk demo di laptop, jadwal hanya berjalan ketika laptop, Docker, dan scheduler aktif.

## Penyimpanan dan keamanan

- File SQL dan manifest memakai permission `0600`; direktori backup memakai permission `0700`.
- Kredensial MySQL hanya ditulis sementara ke file konfigurasi dengan permission `0600`, lalu dihapus setelah proses selesai.
- Nama file backup divalidasi ketat. Restore menolak path traversal, symlink, manifest yang tidak sesuai, dan checksum yang berubah. SHA-256 mendeteksi kerusakan atau perubahan tidak disengaja; manifest bukan tanda tangan digital jika pihak yang sama dapat mengubah SQL dan manifest.
- Backup mencakup data sensitif. Jangan kirim file SQL melalui chat, email, atau tautan publik.
- Backup lokal pada server yang sama bukan perlindungan jika disk atau VPS hilang. Untuk produksi, salin backup ke lokasi off-site yang terpisah dan terlindungi. Patch ini belum mengirim backup ke cloud storage.
- Audit Trail mencatat backup berhasil, gagal, dipulihkan, dan file kedaluwarsa yang terhapus. Audit event sistem memakai pelaku `System Backup`.

Log jadwal disimpan pada `storage/logs/koni-backup.log`. Pesan error umum juga masuk ke `storage/logs/laravel.log`.

## Pemeriksaan setelah pemasangan

```bash
docker compose exec laravel.test php artisan koni:backup
docker compose exec laravel.test php artisan koni:backup:list
docker compose exec laravel.test php artisan koni:backup:verify
docker compose exec laravel.test php artisan test --filter=DatabaseBackupTest
docker compose exec laravel.test php artisan test
```

Jangan melakukan uji restore pada database demo/produksi aktif tanpa membuat backup kondisi terkini dan memastikan operator memahami bahwa restore menimpa tabel yang tercakup dalam dump.
