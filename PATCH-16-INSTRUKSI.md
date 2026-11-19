# PATCH 16 - Inventaris KONI dan Cabang Olahraga

Patch ini menambahkan modul inventaris untuk mencatat aset KONI Kabupaten Klaten dan aset setiap Cabang Olahraga. Patch mempertahankan fitur dan data dari Patch 1 sampai Patch 15.

## Fitur

1. Pencatatan barang dengan kode otomatis, nama, kategori, jumlah, satuan, kondisi, lokasi, tanggal perolehan, harga perolehan, status, dan keterangan.
2. Kategori inventaris dinamis. Admin KONI dan Ketua dapat mengelola kategori umum dan kategori per Cabor. Admin Cabor dapat mengelola kategori khusus cabangnya dan menggunakan kategori umum.
3. Pemisahan inventaris pusat KONI dan inventaris masing-masing Cabor.
4. Riwayat mutasi lokasi yang mencatat lokasi asal, lokasi tujuan, jumlah, waktu, petugas, dan catatan.
5. Peminjaman dan pengembalian barang. Sistem menghitung stok tersedia dan menolak jumlah pinjaman yang melebihi stok.
6. Laporan inventaris berdasarkan kondisi, kategori, dan pemilik. Laporan dapat diekspor ke CSV.
7. Barang yang diarsipkan tidak menghapus riwayat transaksi. Sistem menolak pengarsipan barang yang masih memiliki peminjaman aktif.

## Hak akses

- Admin KONI: melihat dan mengelola seluruh inventaris, kategori, mutasi, peminjaman, pengembalian, dan laporan.
- Ketua KONI: memiliki kewenangan pengelolaan inventaris yang sama dengan Admin KONI.
- Admin Cabor: mengelola inventaris dan kategori khusus cabangnya. Admin Cabor tidak dapat melihat atau mengubah inventaris Cabor lain maupun inventaris pusat KONI.
- Sekretaris KONI: dapat melihat seluruh inventaris, kategori, dan laporan tanpa mengubah data atau transaksi.
- Staff KONI: dapat melihat inventaris pusat KONI dan mencatat mutasi, peminjaman, serta pengembalian barang pusat. Staff tidak dapat mengakses inventaris Cabor.
- Atlet dan Pelatih: tidak memiliki akses ke modul inventaris.

## Cara memasang

Jalankan dari terminal WSL Ubuntu pada folder proyek.

1. Buat cadangan source code dan database.
2. Ekstrak `koni-klaten-patch-16.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa.
3. Jangan menimpa `.env`. ZIP patch tidak berisi `.env`, database, `vendor`, atau `node_modules`.
4. Jalankan migrasi:

```bash
cd ~/koni-klaten
docker compose exec laravel.test php artisan migrate --force
docker compose exec laravel.test php artisan optimize:clear
```

5. Bangun ulang asset frontend. Karena terminal WSL sebelumnya pernah memanggil npm Windows, gunakan npm dari container jika npm lokal masih bermasalah:

```bash
docker compose exec laravel.test sh -lc 'cd /var/www/html && npm run build'
```

6. Jalankan pengujian terarah:

```bash
docker compose exec laravel.test php artisan test --filter=InventoryManagementTest
docker compose exec laravel.test php artisan test --filter=InventorySecurityTest
docker compose exec laravel.test php artisan test --filter=DatabaseMigrationTest
```

7. Jika semua pengujian terarah lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

## Pengujian manual

1. Login sebagai Admin KONI. Buka Inventaris, buat kategori umum, lalu tambahkan barang milik KONI. Pastikan kode barang dibuat otomatis.
2. Login sebagai Admin KONI atau Ketua. Tambahkan barang untuk satu Cabor dan pastikan data tersebut muncul pada filter pemilik.
3. Login sebagai Admin Cabor. Pastikan daftar hanya menampilkan barang milik cabangnya. Buat kategori khusus dan barang baru. Pastikan Admin Cabor tidak dapat membuka detail barang Cabor lain.
4. Catat mutasi lokasi. Pastikan lokasi aktif berubah dan riwayat menyimpan lokasi asal serta tujuan.
5. Catat peminjaman. Pastikan jumlah tersedia berkurang. Coba pinjam melebihi stok dan pastikan sistem menolak.
6. Catat pengembalian. Pastikan jumlah tersedia bertambah kembali, riwayat menyimpan kondisi saat kembali, dan kondisi pada data barang ikut diperbarui.
7. Login sebagai Sekretaris. Pastikan Sekretaris dapat melihat inventaris semua pemilik, kategori, dan laporan, tetapi tidak dapat membuat atau mengubah data.
8. Login sebagai Staff. Pastikan Staff hanya melihat inventaris pusat KONI dan dapat mencatat transaksi barang pusat. Staff tidak dapat membuka inventaris Cabor.
9. Pastikan Atlet dan Pelatih menerima respons 403 jika mencoba membuka panel inventaris.
10. Ekspor laporan CSV dan pastikan file memuat kode, nama, kategori, pemilik, jumlah, kondisi, lokasi, dan status barang.

## Catatan

- Kode barang dibuat otomatis berdasarkan pemilik, tahun, dan ID data.
- Kategori umum dapat digunakan oleh KONI dan Cabor. Kategori khusus hanya dapat digunakan pada Cabor pemiliknya.
- Mutasi dalam patch ini mencatat perpindahan lokasi barang, bukan perpindahan kepemilikan antar-Cabor.
- Peminjaman dicatat per jumlah unit. Sistem menghitung unit yang masih dipinjam sebagai pengurang stok tersedia. Sistem menolak pengurangan jumlah barang di bawah unit yang masih dipinjam dan menolak mutasi lokasi selama masih ada unit yang dipinjam.
- Data inventaris tidak ditampilkan pada portal publik.
