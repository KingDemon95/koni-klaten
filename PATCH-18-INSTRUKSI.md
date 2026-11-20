# PATCH 18 - Keamanan Akun dan Penggantian Password Awal

Patch ini memperkuat keamanan akun KONI, terutama akun yang dibuat otomatis dengan password awal bersama berdasarkan role.

## Perubahan

1. Menambahkan status `must_change_password` dan waktu `password_changed_at` pada tabel pengguna.
2. Menandai akun lama Admin Cabor, Atlet, Pelatih, dan Staff KONI agar wajib mengganti password pada login berikutnya.
3. Semua akun baru yang dibuat otomatis oleh sistem wajib mengganti password awal.
4. Semua akun baru yang dibuat melalui Manajemen Pengguna wajib mengganti password yang diberikan Admin saat login pertama.
5. Jika Admin mengganti password akun melalui Manajemen Pengguna, sistem kembali mewajibkan pemilik akun mengganti password tersebut.
6. Menambahkan halaman Keamanan Akun yang dapat digunakan semua role untuk mengganti password sendiri.
7. Pengguna wajib memasukkan password saat ini. Password baru minimal 10 karakter dan harus memuat huruf besar, huruf kecil, serta angka.
8. Pengguna yang belum mengganti password tidak dapat membuka dashboard atau modul lain. Pengguna tetap dapat membuka halaman penggantian password dan logout.
9. Perubahan password tercatat pada Audit Trail. Sistem tidak menyimpan password baru ke dalam riwayat aktivitas.
10. Menambahkan menu Keamanan Akun pada navigasi desktop dan mobile untuk setiap role.

## Cara memasang

Jalankan dari terminal WSL Ubuntu pada folder proyek.

1. Buat backup database:

```bash
docker compose exec mysql sh -lc 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup-koni-before-patch18.sql
```

2. Ekstrak `koni-klaten-patch-18.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa. Jangan menimpa `.env`.

3. Jalankan migration dan bersihkan cache:

```bash
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan optimize:clear
```

4. Build asset frontend dari dalam container:

```bash
docker compose exec laravel.test sh -lc 'cd /var/www/html && npm run build'
```

5. Jalankan pengujian terarah:

```bash
docker compose exec laravel.test php artisan test --filter=PasswordSecurityTest
docker compose exec laravel.test php artisan test --filter=DatabaseMigrationTest
docker compose exec laravel.test php artisan test --filter=WorkflowAndAutomaticAccountsTest
docker compose exec laravel.test php artisan test --filter=PenggunaControllerTest
```

6. Jika semua pengujian terarah lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

## Pengujian manual

1. Login dengan akun Atlet, Pelatih, Admin Cabor, atau Staff yang sebelumnya menggunakan password awal bersama. Sistem harus langsung membuka halaman Keamanan Akun.
2. Coba membuka dashboard atau URL modul lain sebelum mengganti password. Sistem harus mengarahkan pengguna kembali ke halaman Keamanan Akun.
3. Masukkan password saat ini dan password baru yang memenuhi kebijakan. Setelah berhasil, sistem mengarahkan pengguna ke dashboard sesuai role.
4. Buka menu Keamanan Akun setelah login normal. Pastikan pengguna tetap dapat mengganti password secara mandiri.
5. Login sebagai Admin KONI dan buat akun pengguna baru. Pastikan daftar pengguna menampilkan status Wajib ganti password.
6. Edit akun pengguna dan isi password baru. Pastikan pemilik akun harus menggantinya lagi saat login berikutnya.
7. Periksa Riwayat Aktivitas. Pastikan perubahan password tercatat sebagai `password_changed` dan tidak menampilkan password asli.
8. Pastikan setiap role dapat membuka halaman Keamanan Akun miliknya sendiri dan tidak dapat mengganti password akun lain.

## Catatan

- Migrasi menandai akun lama dengan role Admin Cabor, Atlet, Pelatih, dan Staff KONI agar mengganti password. Akun Ketua, Sekretaris, dan Admin KONI yang sudah ada tidak dipaksa mengganti password oleh migrasi ini.
- Password awal bersama tetap digunakan hanya sebagai kredensial sementara sesuai konfigurasi `config/account_defaults.php` dan variabel `.env` yang sudah ada.
- Password baru harus minimal 10 karakter, memuat huruf besar, huruf kecil, dan angka.
- Patch tidak mengubah password akun secara langsung. Pengguna tetap memasukkan password awal yang saat ini berlaku untuk menggantinya.
- Jangan membagikan `.env`, `APP_KEY`, atau password pengguna.
