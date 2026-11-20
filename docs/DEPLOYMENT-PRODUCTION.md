# Deployment Produksi KONI Klaten

Patch 21 menambahkan konfigurasi produksi terpisah. Konfigurasi lokal `compose.yaml` dan Laravel Sail tidak berubah. Jangan menjalankan stack produksi pada laptop demo sebelum domain, VPS, dan environment produksi siap.

## Arsitektur

`Caddy (80/443, HTTPS otomatis) -> Nginx (internal) -> PHP-FPM/Laravel`

Komponen aplikasi, MySQL, dan Redis berada pada bridge backend tanpa port host yang dipublikasikan. Aplikasi tetap dapat membuat koneksi keluar yang diperlukan untuk SMTP atau backup S3-compatible. Jaringan publik hanya masuk melalui Caddy.

Komponen backend:
- MySQL 8.4 tanpa port publik.
- Redis 7 dengan password dan tanpa port publik.
- Scheduler Laravel yang berjalan sebagai service persisten.
- Queue worker Redis yang berjalan sebagai service persisten.
- Volume persisten terpisah untuk MySQL, Redis, storage aplikasi, dan sertifikat Caddy.

PHP-FPM dan Nginx tidak menerbitkan port ke host. Hanya Caddy yang membuka port 80 dan 443.

## Persyaratan sebelum menjalankan

1. VPS Linux dengan Docker Engine dan Docker Compose plugin.
2. Domain yang sudah memiliki DNS A/AAAA menuju IP VPS.
3. Port TCP 80 dan 443 serta UDP 443 dapat diakses dari internet.
4. Email operasional untuk penerbitan sertifikat TLS.
5. Password database, root database, dan Redis yang unik.
6. Backup terbaru dari Patch 19/20 dan salinan `storage` yang aman.

Belum memiliki domain atau VPS? Jangan menjalankan stack produksi. Konfigurasi ini dapat disimpan sampai infrastruktur siap.

## 1. Siapkan environment produksi

Di root proyek:

```bash
cp .env.production.example .env.production
chmod 600 .env.production
nano .env.production
```

Ubah `APP_DOMAIN`, `ACME_EMAIL`, `APP_URL`, `APP_KEY`, `DB_PASSWORD`, `DB_ROOT_PASSWORD`, dan `REDIS_PASSWORD`. Ganti juga email pengirim jika SMTP sudah tersedia. Jangan memakai password demo dari `.env.example`.

Buat tiga password unik:

```bash
openssl rand -base64 36
openssl rand -base64 36
openssl rand -base64 36
```

Gunakan hasil berbeda untuk `DB_PASSWORD`, `DB_ROOT_PASSWORD`, dan `REDIS_PASSWORD`. Hindari karakter newline. Simpan password di password manager.

`APP_KEY` harus dihasilkan satu kali untuk instalasi produksi. Setelah image berhasil dibangun, jalankan:

```bash
docker compose --env-file .env.production -f compose.production.yaml run --rm --no-deps app php artisan key:generate --show
```

Salin hasil `base64:...` ke `APP_KEY` di `.env.production`. Jangan menjalankan `key:generate` lagi pada aplikasi yang sudah memiliki data terenkripsi. Jangan pernah membagikan APP_KEY.

Pastikan `APP_URL=https://APP_DOMAIN` menggunakan domain yang sama persis dengan `APP_DOMAIN`.

## 2. Validasi dan build image

```bash
docker compose --env-file .env.production -f compose.production.yaml config --quiet
docker compose --env-file .env.production -f compose.production.yaml build
```

Build PHP menggunakan PHP 8.5 FPM, ekstensi PDO MySQL, Redis, OPcache, GD, Intl, ZIP, serta MySQL 8.4 client. MySQL client penting karena perintah backup Patch 19 menggunakan opsi `mysqldump` khusus MySQL. Frontend dibangun pada tahap Node terpisah.

## 3. Jalankan layanan

Pastikan DNS domain sudah mengarah ke VPS dan port 80/443 terbuka. Caddy akan meminta sertifikat TLS otomatis.

```bash
docker compose --env-file .env.production -f compose.production.yaml up -d
docker compose --env-file .env.production -f compose.production.yaml ps
```

Hanya service Caddy yang menerbitkan port publik. Jangan menambahkan port MySQL, Redis, PHP-FPM, atau Nginx ke host.

