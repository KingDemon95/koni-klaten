# PATCH 15 - Role Ketua KONI, Sekretaris KONI, dan Persetujuan Surat Mandiri

Patch ini menambahkan dua role baru dan memperbarui alur persuratan. Patch mempertahankan data serta konfigurasi lokal yang sudah ada.

## Perubahan

1. Admin KONI dapat membuat akun Ketua KONI dan Sekretaris KONI melalui Manajemen Pengguna.
2. Ketua KONI mendapat dashboard sendiri dan dapat mengelola data organisasi melalui modul Admin. Ketua tidak mendapat akses Manajemen Pengguna atau Profil & Pengaturan KONI.
3. Sekretaris KONI mendapat dashboard sendiri, akses baca data cabang olahraga, atlet, pelatih, pengurus, laporan, serta akses mengelola template surat.
4. Sekretaris dapat melihat surat yang sudah diperiksa Staff KONI, lalu menyetujui, menolak, atau meminta revisi.
5. Ketua dan Sekretaris dapat mengambil keputusan secara mandiri. Satu keputusan menutup antrean persetujuan. Pihak lain menerima notifikasi bahwa surat sudah diputuskan dan tidak perlu memberi persetujuan kedua.
6. Staff tetap menyampaikan keputusan kepada Admin Cabor. Riwayat keputusan mencatat pengguna, role, waktu, dan catatan keputusan.
7. Admin KONI tetap memiliki akses penuh sebagai administrator sistem dan tetap dapat memutuskan surat.

## Cara memasang

Jalankan dari terminal WSL Ubuntu pada folder proyek.

1. Buat cadangan source code dan database sebelum memasang patch.
2. Ekstrak `koni-klaten-patch-15.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa.
3. Jangan menimpa `.env`. ZIP patch tidak berisi `.env`, database, `vendor`, atau `node_modules`.
4. Jalankan migrasi dan daftarkan role baru:

```bash
cd ~/koni-klaten

docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan db:seed --class=RoleSeeder --force
docker compose exec laravel.test php artisan optimize:clear
```

5. Bangun ulang asset frontend dari folder utama proyek:

```bash
npm run build
```

6. Jalankan pengujian terarah:

```bash
docker compose exec laravel.test php artisan test --filter=LeadershipRolesTest
docker compose exec laravel.test php artisan test --filter=SuratWorkflowTest
docker compose exec laravel.test php artisan test --filter=AdminKoniSecurityTest
docker compose exec laravel.test php artisan test --filter=SystemIntegrationSecurityTest
```

7. Jika semua pengujian terarah lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

## Pengujian manual

1. Login sebagai Admin KONI, buka Manajemen Pengguna, lalu buat akun Ketua KONI dan Sekretaris KONI. Tetapkan password masing-masing melalui form. Patch tidak membuat password default untuk dua role baru.
2. Login sebagai Ketua. Pastikan dashboard Ketua terbuka, Ketua dapat menambah dan mengubah data organisasi, serta tidak dapat membuka Manajemen Pengguna atau Profil & Pengaturan KONI.
3. Login sebagai Sekretaris. Pastikan Sekretaris dapat melihat data organisasi, mengelola template surat, dan membuka daftar persetujuan surat. Pastikan Sekretaris tidak dapat menambah atau mengubah data master organisasi.
4. Login sebagai Staff. Teruskan satu surat yang sudah diperiksa. Pastikan notifikasi masuk ke Ketua dan Sekretaris.
5. Login sebagai Sekretaris dan setujui atau tolak surat. Pastikan status berubah menjadi menunggu penyampaian hasil dan Ketua mendapat notifikasi bahwa persetujuan kedua tidak diperlukan.
6. Ulangi dengan surat lain. Kali ini Ketua yang memutuskan. Pastikan Sekretaris mendapat notifikasi dan keputusan kedua ditolak oleh sistem.
7. Login kembali sebagai Staff dan sampaikan hasil keputusan kepada Admin Cabor.

## Catatan keamanan

- Jalankan `RoleSeeder` setelah migrasi agar role baru tersedia di database.
- Ketua menggunakan route modul pengelolaan organisasi, tetapi route Manajemen Pengguna dan pengaturan sistem tetap khusus Admin KONI.
- Sekretaris hanya mendapat route baca data organisasi, persuratan, laporan, dan pengelolaan template surat.
- Jangan membagikan file `.env`, `APP_KEY`, atau dokumen surat privat.
