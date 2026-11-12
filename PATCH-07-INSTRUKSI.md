# Patch 07 — Dokumentasi dan Publikasi

## Fitur
- Staff KONI dan Admin KONI dapat membuat, mengedit, menghapus, dan langsung memublikasikan berita, foto, serta video.
- Konten baru disimpan sebagai draft sampai pengelola menekan tombol Publikasikan.
- Website publik menampilkan hanya konten dengan status `published` dan `published_at` terisi.
- Halaman publik tersedia untuk Berita dan Dokumentasi, termasuk filter pencarian dan kategori.
- Beranda menampilkan berita dan dokumentasi terbaru yang sudah dipublikasikan.
- File media disimpan di disk `local` privat (`storage/app/private`) dan disajikan melalui controller yang memeriksa status publikasi. Draft tidak dapat diakses publik.
- Admin Cabor, Atlet, dan Pelatih tidak memiliki akses ke panel pengelolaan publikasi.

## Batas unggahan
- Foto / sampul berita: JPG, JPEG, PNG, WEBP, maksimal 5 MB.
- Video: MP4, WEBM, MOV, maksimal 50 MB.
- Thumbnail video: JPG, JPEG, PNG, WEBP, maksimal 5 MB.
- Isi berita wajib untuk jenis Berita.
- File media wajib untuk jenis Foto dan Video.

## Pemasangan
1. Ekstrak `koni-klaten-patch-07.zip` di root proyek `~/koni-klaten` dan izinkan file yang sama ditimpa.
2. Jalankan migrasi:
   ```bash
   docker compose exec laravel.test php artisan migrate
   ```
3. Bersihkan cache:
   ```bash
   docker compose exec laravel.test php artisan optimize:clear
   ```
4. Build aset:
   ```bash
   docker compose exec laravel.test npm run build
   ```
5. Jalankan seluruh pengujian:
   ```bash
   docker compose exec laravel.test php artisan test
   ```

Patch ini menambahkan tabel `publikasis`. Jangan gunakan `migrate:fresh`.
