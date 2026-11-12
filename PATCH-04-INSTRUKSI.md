# Patch 04 — Sistem Persuratan KONI Klaten

Patch ini melengkapi modul Persuratan berdasarkan alur yang diminta klien.

## Alur wajib

1. Admin Cabor membuat surat sebagai draft, lalu mengajukannya kepada Staff KONI.
2. Staff KONI memeriksa surat. Staff dapat meminta revisi kepada Admin Cabor atau meneruskan surat kepada Admin KONI.
3. Admin KONI hanya dapat melihat surat yang sudah diteruskan Staff. Admin memberi keputusan: diterima, ditolak, atau perlu revisi.
4. Jika Admin meminta revisi, surat kembali ke Staff. Staff wajib meneruskan catatan revisi kepada Admin Cabor. Admin Cabor tidak dapat melihat keputusan itu sebelum Staff menyampaikannya.
5. Jika Admin menerima/menolak, Staff menyampaikan hasil kepada Admin Cabor. Proses baru berstatus selesai setelah Staff menandai hasil telah disampaikan.

## Fitur

- Pengajuan surat oleh Admin Cabor dengan pembatasan cabang olahraga.
- Pemeriksaan, permintaan revisi, dan penerusan oleh Staff KONI.
- Keputusan Admin KONI hanya untuk surat yang telah diteruskan Staff.
- Penyampaian hasil keputusan oleh Staff kepada Admin Cabor.
- Riwayat aktivitas dan catatan setiap tahapan.
- Penyimpanan dokumen secara privat dan unduhan melalui route yang memeriksa hak akses.
- Manajemen template surat oleh Staff dan Admin KONI.
- Admin Cabor hanya dapat melihat dan mengunduh template yang aktif.
- Menu Persuratan dan Template Surat ditambahkan ke navigasi masing-masing role.

## Instalasi

Jalankan dari folder utama proyek `~/koni-klaten`:

1. Ekstrak ZIP dan izinkan file dengan nama sama ditimpa.
2. Jalankan migrasi baru:
   `docker compose exec laravel.test php artisan migrate`
3. Bersihkan cache:
   `docker compose exec laravel.test php artisan optimize:clear`
4. Build asset:
   `docker compose exec laravel.test npm run build`
5. Jalankan seluruh pengujian:
   `docker compose exec laravel.test php artisan test`

Target setelah patch: **52 tests passed** (47 pengujian sebelumnya + 5 pengujian alur Persuratan baru).

Patch ini tidak mengubah `.env`, tidak menghapus data, dan tidak menjalankan `migrate:fresh`. Migrasi menambahkan tabel `surats`, `surat_activities`, dan `template_surats`.

File surat disimpan pada disk `local` agar tidak menjadi file publik. Tidak perlu menjalankan `storage:link` untuk modul Persuratan.
