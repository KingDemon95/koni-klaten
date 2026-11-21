# Patch 21: Master Data, Backdrop, dan Absensi

## Struktur data

`custom_fields` menyimpan definisi field tambahan berdasarkan entitas `atlet` atau `pelatih`. Nilai setiap field tersimpan pada JSON `custom_data` di tabel profil. Pendekatan ini menjaga kolom biodata utama tetap stabil saat organisasi menambah kebutuhan formulir.

`dashboard_settings` menyimpan satu konfigurasi backdrop untuk dashboard Admin KONI. File gambar berada pada disk publik karena browser perlu menampilkannya.

`attendance_sessions` menyimpan sesi, jadwal, cabang olahraga, target peserta, token QR, dan status. `attendance_records` menyimpan satu catatan per akun dan sesi, termasuk snapshot nama, email, peran, waktu hadir, serta lokasi file selfie.

## Keamanan dan validasi

- Pengelolaan Master Data, backdrop, sesi, ekspor, dan akses selfie hanya tersedia untuk role `admin_koni`.
- Admin Cabor hanya menggunakan field yang Admin KONI aktifkan. Cakupan data atlet dan pelatih tetap mengikuti cabang milik Admin Cabor.
- Peserta absensi wajib login dan harus sesuai target peran serta cabang olahraga sesi.
- Token QR menggunakan nilai acak per sesi. Sistem menolak check-in sebelum jadwal, setelah jadwal, atau setelah Admin menutup sesi.
- Database membatasi satu record untuk kombinasi sesi dan akun.
- Selfie menerima data JPEG dari hasil capture kamera, memeriksa signature dan dimensi, lalu menyimpan file pada disk privat.
- Route selfie berada di balik middleware role Admin KONI.
- Ekspor CSV menetralkan awalan formula spreadsheet pada data teks.

## Batasan implementasi

Patch ini belum menerapkan geofencing, pengenalan wajah, atau absensi otomatis dari agenda. QR hanya membuka halaman check-in yang memerlukan autentikasi. Kamera browser membutuhkan HTTPS kecuali localhost.
