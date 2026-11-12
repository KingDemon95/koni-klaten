# Panduan Deployment Produksi — KONI Klaten

Dokumen ini adalah checklist operasional. Nilai domain, kredensial, dan penyedia hosting harus diisi oleh pengelola server; jangan menaruh rahasia produksi di repository.

## 1. Persiapan server

- Gunakan server Linux yang didukung dan versi PHP sesuai `composer.json` (PHP 8.3 atau lebih baru; image Docker proyek menggunakan PHP 8.5).
- Pasang Docker/Compose atau stack PHP-FPM + web server yang dikelola.
- Siapkan domain resmi dan DNS menuju server.
- Pasang reverse proxy/web server dengan HTTPS aktif.
- Pastikan hanya port web yang dapat diakses publik. Jangan membuka port MySQL `3306` atau Redis `6379` ke internet.
- Siapkan volume persisten untuk database dan `storage`.

## 2. Konfigurasi environment

Buat `.env` di server dari `.env.example`, lalu isi nilai produksi. Jangan commit `.env`.

Minimal pastikan:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://DOMAIN-RESMI-KONI
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=NAMA_DATABASE
DB_USERNAME=USER_DATABASE
DB_PASSWORD=PASSWORD_DATABASE_YANG_KUAT

SESSION_SECURE_COOKIE=true
```

Gunakan `APP_KEY` unik dan rahasia. Jika aplikasi sudah memiliki data terenkripsi, jangan mengganti `APP_KEY` tanpa rencana migrasi kunci. Sesuaikan `DB_HOST` dengan topologi server; `mysql` hanya tepat jika service Compose memang bernama `mysql` dan berada pada network yang sama.

## 3. Instalasi dan build

Dari root repository, jalankan perintah sesuai cara deployment yang dipilih. Untuk deployment berbasis container Sail/Compose:

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
docker compose up -d --build
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan db:seed --force
docker compose exec laravel.test php artisan storage:link
docker compose exec laravel.test php artisan optimize
```

Jalankan seeder hanya setelah memastikan isi `DatabaseSeeder` aman untuk database produksi. Seeder saat ini menyiapkan role aplikasi; tetap tinjau hasilnya sebelum menjalankan pada database yang sudah berisi data.

## 4. Storage dan permission

- Pastikan `storage/` dan `bootstrap/cache/` dapat ditulis oleh user proses PHP.
- `storage/app/public` digunakan untuk aset publik seperti logo organisasi; symlink `public/storage` harus tersedia.
- Dokumen surat, bukti prestasi, izin latihan, dan media publikasi yang belum terbit disimpan pada disk privat. Jangan membuat symlink publik ke `storage/app/private`.
- Pastikan volume `storage` persisten saat container diperbarui.

## 5. Pemeriksaan setelah deploy

```bash
docker compose ps
docker compose exec laravel.test php artisan about
docker compose exec laravel.test php artisan route:list
docker compose exec laravel.test php artisan test
```

Periksa melalui browser:

- Halaman publik dan menu pada ponsel, tablet, dan desktop.
- Login serta dashboard kelima role yang dipakai organisasi.
- Pembatasan data Admin Cabor per cabang.
- Alur surat: Admin Cabor → Staff KONI → Admin KONI → Staff KONI → Admin Cabor.
- Prestasi baru belum tampil sebelum diverifikasi Admin Cabor.
- Draft publikasi tidak dapat dibuka oleh pengunjung.
- Upload dan unduh file privat sesuai hak akses.
- `/up` memberikan status sehat dan `/sitemap.xml` berisi hanya konten publik.

## 6. Operasional dan backup

- Aktifkan backup database terjadwal dan backup `storage` secara terpisah.
- Tabel `notifications` menyimpan pemberitahuan dalam aplikasi; pastikan tabel ini termasuk dalam backup database dan atur kebijakan retensi sesuai kebutuhan organisasi.
- Uji proses restore backup, bukan hanya membuat file backup.
- Pantau `storage/logs/laravel.log`, status container, ruang disk, dan masa berlaku SSL.
- Siapkan prosedur pembaruan: backup → deploy kode → migrate → optimize → smoke test.
- Jangan menghapus volume database saat deploy. Hindari `migrate:fresh` pada produksi.
- Buat akun awal dengan password unik, lalu serahkan kredensial melalui kanal aman. Jangan menaruh password demo di README atau repository.

## 7. Hal yang harus diputuskan klien/pengelola

- Domain dan hosting resmi.
- Alamat email resmi dan konfigurasi SMTP untuk notifikasi (jika dibutuhkan).
- Kebijakan retensi dokumen dan backup.
- Penanggung jawab reset password dan pembuatan akun.
- Konten resmi profil, logo, visi-misi, kontak, dan foto publikasi.
- Akun Admin KONI awal serta proses serah terima kredensial.
