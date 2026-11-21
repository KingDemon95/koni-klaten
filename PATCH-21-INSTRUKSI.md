# KONI Klaten Patch 21

Patch ini menambahkan Master Data Dinamis, pengaturan backdrop dashboard, dan absensi QR Code dengan selfie.

## Isi patch

### Master Data Dinamis

- Admin KONI dapat membuat field tambahan untuk Atlet dan Pelatih.
- Jenis field: teks, teks panjang, angka, tanggal, email, URL, dropdown, dan checkbox.
- Field dapat diatur wajib atau opsional, aktif atau nonaktif, serta urutan tampilnya.
- Form Admin KONI, Admin Cabor, Atlet, dan Pelatih menggunakan field aktif yang sama.
- Nilai tambahan tersimpan pada kolom JSON `custom_data` di tabel `atlets` dan `pelatihs`.
- Menonaktifkan field tidak menghapus nilai lama.

### Backdrop Dashboard

- Admin KONI dapat mengunggah gambar JPG, PNG, atau WEBP maksimal 4 MB.
- Admin dapat mengatur tingkat lapisan gelap dan posisi gambar.
- Admin dapat melihat gambar yang sedang digunakan dan menghapusnya.
- Backdrop tampil pada area sambutan dashboard Admin KONI.

### Absensi QR dan Selfie

- Admin KONI dapat membuat sesi absensi berdasarkan jadwal, cabang olahraga, dan jenis peserta.
- Sistem membuat QR Code unik untuk setiap sesi.
- Peserta harus login dengan akun Atlet atau Pelatih.
- Peserta mengambil selfie langsung dari kamera browser. Sistem tidak menerima unggahan galeri sebagai pengganti selfie.
- Satu akun hanya dapat check-in satu kali pada satu sesi.
- Sesi dapat ditutup oleh Admin KONI.
- Rekap kehadiran dapat diunduh dalam format CSV.
- File selfie tersimpan pada disk privat dan hanya Admin KONI yang dapat melihatnya.

## Cara memasang

Jalankan dari folder project `~/koni-klaten`.

### 1. Ekstrak patch

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-21.zip -d .
```

### 2. Jalankan migration

```bash
docker compose exec laravel.test php artisan migrate --force
```

Migration menambahkan kolom `custom_data` dan membuat tabel `custom_fields`, `dashboard_settings`, `attendance_sessions`, serta `attendance_records`.

### 3. Pastikan storage link tersedia

Jalankan perintah berikut hanya jika `public/storage` belum tersedia:

```bash
docker compose exec laravel.test php artisan storage:link
```

Backdrop dashboard memakai disk publik. Selfie absensi memakai disk privat.

### 4. Bersihkan cache

```bash
docker compose exec laravel.test php artisan optimize:clear
```

### 5. Jalankan pengujian fitur baru

```bash
docker compose exec laravel.test php artisan test --filter=CustomMasterDataTest

docker compose exec laravel.test php artisan test --filter=DashboardAppearanceTest

docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Patch ini menambahkan 12 pengujian baru. Jalankan seluruh suite setelah pengujian fitur baru:

```bash
docker compose exec laravel.test php artisan test
```

## Catatan penting untuk absensi dengan kamera

Browser hanya mengizinkan akses kamera pada HTTPS atau `localhost`. Untuk scan QR menggunakan ponsel, buka panel Admin dari domain HTTPS yang dapat diakses ponsel. QR Code mengikuti host dari halaman Admin saat sesi dibuat. Jangan membuat QR dari `http://localhost` jika peserta akan memindainya menggunakan ponsel lain.

Akun yang dapat melakukan check-in adalah Atlet dan Pelatih. Admin KONI mengelola sesi dan rekap. Sesi dengan pilihan cabang olahraga tertentu hanya menerima akun yang terhubung dengan cabang tersebut.

## Tidak diperlukan

- Tidak perlu menambah dependency Composer.
- Tidak perlu mengubah `composer.json` atau `composer.lock`.
- Tidak perlu menjalankan `npm install`.
- Tidak perlu `npm run build`.
- Tidak perlu `migrate:fresh`.
- Jangan menghapus volume database Docker.
