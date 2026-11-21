# PATCH 22: Absensi Multi-Role dan Tampilan Website Publik

Patch ini melanjutkan source KONI Klaten yang sudah ada. Patch tidak menghapus database, tidak menjalankan `migrate:fresh`, dan tidak mengubah file `.env`.

## Cakupan

### Absensi QR

- Admin KONI, Staff KONI, Ketua KONI, Sekretaris KONI, Admin Cabor, Atlet, dan Pelatih dapat menggunakan absensi sesuai role peserta yang pengelola pilih.
- Role Public tidak dapat membuka halaman absensi atau memproses QR.
- Admin KONI dan Staff KONI dapat membuat serta mengelola sesi. Admin Cabor hanya dapat mengelola sesi untuk cabangnya sendiri.
- Ketua KONI dan Sekretaris KONI dapat melihat sesi dan rekap, tetapi tidak dapat mengubah sesi.
- Atlet dan Pelatih hanya melihat riwayat absensi pribadi.
- Pengelola dapat memilih satu atau beberapa role untuk setiap sesi. Pilihan ini mendukung kegiatan khusus Staff KONI.
- QR menggunakan token bertanda tangan yang berubah setiap tanggal berdasarkan zona waktu aplikasi. Halaman detail sesi memuat ulang QR setelah pergantian hari.
- Check-in dan check-out masing-masing mewajibkan selfie dari kamera.
- Rekap menampilkan waktu check-in, waktu check-out, role, dan cabang olahraga. Pengelola dapat memfilter role, cabor, dan tanggal.
- Foto selfie tetap privat. Admin KONI dan Staff KONI dapat melihat semua selfie. Admin Cabor hanya dapat melihat selfie cabangnya. Ketua, Sekretaris, Atlet, dan Pelatih tidak mendapat akses ke foto peserta lain.

Migration mengisi daftar role pada sesi absensi lama. QR lama perlu dibagikan ulang dari halaman detail sesi agar peserta menggunakan QR harian yang baru.

### Tampilan Website Publik

- Admin KONI mendapat menu **Tampilan Website Publik**.
- Pengaturan warna utama, warna sekunder, warna header, warna footer, dan font berlaku pada seluruh halaman publik.
- Admin dapat mengunggah backdrop berbeda untuk Beranda, Cabang Olahraga, Struktur Organisasi, Agenda, Statistik Prestasi, Berita, dan Dokumentasi.
- Halaman detail menggunakan backdrop kategori masing-masing.
- Admin menyimpan perubahan sebagai Draft, membuka Preview, lalu memilih Publikasikan.
- Draft tidak mengubah website publik sampai Admin mempublikasikannya.
- Pengaturan ini terpisah dari **Tampilan Dashboard Internal**. Dashboard seluruh role tidak ikut berubah.
- Logo dan identitas organisasi tetap dikelola melalui menu Profil & Pengaturan KONI.

## Cara memasang

1. Buat backup database demo terlebih dahulu.
2. Ekstrak seluruh isi `koni-klaten-patch-22.zip` ke root project `~/koni-klaten`, dengan mempertahankan struktur folder. Setujui penggantian file yang diminta.
3. Jalankan migration tambahan:

```bash
cd ~/koni-klaten

docker compose exec laravel.test php artisan migrate --force
```

4. Bersihkan cache Laravel:

```bash
docker compose exec laravel.test php artisan optimize:clear
```

5. Jalankan seluruh pengujian:

```bash
docker compose exec laravel.test php artisan test
```

Patch ini hanya mengubah PHP, Blade, route, migration, dan test. Build frontend tidak diperlukan.

**Jangan jalankan `php artisan migrate:fresh` dan jangan jalankan `docker compose down -v`.** Keduanya tidak diperlukan untuk patch ini dan dapat menghapus data.

## Pemeriksaan manual

1. Login sebagai Staff KONI. Pastikan menu **Absensi QR & Selfie** muncul.
2. Buat sesi dengan peserta hanya **Staff KONI**. Login sebagai Staff, buka tautan QR dari pengelola, lakukan selfie check-in, lalu lakukan selfie check-out.
3. Ulangi pemeriksaan akses untuk Admin KONI, Ketua, Sekretaris, Admin Cabor, Atlet, dan Pelatih. Pastikan role Public menerima penolakan akses.
4. Periksa rekap berdasarkan role dan cabor. Pastikan Admin Cabor hanya melihat data cabangnya.
5. Login sebagai Admin KONI, buka **Tampilan Website Publik**. Unggah backdrop Beranda dan Cabang Olahraga dengan gambar berbeda.
6. Klik **Simpan Draft**, buka Preview kedua halaman, lalu klik **Publikasikan Website**.
7. Buka website publik dan pastikan backdrop setiap halaman sesuai. Buka dashboard internal dan pastikan tampilannya tidak berubah.

## Catatan demo lokal di satu laptop

Karena aplikasi berjalan di `localhost`, tautan QR hanya dapat dibuka dari laptop yang menjalankan Docker. Untuk demo pada laptop yang sama, gunakan tombol **Salin tautan absensi**, lalu buka tautan tersebut di tab browser baru. Kamera laptop tetap dapat mengambil selfie melalui halaman tersebut. Ponsel lain tidak dapat membuka alamat `localhost` milik laptop tanpa konfigurasi jaringan tambahan.
