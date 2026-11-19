# PATCH 16.2 - Penyelarasan Pengujian Hak Akses Staff Inventaris

Patch ini memperbaiki konflik pada `StaffSecurityTest` setelah Patch 16 menambahkan transaksi inventaris untuk Staff KONI.

## Penyebab

Pengujian Staff lama melarang seluruh metode perubahan data pada route `staff/*`, kecuali agenda, persuratan, template surat, dan publikasi. Patch 16 secara sengaja memberi Staff kewenangan operasional terbatas untuk inventaris pusat, yaitu mutasi lokasi, peminjaman, dan pengembalian. Karena pengujian lama belum memasukkan tiga route tersebut sebagai pengecualian, pengujian gagal meskipun kewenangan tersebut sesuai spesifikasi Patch 16.

## Perubahan

- Mengizinkan hanya route bernama `staff.inventaris.mutasi`, `staff.inventaris.peminjaman.store`, dan `staff.inventaris.peminjaman.return` dalam pemeriksaan route write Staff.
- Memastikan ketiga route tersebut tetap menggunakan metode `POST`.
- Tetap memeriksa bahwa route perubahan data Staff lain di luar modul operasional yang sudah disetujui tidak tersedia.
- Tidak mengubah route aplikasi, controller, policy, middleware, database, atau data pengguna.
- Tidak memerlukan migrasi database.

## Cara memasang

Ekstrak `koni-klaten-patch-16.2.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa. Patch hanya mengganti file pengujian.

Jalankan pengujian terkait:

```bash
docker compose exec laravel.test php artisan test --filter=StaffSecurityTest
```

Kemudian jalankan pengujian keamanan inventaris:

```bash
docker compose exec laravel.test php artisan test --filter=InventorySecurityTest
```

Jika keduanya lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```
