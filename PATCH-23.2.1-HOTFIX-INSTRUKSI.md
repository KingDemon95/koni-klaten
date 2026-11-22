# PATCH 23.2.1 HOTFIX: Perbaikan Layout Absensi Admin Cabor

Hotfix ini memperbaiki HTTP 500 pada halaman Absensi Saya milik Admin Cabor.

## Penyebab

`resources/views/layouts/cabor.blade.php` menggunakan variabel `$cabor`, tetapi controller absensi tidak selalu mengirim variabel tersebut. Blade kemudian melempar `Undefined variable $cabor` ketika merender layout.

## Perbaikan

- Layout Admin Cabor sekarang menggunakan `$cabor` yang diberikan controller jika tersedia.
- Jika controller tidak mengirim `$cabor`, layout mengambil relasi `cabangOlahraga` dari akun yang sedang login.
- Layout menampilkan teks fallback yang aman jika relasi cabang tidak tersedia.
- Perbaikan berlaku untuk halaman Absensi Saya, Buat Sesi, dan Detail Absensi yang memakai layout Admin Cabor.
- Regression test memeriksa ketiga halaman tersebut.

## Cara memasang

Pastikan Patch 23.2 sudah terpasang. Ekstrak ZIP hotfix ini ke root project `~/koni-klaten` dan izinkan penggantian file.

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-23.2.1-hotfix.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Jalankan pengujian terkait:

```bash
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Kemudian jalankan seluruh pengujian:

```bash
docker compose exec laravel.test php artisan test
```

## Catatan

- Tidak ada migration database.
- Tidak mengubah data absensi.
- Tidak mengubah token QR, validasi role/cabor, alur pemindaian galeri, atau kewajiban selfie.
- Tidak perlu rebuild Docker atau menjalankan `npm run build`.
