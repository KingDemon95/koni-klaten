# Patch 06 — Statistik dan Prestasi Kejuaraan

Patch ini menambahkan halaman statistik publik dan pencatatan jenis medali secara eksplisit pada data prestasi.

## Fitur

- Halaman publik `/statistik-prestasi`.
- Ringkasan jumlah cabor aktif, atlet aktif, pelatih aktif, dan prestasi terverifikasi.
- Rekap medali emas, perak, dan perunggu.
- Filter statistik berdasarkan tahun kejuaraan.
- Grafik tren lima tahun, sebaran tingkat kejuaraan, dan peringkat cabor berdasarkan jumlah prestasi terverifikasi.
- Prestasi terbaru pada beranda dan tautan statistik pada navigasi publik.
- Hanya prestasi berstatus `terverifikasi` dari cabor berstatus `aktif` yang masuk statistik publik.
- Penambahan pilihan jenis medali pada formulir unggah prestasi. Jenis medali bersifat opsional.

## Catatan data lama

Migration menambahkan kolom nullable `jenis_medali`. Data prestasi yang sudah ada tetap aman dan tidak diubah. Karena data lama belum tentu menyimpan klasifikasi medali secara terstruktur, jenis medali tidak ditebak dari teks hasil/peringkat. Data lama tetap masuk hitungan total prestasi jika sudah terverifikasi, tetapi baru masuk hitungan medali setelah klasifikasinya diisi secara valid.

## Cara memasang

Jalankan dari folder proyek `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-06.zip -d .
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test npm run build
docker compose exec laravel.test php artisan test
```

Jangan menjalankan `migrate:fresh`. Patch ini hanya menambahkan satu kolom baru dan tidak menghapus tabel atau data yang sudah ada.

## Target pengujian

60 pengujian sebelumnya + 5 pengujian statistik baru = 65 pengujian.

Pengujian otomatis harus dijalankan di Docker proyek pengguna. Pada saat pembuatan patch ini, pemeriksaan sintaks PHP untuk semua file PHP yang berubah telah lolos; suite Laravel belum dijalankan karena folder `vendor` proyek tidak tersedia di lingkungan penyusunan patch.
