# Patch 03 — Agenda & Kejuaraan

Patch ini menambahkan modul Agenda, Kegiatan, dan Kejuaraan untuk Admin Cabor, Staff KONI, Admin KONI, dan halaman Public.

## Hak akses
- Admin Cabor: mengelola agenda cabangnya sendiri, lalu mengajukan publikasi. Tidak dapat mempublikasikan sendiri.
- Staff KONI: mengelola seluruh agenda, meninjau pengajuan, meminta revisi, dan dapat langsung mempublikasikan agenda baru maupun pengajuan.
- Admin KONI: akses penuh seluruh agenda dan publikasi.
- Public: hanya melihat agenda berstatus `published`.

## Instalasi
1. Ekstrak ZIP ke folder utama proyek (`~/koni-klaten`) dan izinkan file yang sama ditimpa.
2. Jalankan migrasi baru:
   `docker compose exec laravel.test php artisan migrate`
3. Pastikan symbolic link storage tersedia (jalankan jika belum pernah dibuat):
   `docker compose exec laravel.test php artisan storage:link`
4. Build asset agar class Tailwind baru ikut terpaket:
   `docker compose exec laravel.test npm run build`
5. Bersihkan cache:
   `docker compose exec laravel.test php artisan optimize:clear`
6. Jalankan seluruh pengujian:
   `docker compose exec laravel.test php artisan test`

Patch ini tidak menjalankan `migrate:fresh` dan tidak mengubah `.env`. Migrasi hanya menambahkan tabel `agendas`.
