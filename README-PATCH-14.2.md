# PATCH 14.2: Perbaikan Responsivitas QR Absensi Semua Role

## Masalah yang diperbaiki

Patch 14.1 sebelumnya menguji dan mengubah `resources/views/admin/absensi/show.blade.php`.
Namun, `AttendanceSessionController::show()` pada source terbaru mengembalikan view `attendance.show`, sedangkan halaman QR peserta menggunakan view `attendance.index`.

Akibatnya, perbaikan QR pada patch sebelumnya tidak menyentuh dua view yang benar-benar dipakai oleh alur absensi.

## Cakupan perbaikan

1. `resources/views/attendance/index.blade.php`
   - QR pada kartu "Absensi Saya" memakai kontainer persegi yang lebarnya mengikuti ruang layar.
   - SVG QR dipaksa mengikuti lebar kontainer, tanpa mengubah isi/token QR.
   - Kartu, grid, teks URL, dan area tombol diberi batas lebar serta `min-width: 0`.
   - Berlaku untuk Admin KONI, Ketua KONI, Sekretaris KONI, Staff KONI, Admin Cabor, Atlet, dan Pelatih sesuai hak akses sesi.

2. `resources/views/attendance/show.blade.php`
   - Halaman detail QR memakai satu kontainer responsif.
   - Grid detail menjadi satu kolom di layar kecil dan dua kolom pada layar besar.
   - URL scan dapat membungkus baris, sehingga tidak mendorong halaman melebar.
   - Tombol QR tetap dapat digunakan.

3. Enam layout internal:
   - `resources/views/layouts/admin.blade.php`
   - `resources/views/layouts/cabor.blade.php`
   - `resources/views/layouts/staff.blade.php`
   - `resources/views/layouts/sekretaris.blade.php`
   - `resources/views/layouts/atlet.blade.php`
   - `resources/views/layouts/pelatih.blade.php`

   Elemen `<main>` sekarang memiliki `min-w-0 w-full max-w-full` agar konten absensi tidak memperlebar area flex layout.

4. `tests/Feature/MobileResponsiveLayoutTest.php`
   - Menguji QR pada halaman "Absensi Saya" untuk tujuh role internal.
   - Menguji detail QR untuk role pengelola yang memiliki akses ke halaman detail.
   - Menguji struktur kontainer responsif yang benar-benar dirender oleh view aktif.

## Catatan penting

- Patch ini memperbaiki view aktif `attendance.index` dan `attendance.show`.
- `resources/views/admin/absensi/show.blade.php` tidak diubah karena controller pada source yang dikirim tidak menggunakan view tersebut untuk halaman detail QR.
- Tidak ada migration atau perubahan database.
- Tidak mengubah route, role/permission, token QR, jadwal sesi, proses unduh QR, pemindaian dari galeri, check-in/check-out, kamera, maupun kewajiban selfie.
- Public tetap tidak mendapatkan halaman Absensi Saya. Patch ini hanya mengubah responsivitas halaman internal yang sudah memiliki akses.

## Cara memasang

Letakkan ZIP di root proyek `~/koni-klaten`, lalu jalankan:

```bash
cd ~/koni-klaten

unzip -o PATCH-14.2-ALL-ROLES-ATTENDANCE-MOBILE-FIX.zip -d .

docker compose exec laravel.test php artisan optimize:clear

docker compose exec laravel.test php artisan test --filter=MobileResponsiveLayoutTest
```

Jika test khusus lulus, jalankan regression test:

```bash
docker compose exec laravel.test php artisan test
```

## Pemeriksaan manual dari HP

Uji pada lebar layar sekitar 320 px, 360 px, dan 390 px:

- Buka "Absensi Saya" sebagai setiap role internal yang berhak.
- Pastikan QR berada di dalam kartu dan tidak keluar dari layar.
- Pastikan tombol "Unduh QR ke HP" dan "Pindai QR dari Galeri" tetap terlihat penuh.
- Untuk Admin KONI, Staff, Ketua, Sekretaris, dan Admin Cabor, buka halaman detail sesi dan periksa QR serta URL scan.
- Pastikan halaman tidak memiliki scroll horizontal.
- Coba unduh dan pindai QR. Alur scan, kamera, selfie, dan check-in/check-out harus tetap berfungsi.
