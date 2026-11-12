# PATCH 12 — Pusat Notifikasi dan Pemberitahuan Alur Kerja

Patch ini ditujukan untuk source KONI Klaten setelah Patch 11 (Laravel 13).

## Isi patch

1. **Pusat notifikasi internal**
   - Menambahkan tabel database `notifications` menggunakan format standar Laravel.
   - Menambahkan menu Notifikasi pada topbar Admin KONI, Staff KONI, Admin Cabor, Atlet, dan Pelatih.
   - Menampilkan jumlah notifikasi yang belum dibaca.
   - Pengguna dapat membuka notifikasi dan menandai seluruh notifikasi miliknya sebagai sudah dibaca.
   - Notifikasi hanya dapat dibaca pemilik akun; endpoint pembukaan tidak menerima URL eksternal.

2. **Pemberitahuan prestasi**
   - Admin Cabor cabang terkait diberi notifikasi ketika Atlet/Pelatih mengirim atau mengirim ulang prestasi.
   - Pemilik prestasi diberi notifikasi ketika prestasi diverifikasi, diminta revisi, atau ditolak.

3. **Pemberitahuan izin latihan**
   - Admin Cabor cabang terkait diberi notifikasi ketika ada permohonan izin latihan baru.
   - Atlet/Pelatih diberi notifikasi ketika permohonan disetujui atau ditolak.

4. **Pemberitahuan persuratan**
   - Staff KONI diberi notifikasi saat Admin Cabor mengajukan surat.
   - Admin Cabor diberi notifikasi saat Staff meminta revisi dan saat hasil keputusan disampaikan.
   - Admin KONI diberi notifikasi saat Staff meneruskan surat untuk diputuskan.
   - Staff yang menangani surat diberi notifikasi saat keputusan Admin KONI tersedia.

5. **Pemberitahuan agenda**
   - Staff KONI diberi notifikasi saat Admin Cabor mengajukan agenda.
   - Pembuat agenda diberi notifikasi saat agenda perlu revisi atau sudah dipublikasikan.

## Cara memasang

### 1. Backup database

```bash
cd ~/koni-klaten
docker compose exec -T mysql sh -lc 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"' > backup-sebelum-patch-12.sql
ls -lh backup-sebelum-patch-12.sql
```

Pastikan backup tidak kosong.

### 2. Ekstrak patch

Ekstrak `koni-klaten-patch-12.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa. Jangan menimpa `.env`.

### 3. Jalankan migration dan bersihkan cache

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan migrate --force
```

Migration menambahkan tabel `notifications`; migration tidak menghapus data aplikasi yang sudah ada.

### 4. Jalankan seluruh pengujian

```bash
docker compose exec laravel.test php artisan test
```

Patch ini menambahkan `tests/Feature/WorkflowNotificationsTest.php` untuk akses notifikasi, isolasi pemilik, notifikasi prestasi, izin latihan, persuratan, dan agenda.

### 5. Uji manual

- Login sebagai masing-masing role dan pastikan menu Notifikasi tampil.
- Kirim prestasi sebagai Atlet/Pelatih; Admin Cabor cabang terkait menerima notifikasi.
- Review prestasi; pemilik menerima notifikasi.
- Ajukan izin latihan; Admin Cabor menerima notifikasi dan pemohon menerima notifikasi keputusan.
- Jalankan alur persuratan dan pastikan notifikasi diterima oleh pihak berikutnya.
- Ajukan agenda, minta revisi, dan publikasikan; pembuat menerima pemberitahuan yang sesuai.
- Pastikan satu akun tidak dapat membuka notifikasi akun lain.

## Catatan

- Notifikasi disimpan di database dan tampil saat pengguna membuka atau memuat ulang aplikasi; patch ini bukan notifikasi push/browser real-time.
- Pengiriman email tidak diaktifkan. Email membutuhkan konfigurasi SMTP terpisah dan persetujuan kebijakan komunikasi organisasi.
- Notifikasi tidak menyertakan dokumen privat atau kredensial/password.
- Tabel notifikasi harus disertakan dalam backup database. Terapkan kebijakan retensi sesuai kebutuhan organisasi.
- Jalankan seluruh tes sebelum demo atau deployment.
