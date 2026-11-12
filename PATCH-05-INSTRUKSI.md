# KONI Klaten — Patch 05: Portal Atlet dan Pelatih

Patch melanjutkan struktur Laravel yang sudah ada. Patch tidak mengganti `.env`, tidak membawa `vendor`, dan tidak menghapus data/database.

## Fitur

- Atlet dan Pelatih dapat memperbarui biodata profil sendiri dan foto profil.
- Atlet dan Pelatih dapat mengunggah prestasi serta bukti dokumen.
- Semua prestasi berstatus menunggu verifikasi; hanya Admin Cabor dari cabang yang sama yang dapat memverifikasi, meminta revisi, atau menolak.
- Hanya prestasi terverifikasi yang ditampilkan pada halaman publik cabang olahraga.
- Atlet dan Pelatih dapat mengunggah dokumen izin latihan dan melihat riwayat unggahannya.
- Admin Cabor dapat melihat serta mengunduh dokumen izin latihan dari cabangnya sendiri.
- Atlet dan Pelatih dapat mengunduh template surat aktif yang dikelola Staff/Admin KONI.
- Bukti prestasi dan dokumen izin latihan disimpan di disk `local` (private), bukan direktori publik.

## Pemasangan

Jalankan dari folder utama `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-05.zip -d .
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test npm run build
docker compose exec laravel.test php artisan test
```

Jangan jalankan `migrate:fresh`. Migration Patch 05 hanya menambahkan tabel `prestasis` dan `izin_latihans`.

## Catatan keamanan

- Prestasi atlet maupun pelatih harus diverifikasi Admin Cabor sebelum tampil di halaman publik.
- Pengguna hanya dapat mengubah profil dan mengakses dokumen miliknya sendiri.
- Admin Cabor dibatasi pada data dari cabang olahraga yang terhubung ke akunnya.
- Template surat yang dapat diunduh oleh Atlet/Pelatih hanya template aktif.
- Jalankan seluruh pengujian di Docker proyek. Di lingkungan penyusunan patch ini, pemeriksaan sintaks PHP dilakukan, tetapi pengujian Laravel belum dijalankan karena dependensi `vendor` dan container proyek pengguna tidak tersedia.
