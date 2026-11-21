# PATCH 22.2 HOTFIX

Memperbaiki label judul formulir absensi agar sesuai dengan role dan jenis transaksi.

- Atlet dan role selain Staff tetap melihat `Ambil foto selfie untuk check-in/check-out`.
- Staff KONI melihat `Ambil selfie check-in` atau `Ambil selfie check-out`.
- Tidak mengubah controller, validasi selfie, penyimpanan, role, route, maupun database.
- Tidak ada migration.

## Pemasangan

Ekstrak ZIP dari root project `~/koni-klaten` agar file `resources/` tergabung ke direktori yang sesuai.

Jalankan:

```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
docker compose exec laravel.test php artisan test --filter=StaffSecurityTest
```

Jika keduanya lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

Tidak perlu menjalankan migration atau menghapus volume/database.
