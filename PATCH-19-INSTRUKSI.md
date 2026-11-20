# PATCH 19 — Backup dan Pemulihan Database

Patch 19 menambahkan backup MySQL privat, verifikasi SHA-256, retensi file, restore berbasis Artisan, jadwal Laravel Scheduler, dan pencatatan aktivitas pada Audit Trail.

## Isi patch

- `app/Support/Backups/DatabaseBackupManager.php`
- Empat Artisan command: backup, list, verify, restore
- `config/koni-backups.php`
- Jadwal backup harian dan verifikasi mingguan pada `routes/console.php`
- Label event backup pada Audit Trail
- Konfigurasi retensi pada `.env.example`
- Pengujian fitur backup dan restore
- Panduan operasional pada `docs/PATCH-19-BACKUP-RESTORE.md`

## Pemasangan

1. Buat backup database manual terlebih dahulu jika aplikasi sudah memiliki data penting.
2. Ekstrak `koni-klaten-patch-19.zip` ke root proyek `~/koni-klaten`, izinkan file yang sama ditimpa. Jangan menimpa `.env`.
3. Bersihkan cache:

```bash
docker compose exec laravel.test php artisan optimize:clear
```

4. Pastikan MySQL client tersedia:

```bash
docker compose exec laravel.test sh -lc 'command -v mysqldump && command -v mysql'
```

Jika salah satu binary tidak ditemukan, jangan jalankan backup/restore sebelum image PHP dilengkapi MySQL client.

5. Periksa command terdaftar:

```bash
docker compose exec laravel.test php artisan list --raw | grep '^koni:backup'
```

6. Jalankan pengujian patch:

```bash
docker compose exec laravel.test php artisan test --filter=DatabaseBackupTest
```

7. Jalankan pengujian regresi:

```bash
docker compose exec laravel.test php artisan test --filter=AuditTrailTest
docker compose exec laravel.test php artisan test --filter=AuditTrailSecurityTest
docker compose exec laravel.test php artisan test
```

Tidak ada migration database dan tidak ada perubahan frontend. Karena itu, Patch 19 tidak memerlukan `php artisan migrate` atau `npm run build`.

## Uji backup setelah patch terpasang

```bash
docker compose exec laravel.test php artisan koni:backup
docker compose exec laravel.test php artisan koni:backup:list
docker compose exec laravel.test php artisan koni:backup:verify
```

Pastikan file SQL dan file `.json` muncul di `storage/app/private/backups`. Jangan mengunggah atau membagikan file backup karena isinya dapat mencakup seluruh data organisasi.

## Catatan penting

- Backup default berjalan setiap hari pukul 02:00 Asia/Jakarta. Verifikasi terjadwal berjalan setiap Senin pukul 03:00.
- Jadwal hanya aktif jika Laravel Scheduler berjalan setiap menit. Lihat `docs/PATCH-19-BACKUP-RESTORE.md` untuk cron server.
- Restore menimpa tabel yang tercakup dalam dump pada database aktif. Buat backup kondisi terkini sebelum restore.
- Pada production, restore wajib menyertakan `--force`.
- Backup disimpan di server yang sama. Untuk perlindungan bencana, pengelola server tetap perlu menyalin backup ke penyimpanan off-site terpisah.
- SHA-256 mendeteksi kerusakan atau perubahan file yang tidak disengaja. Manifest bukan tanda tangan digital, sehingga backup dan manifest harus disimpan pada lokasi yang sama-sama terlindungi.
