# PATCH 17 - Audit Trail dan Riwayat Aktivitas

Patch ini menambahkan pencatatan aktivitas penting pada Portal KONI Kabupaten Klaten. Catatan baru mulai terbentuk setelah Patch 17 dipasang. Sistem tidak membuat riwayat retroaktif untuk perubahan yang terjadi sebelum pemasangan.

## Fitur

- Mencatat pembuatan, perubahan, penghapusan, dan pemulihan data pada modul organisasi, atlet, pelatih, prestasi, agenda, persuratan, template surat, publikasi, izin latihan, profil KONI, akun pengguna, dan inventaris.
- Mengklasifikasikan perubahan status sebagai persetujuan, penolakan, publikasi, pengajuan, permintaan revisi, atau penerusan proses.
- Mencatat perubahan role akun, login, dan logout.
- Menyimpan identitas pelaku, role saat tindakan berlangsung, waktu, alamat IP, user agent, route, dan ringkasan perubahan.
- Menyaring kata sandi, token, email, nomor kontak, alamat, tanggal lahir, jalur file, dan sejumlah data pribadi dari snapshot perubahan. Pola email dan nomor telepon dalam teks juga disamarkan.
- Menyediakan filter kata kunci, jenis aktivitas, dan rentang tanggal.
- Menjadikan catatan audit baca saja pada antarmuka dan model aplikasi.

## Hak akses

- Admin KONI dan Ketua KONI dapat membaca seluruh riwayat.
- Sekretaris KONI dapat membaca seluruh riwayat tanpa endpoint perubahan.
- Admin Cabor hanya dapat membaca aktivitas yang terkait dengan cabang olahraganya sendiri.
- Staff KONI hanya dapat membaca aktivitas yang dilakukan oleh akunnya sendiri.
- Atlet dan Pelatih tidak dapat membuka modul riwayat aktivitas.

## File dan database

Patch menambahkan tabel `audit_logs` melalui satu migration baru. Patch juga memperbarui observer model, provider, route, navigasi, controller, tampilan, serta pengujian. Patch tidak mengubah `.env`, dependency Composer, atau dependency NPM.

## Cara memasang

1. Buat backup database sebelum memasang patch:

```bash
docker compose exec mysql sh -lc 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup-koni-before-patch17.sql
```

2. Ekstrak `koni-klaten-patch-17.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa. Jangan menimpa `.env`.

3. Jalankan migration dan bersihkan cache:

```bash
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan optimize:clear
```

4. Build frontend agar kelas Tailwind pada navigasi dan halaman baru ikut masuk ke asset produksi:

```bash
docker compose exec laravel.test sh -lc 'cd /var/www/html && npm run build'
```

5. Jalankan pengujian Patch 17:

```bash
docker compose exec laravel.test php artisan test --filter=AuditTrailTest
docker compose exec laravel.test php artisan test --filter=AuditTrailSecurityTest
docker compose exec laravel.test php artisan test --filter=DatabaseMigrationTest
```

6. Jika seluruh pengujian terkait lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

## Verifikasi manual

- Login sebagai Admin KONI atau Ketua KONI, lalu buka menu **Riwayat Aktivitas**. Pastikan keduanya melihat catatan global.
- Login sebagai Sekretaris KONI. Pastikan menu riwayat hanya menyediakan tampilan baca saja.
- Login sebagai Admin Cabor. Pastikan catatan hanya berasal dari cabang olahraga yang terhubung ke akunnya.
- Login sebagai Staff KONI. Pastikan halaman hanya menampilkan aktivitas akun Staff yang sedang digunakan.
- Pastikan Atlet dan Pelatih menerima akses terlarang ketika membuka URL riwayat.
- Buat atau ubah data uji, lalu periksa aktor, waktu, modul, jenis tindakan, dan detail perubahan.

## Catatan

- Riwayat sebelum Patch 17 tidak tersedia karena aplikasi belum merekamnya.
- Catatan audit tidak memiliki halaman edit atau hapus.
- Patch tidak membutuhkan seeder tambahan.
- Pengujian Laravel harus dijalankan pada Docker/WSL proyek pengguna. Pemeriksaan pada paket ini mencakup sintaks PHP dan integritas arsip.
