# PATCH 23: QR Download dan Absensi Mandiri dari HP

Patch ini melanjutkan modul absensi QR yang sudah ada. Patch tidak menambah tabel, tidak menjalankan migration, dan tidak mengubah data absensi.

## Perubahan

1. Pengelola dapat mengunduh QR sebagai PNG atau SVG, serta mencetak QR langsung dari halaman detail sesi.
2. Peserta dapat memindai QR yang tampil di laptop atau hasil cetak menggunakan kamera bawaan HP. QR tetap memakai token harian.
3. Semua role internal mendapat bagian **Absensi Saya** pada halaman Absensi. Pengguna dapat membuka sesi yang sesuai dengan role dan cabor akunnya tanpa memindai QR dari HP yang sama.
4. Rute absensi mandiri tetap menggunakan validasi sesi, role, cabor, jadwal, dan selfie yang sama dengan rute QR.
5. Check-in dan check-out tetap mewajibkan selfie. Halaman menyediakan kamera langsung browser dan opsi **Kamera HP** untuk membuka kamera perangkat pada browser yang mendukung atribut capture.
6. Role Public tetap tidak dapat membuka halaman atau mengirim absensi.
7. Pengaturan `APP_BIND_ADDRESS` bersifat opsional. Nilai default tetap `127.0.0.1`, sehingga aplikasi tetap hanya dapat diakses dari laptop.
8. `ATTENDANCE_BASE_URL` opsional untuk membuat QR menggunakan alamat IP laptop saat peserta memindai dari HP.

## Cara memasang

Buat backup database demo terlebih dahulu. Ekstrak ZIP patch ke root project `~/koni-klaten` dan setujui penggantian file.

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-23.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Patch ini tidak memerlukan `php artisan migrate`, `migrate:fresh`, rebuild Docker, atau `npm run build`.

Jalankan pengujian fitur:

```bash
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Lalu jalankan seluruh pengujian:

```bash
docker compose exec laravel.test php artisan test
```

## Cara memakai

**Unduh atau cetak QR**

1. Login sebagai Admin KONI, Staff KONI, Ketua KONI, Sekretaris KONI, atau Admin Cabor sesuai kewenangan.
2. Buka menu Absensi, lalu pilih detail sesi.
3. Tekan **Unduh PNG**, **Unduh SVG**, atau **Cetak QR**.
4. Tampilkan QR pada layar laptop atau cetak. Peserta memindainya dengan kamera bawaan HP.

**Absensi langsung dari HP**

1. Pengguna internal login dari HP.
2. Buka menu **Absensi Saya** atau **Absensi QR & Selfie**.
3. Pilih sesi yang muncul pada bagian **Absensi Saya**.
4. Tekan **Aktifkan Kamera** atau **Kamera HP**.
5. Ambil selfie, lalu kirim Check-in. Setelah kegiatan selesai, buka sesi yang sama dan kirim Check-out dengan selfie kedua.

Pengguna hanya melihat sesi yang sesuai dengan role dan batas cabor akunnya. Pengguna Public tidak mendapat akses.

## Opsional: membuka demo laptop dari HP pada Wi-Fi yang sama

Secara default, aplikasi tetap hanya terbuka dari laptop melalui `127.0.0.1`. Untuk mengizinkan HP membuka aplikasi melalui jaringan lokal, ubah `.env` pada laptop. Gunakan alamat IPv4 Wi-Fi laptop dari perintah `ipconfig` di Windows, bukan alamat `localhost`.

Contoh jika alamat IPv4 laptop adalah `192.168.1.10` dan `APP_PORT=8000`:

```dotenv
APP_BIND_ADDRESS=0.0.0.0
ATTENDANCE_BASE_URL=http://192.168.1.10:8000
```

Setelah menyimpan `.env`, buat ulang service Sail agar binding port berubah:

```bash
docker compose up -d --force-recreate laravel.test
docker compose exec laravel.test php artisan optimize:clear
```

Pastikan HP dan laptop terhubung ke Wi-Fi yang sama, lalu buka `http://192.168.1.10:8000` dari HP. Jika Windows Firewall meminta izin, izinkan koneksi lokal pada port aplikasi.

Jangan mengubah `APP_BIND_ADDRESS` jika demo hanya berjalan di laptop. Nilai default menjaga aplikasi tetap lokal.

## Catatan akses kamera

Browser hanya mengizinkan pratinjau kamera langsung melalui HTTPS atau alamat `localhost`. Saat HP membuka alamat IP laptop melalui HTTP, tombol **Aktifkan Kamera** mungkin tidak tersedia. Tombol **Kamera HP** meminta browser membuka kamera bawaan melalui mekanisme capture perangkat, jika browser mendukungnya. Untuk pratinjau kamera langsung yang konsisten dari HP melalui jaringan lokal, aplikasi perlu HTTPS lokal dan sertifikat yang dipercaya HP.

QR yang dibuat ketika panel pengelola dibuka melalui `localhost` tidak dapat dipakai oleh HP karena `localhost` pada HP menunjuk ke HP itu sendiri. Gunakan `ATTENDANCE_BASE_URL` seperti contoh di atas, atau buka panel pengelola menggunakan alamat IP laptop sebelum membagikan QR.

## Catatan keamanan dan data

- Tidak ada migration baru.
- Tidak ada data absensi lama yang dihapus atau diubah.
- Foto selfie tetap tersimpan pada disk privat.
- Token QR tetap berubah setiap hari.
- Jangan jalankan `php artisan migrate:fresh` atau `docker compose down -v` untuk memasang patch ini.
