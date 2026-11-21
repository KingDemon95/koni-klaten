# PATCH 22.1 HOTFIX

Hotfix ini memperbaiki dua kegagalan pengujian setelah Patch 22.

## Perubahan
1. Form absensi menampilkan frasa "Ambil foto selfie" untuk check-in dan check-out.
2. StaffSecurityTest mengizinkan hanya dua endpoint tulis absensi Staff yang memang disediakan:
   - `staff.absensi.store` dengan metode POST
   - `staff.absensi.close` dengan metode POST
   Pengecualian transaksi inventaris yang sudah ada tetap dipertahankan. Route tulis Staff lain tetap ditolak.

## Pemasangan
Ekstrak ZIP dari root project `~/koni-klaten` agar struktur `resources/` dan `tests/` tergabung ke folder yang sesuai.

Tidak ada migration pada hotfix ini. Jangan menjalankan `migrate:fresh` dan tidak perlu mengulang migration Patch 22.

Jalankan:
```bash
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
docker compose exec laravel.test php artisan test --filter=StaffSecurityTest
docker compose exec laravel.test php artisan test
```

Jika kedua pengujian terfokus lulus, jalankan seluruh test suite.
