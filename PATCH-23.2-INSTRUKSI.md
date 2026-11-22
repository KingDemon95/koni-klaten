# PATCH 23.2: QR Wajib dari Galeri dan Konfirmasi Selfie

Patch ini memperbaiki alur Patch 23 agar seluruh check-in dan check-out wajib dimulai dari QR. Patch 23.1 Hotfix tetap dipertahankan.

## Alur pengguna

1. Pengelola membuat sesi absensi dan menentukan role serta cabor peserta.
2. QR harian otomatis tampil pada halaman **Absensi Saya** milik setiap pengguna internal yang memenuhi target role dan cabor.
3. Pengguna menekan **Unduh QR ke HP** untuk menyimpan gambar QR.
4. Pengguna menekan **Pindai QR dari Galeri**, lalu memilih gambar QR yang baru diunduh. Browser membaca token dari gambar tersebut.
5. Server memvalidasi token harian, status dan jadwal sesi, role, serta cabor akun.
6. Setelah QR valid, halaman absensi mencoba membuka kamera depan secara otomatis.
7. Pengguna mengambil selfie, memeriksa pratinjau, lalu menekan tombol **Konfirmasi Check-in** atau **Konfirmasi Check-out**.
8. Server menyimpan kehadiran dan foto selfie setelah konfirmasi berhasil.

QR juga dapat dipindai dari layar atau cetakan perangkat lain. QR yang dipilih dari galeri tetap diarahkan ke origin website yang sedang dibuka, bukan ke host yang tertulis di gambar. Ini memungkinkan QR yang diunduh dari HP dipindai pada HP yang sama.

## Perubahan teknis

- QR harian sekarang muncul langsung di setiap kartu sesi pada **Absensi Saya**.
- Pengguna dapat mengunduh QR sebagai PNG.
- Pengguna dapat memilih gambar QR dari galeri. Browser memakai `BarcodeDetector` jika tersedia dan fallback `html5-qrcode` untuk membaca file gambar.
- Rute absensi langsung tanpa QR dihapus. Check-in dan check-out hanya menerima token QR melalui `/absensi/scan/{token}`.
- Halaman setelah pemindaian mencoba mengaktifkan kamera depan secara otomatis.
- Kebijakan Permissions-Policy mengizinkan akses kamera untuk origin aplikasi sendiri, tetapi tetap menolak mikrofon dan geolokasi.
- Pengguna harus melihat pratinjau dan menekan tombol centang untuk mengirim selfie.
- Sesi yang selesai tidak lagi menampilkan tombol pemindaian.
- Validasi role, cabor, token harian, jadwal, selfie wajib, dan penyimpanan privat tetap berjalan di server.

## Cara memasang

Buat backup database demo terlebih dahulu. Ekstrak ZIP patch ke root project `~/koni-klaten` dan setujui penggantian file.

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-23.2.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Tidak ada migration. Tidak perlu `migrate:fresh`, rebuild Docker, atau `npm run build`.

Jalankan pengujian:

```bash
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Lalu jalankan seluruh test:

```bash
docker compose exec laravel.test php artisan test
```

## Catatan penggunaan kamera

Kamera langsung browser membutuhkan HTTPS atau `localhost`. Jika HP membuka website laptop melalui alamat IP lokal dengan HTTP, browser dapat menolak akses kamera langsung. Dalam kondisi tersebut, halaman tetap menyediakan tombol **Kamera HP** yang meminta browser membuka kamera perangkat untuk mengambil foto.

Pemindaian gambar QR dari galeri tidak membutuhkan izin kamera. Library fallback `html5-qrcode` dimuat dari CDN jsDelivr, sehingga browser memerlukan koneksi internet jika tidak mendukung `BarcodeDetector`.

Untuk QR yang dipindai dari perangkat lain, isi `ATTENDANCE_BASE_URL` dengan alamat laptop yang dapat dijangkau HP, misalnya `http://192.168.1.10:8000`. Untuk alur unduh dan pilih gambar QR pada HP yang sama, scanner mengarahkan token ke origin website yang sedang dibuka.

## Keamanan dan data

- Tidak ada migration atau perubahan struktur database.
- Tidak ada data absensi lama yang dihapus.
- Rute langsung tanpa QR tidak lagi tersedia.
- Server tetap memvalidasi tanda tangan token QR dan tanggal berlakunya.
- Selfie check-in dan check-out tetap wajib serta tersimpan pada disk privat.
- Role Public tetap tidak memiliki akses ke absensi.
