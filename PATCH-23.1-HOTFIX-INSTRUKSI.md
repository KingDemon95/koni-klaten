# Patch 23.1 Hotfix: Perbaikan halaman Absensi Saya

## Penyebab
Callback eager loading relasi `records` menerima instance `Illuminate\\Database\\Eloquent\\Relations\\HasMany`, bukan `Illuminate\\Database\\Eloquent\\Builder`. Type hint yang salah memicu HTTP 500 saat membuka `admin.absensi.index`.

## Perbaikan
- Mengganti type hint callback dari `Builder` menjadi `HasMany`.
- Tidak mengubah struktur database, migration, route, atau alur penyimpanan absensi.
- Mempertahankan filter agar halaman Absensi Saya hanya memuat record milik pengguna yang sedang login.

## Pemasangan
Jalankan dari direktori root project `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-23.1-hotfix.zip -d .
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Tidak perlu menjalankan migration atau membangun ulang image Docker.

## Hasil yang diharapkan
Semua test `AttendanceWorkflowTest` lulus, termasuk `internal user sees eligible sessions in absensi saya`.
