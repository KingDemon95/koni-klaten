# PATCH 13 — Laporan & Rekapitulasi KONI Klaten

Patch ini dipasang setelah **Patch 12 dan Patch 12.1**.

## Fitur

1. **Halaman Laporan & Rekapitulasi**
   - Ringkasan jumlah cabang olahraga aktif.
   - Jumlah Atlet dan Pelatih aktif.
   - Jumlah prestasi yang sudah terverifikasi.
   - Rekap medali emas, perak, dan perunggu.
   - Jumlah prestasi yang masih menunggu verifikasi.
   - Rekap per cabang olahraga.
   - Daftar prestasi terverifikasi terbaru.

2. **Filter laporan**
   - Filter tahun prestasi.
   - Filter cabang olahraga untuk Admin KONI dan Staff KONI.
   - Admin Cabor otomatis dibatasi pada cabang olahraga yang ditugaskan kepadanya, termasuk saat mengubah parameter URL.

3. **Ekspor CSV**
   - Rekapitulasi per cabang olahraga.
   - Daftar prestasi terverifikasi.
   - Ekspor mengikuti filter yang sedang dipilih.
   - CSV menggunakan UTF-8 BOM agar lebih mudah dibuka di Excel.
   - Nilai yang berpotensi menjadi formula spreadsheet diamankan.
   - Dokumen bukti, alamat, nomor telepon, dan email tidak dimasukkan ke dalam ekspor.

4. **Navigasi responsif**
   - Menu Laporan & Rekapitulasi ditambahkan ke panel Admin KONI, Staff KONI, dan Admin Cabor, termasuk navigasi mobile.

## Cara memasang

### 1. Pastikan Patch 12.1 sudah terpasang

Kamu sudah melaporkan seluruh **107 test lulus** setelah Patch 12.1.

### 2. Ekstrak ZIP

Ekstrak `koni-klaten-patch-13.zip` ke folder utama proyek:

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-13.zip -d .
```

Izinkan file yang sama ditimpa. Jangan menimpa `.env`.

### 3. Bersihkan cache Laravel

```bash
docker compose exec laravel.test php artisan optimize:clear
```

### 4. Periksa route baru

```bash
docker compose exec laravel.test php artisan route:list --name=laporan
```

Seharusnya terlihat route laporan untuk Admin KONI, Staff KONI, dan Admin Cabor.

### 5. Jalankan test baru dahulu

```bash
docker compose exec laravel.test php artisan test --filter=ReportsTest
```

Patch ini menambahkan 5 pengujian baru. Target pengujian baru: **5 passed**.

### 6. Jalankan seluruh test

```bash
docker compose exec laravel.test php artisan test
```

Dengan baseline 107 test yang sudah lulus, target setelah Patch 13 adalah **112 passed, 0 failed**.

## Pengujian manual

- Login Admin KONI, buka menu **Laporan & Rekapitulasi**.
- Pilih tahun dan cabang olahraga, lalu terapkan filter.
- Unduh CSV rekap cabor dan CSV prestasi.
- Login Staff KONI dan pastikan laporan dapat dibuka serta diekspor.
- Login Admin Cabor dan pastikan laporan hanya menampilkan cabang olahraganya sendiri, meskipun parameter `cabor_id` di URL diubah.

## Catatan teknis

- **Tidak ada migration baru.**
- Tidak perlu menjalankan `php artisan migrate`.
- Tidak perlu rebuild Docker.
- Tidak perlu `npm run build`; patch ini hanya mengubah PHP dan Blade.
- Halaman laporan dan ekspor hanya tersedia bagi Admin KONI, Staff KONI, dan Admin Cabor.
- Data prestasi yang dihitung sebagai capaian hanya yang berstatus `terverifikasi` dari cabang olahraga aktif.
