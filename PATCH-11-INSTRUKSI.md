# PATCH 11 — Workflow Prestasi, Persetujuan Izin, dan Akun Otomatis

Patch ini dibuat untuk source KONI Klaten yang dikirim pada 11 November 2026 (Laravel 13).

## Isi patch

1. **Verifikasi prestasi**
   - Pesan validasi ditampilkan pada halaman Admin Cabor ketika catatan revisi/penolakan belum diisi.
   - Alasan penolakan ditampilkan lebih jelas di portal Atlet dan Pelatih.
   - Pesan hasil review tidak lagi mengasumsikan pemilik prestasi selalu Atlet.

2. **Persetujuan izin latihan**
   - Admin Cabor dapat menyetujui atau menolak permohonan dari cabang olahraganya sendiri.
   - Penolakan wajib disertai alasan.
   - Keputusan, catatan, petugas pemroses, dan waktu pemrosesan disimpan.
   - Atlet/Pelatih melihat status dan catatan keputusan di portal masing-masing.
   - Admin Cabor tetap tidak dapat memproses permohonan dari cabang lain.

3. **Pembuatan akun otomatis**
   - Saat Cabor ditambahkan, akun Admin Cabor dibuat dan dihubungkan ke cabor tersebut.
   - Saat Atlet ditambahkan oleh Admin KONI maupun Admin Cabor, akun Atlet dibuat dan ditautkan ke profil.
   - Saat Pelatih ditambahkan oleh Admin KONI maupun Admin Cabor, akun Pelatih dibuat dan ditautkan ke profil.
   - Saat pengurus tingkat KONI ditambahkan, Admin dapat mencentang opsi pembuatan akun Staff KONI. Pengurus tingkat Cabor tidak dapat memakai opsi tersebut.
   - Email harus unik karena digunakan sebagai alamat login.
   - Jika profil lama belum memiliki akun, menyimpan perubahan profil dengan email yang valid akan membuat akun untuk profil Atlet/Pelatih. Untuk Cabor lama, buka edit Cabor dan lengkapi data akun Admin Cabor.

## Password awal DEMO

Password bersama per role diambil dari `config/account_defaults.php` dan dapat diubah melalui `.env`.

| Role | Password awal default |
|---|---|
| Admin Cabor | `CaborDemo123!` |
| Atlet | `12345678` |
| Pelatih | `87654321` |
| Staff KONI | `StaffDemo123!` |

**PENTING:** Password bersama hanya untuk demo dengan data fiktif. Jangan gunakan password tersebut untuk produksi atau akun yang berisi data pribadi. Sebelum produksi, ganti nilai `DEFAULT_PASSWORD_*` dengan kebijakan password yang aman (idealnya password acak unik per akun dan wajib diganti pada login pertama).

## Cara memasang

### 1. Backup database terlebih dahulu

Dari folder `~/koni-klaten`:

```bash
cd ~/koni-klaten

docker compose exec -T mysql sh -lc 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup-sebelum-patch-11.sql
```

Pastikan file backup terbentuk dan ukurannya tidak 0:

```bash
ls -lh backup-sebelum-patch-11.sql
```

### 2. Ekstrak ZIP patch

Ekstrak `koni-klaten-patch-11.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa.

Jangan menimpa `.env` milikmu. Patch ini tidak menyertakan `.env`, database, `vendor`, atau `node_modules`.

### 3. (Opsional) Atur password demo di `.env`

Jika ingin menetapkan nilainya secara eksplisit, tambahkan:

```env
DEFAULT_PASSWORD_ADMIN_CABOR=CaborDemo123!
DEFAULT_PASSWORD_ATLET=12345678
DEFAULT_PASSWORD_PELATIH=87654321
DEFAULT_PASSWORD_STAFF_KONI=StaffDemo123!
```

Jika variabel ini tidak ditambahkan, nilai default pada `config/account_defaults.php` akan digunakan.

### 4. Jalankan migration dan bersihkan cache

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan migrate --force
```

Migration menambahkan:
- kolom catatan dan informasi pemrosesan pada `izin_latihans`;
- relasi `user_id` nullable dan unik pada `pengurus`.

Migration tidak menghapus data lama.

### 5. Jalankan seluruh pengujian

```bash
docker compose exec laravel.test php artisan test
```

Patch menambahkan `tests/Feature/WorkflowAndAutomaticAccountsTest.php` untuk alur penolakan prestasi, keputusan izin, pembatasan cabang, dan pembuatan akun otomatis.

### 6. Cara menggunakan fitur akun otomatis

- **Cabor:** saat menambah Cabor, isi bagian Akun Admin Cabor (nama pemegang akun dan email login).
- **Atlet:** isi email pada form Atlet (Admin KONI atau Admin Cabor). Email tersebut menjadi alamat login.
- **Pelatih:** isi email pada form Pelatih (Admin KONI atau Admin Cabor). Email tersebut menjadi alamat login.
- **Staff KONI:** pada form Struktur Organisasi, pilih tingkat `Pengurus KONI Kabupaten Klaten`, isi email, lalu centang `Buat akun login Staff KONI secara otomatis`. Jangan centang opsi ini untuk pengurus tingkat Cabor.

Setelah berhasil, halaman admin menampilkan email dan password awal sesuai role. Sampaikan kredensial tersebut secara privat kepada pemilik akun.

## Catatan

- Patch ini mempertahankan route dan role yang sudah ada.
- Akun Atlet/Pelatih yang dibuat otomatis akan dinonaktifkan jika profil diubah menjadi nonaktif.
- Akun terkait profil dinonaktifkan saat profil Atlet/Pelatih/Pengurus dihapus agar tidak meninggalkan akun login aktif tanpa profil.
- Akun Admin Cabor mengikuti status aktif/nonaktif Cabor.
- Jalankan tes setelah migration. Jangan membagikan link Quick Tunnel dengan data asli atau password demo bersama.