## 4. Backup, migrasi, dan pemeriksaan

Sebelum migrasi pertama atau setiap rilis:

```bash
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan koni:backup
```

Lalu jalankan migrasi secara eksplisit:

```bash
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan migrate --force
```

Jangan menjalankan `db:seed` pada database yang sudah berisi data organisasi. Seeder hanya menyiapkan role aplikasi pada instalasi baru dan harus ditinjau terlebih dahulu.

Periksa kesiapan:

```bash
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan koni:production-check
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan koni:system-health
docker compose --env-file .env.production -f compose.production.yaml exec app php artisan about
```

Target `koni:production-check` adalah seluruh pemeriksaan `[OK]`. Pemeriksaan memvalidasi APP_ENV, APP_DEBUG, APP_KEY, HTTPS, cookie aman, MySQL, Redis, Vite manifest, storage, queue, cache, session, dan logging. Pemeriksaan ini tidak menampilkan nilai rahasia.

## 5. Operasional

```bash
docker compose --env-file .env.production -f compose.production.yaml logs -f caddy nginx app scheduler queue
```

Scheduler dan queue worker berjalan sebagai service Docker terpisah dan otomatis restart. Jangan menjalankan `schedule:work` manual bersamaan dengan service scheduler produksi.

Untuk restart aplikasi setelah mengubah `.env.production`:

```bash
docker compose --env-file .env.production -f compose.production.yaml up -d --force-recreate app scheduler queue
```

Jika mengubah konfigurasi Caddy atau domain, recreate service Caddy juga. Jika mengubah kode atau dependency, naikkan `APP_IMAGE_TAG`, build image baru, lalu deploy.

## 6. Pembaruan dan rollback

Urutan pembaruan:
1. Ambil backup database dan pastikan file backup valid.
2. Simpan image/tag aplikasi sebelumnya.
3. Build image dengan `APP_IMAGE_TAG` baru.
4. Jalankan container baru.
5. Jalankan `php artisan migrate --force` hanya jika rilis memiliki migration.
6. Jalankan `koni:production-check`, `koni:system-health`, dan smoke test browser.
7. Pantau log dan proses worker.

Rollback kode menggunakan tag image sebelumnya. Perubahan database tidak otomatis ikut mundur. Jangan menjalankan migration rollback pada data produksi tanpa rencana kompatibilitas dan backup yang sudah diuji. Jika perlu memulihkan database, ikuti prosedur restore Patch 19 pada maintenance window setelah membuat backup kondisi terkini.

Jangan menghapus volume `mysql_data`, `koni_storage`, `redis_data`, `caddy_data`, atau `caddy_config` saat deploy. Perintah `docker compose down -v` dapat menghapus data persisten dan tidak boleh dipakai sebagai langkah deployment normal.

## 7. Catatan batasan dan keamanan

- Patch 21 tidak mengubah `compose.yaml` lokal, `.env`, database, akun, atau data organisasi.
- `.env.production` tidak masuk ke image karena `.dockerignore` mengecualikan file `.env*`.
- Folder backup privat dan upload pengguna tidak dibundel ke image.
- Backup lokal tetap memerlukan konfigurasi off-site Patch 20. Konfigurasi S3-compatible belum aktif otomatis.
- `MAIL_MAILER=log` pada contoh hanya untuk memulai tanpa SMTP. Atur SMTP resmi sebelum mengandalkan email produksi.
- Ukuran upload Nginx/PHP default Patch 21 adalah 20 MB. Sesuaikan setelah mengevaluasi kebutuhan dan kapasitas server.
- Konfigurasi menggunakan `php artisan config:cache` dan `view:cache`. Jangan menjalankan `route:cache` pada source saat ini karena route dashboard masih memuat closure.
- Uji restore pada database staging terpisah sebelum menyatakan prosedur pemulihan siap produksi.
- Gunakan firewall VPS untuk membatasi inbound hanya ke SSH terkelola, HTTP, dan HTTPS. Jangan membuka MySQL atau Redis ke internet.

## Referensi MySQL client

Image memasang MySQL Community Client 8.4 dari repositori resmi MySQL untuk menjaga kompatibilitas dengan opsi backup Patch 19. Panduan repositori APT resmi: https://dev.mysql.com/doc/refman/8.4/en/linux-installation-apt-repo.html
