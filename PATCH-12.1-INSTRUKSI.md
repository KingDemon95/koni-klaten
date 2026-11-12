# PATCH 12.1 — Perbaikan Role Penerima Notifikasi

Patch koreksi untuk KONI Klaten setelah Patch 12.

## Penyebab

Test `PublicStatisticsTest::test_atlet_can_submit_explicit_medal_type_and_invalid_types_are_rejected`
gagal karena test tersebut hanya membuat role `atlet`. Saat pengajuan prestasi memanggil
notifikasi Admin Cabor, pemanggilan scope Spatie `->role('admin_cabor')` melempar
`RoleDoesNotExist` jika role tersebut belum tersedia.

Notifikasi seharusnya tidak menggagalkan penyimpanan prestasi ketika belum ada penerima.

## Perubahan

- `WorkflowNotifier::toRole()` sekarang mencari penerima melalui relasi `roles`
  dan mencocokkan `name` serta `guard_name`.
- Jika role belum dibuat atau belum ada pengguna aktif dengan role tersebut,
  hasil pencarian kosong dan alur utama tetap berjalan.
- Jika role tersedia, notifikasi tetap dikirim seperti sebelumnya.
- Tidak ada perubahan database atau migration.

## Cara memasang

1. Ekstrak `koni-klaten-patch-12.1.zip` ke folder utama `~/koni-klaten`.
2. Izinkan file `app/Support/WorkflowNotifier.php` ditimpa.
3. Jangan mengubah `.env`.

Kemudian jalankan:

```bash
cd ~/koni-klaten
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test --filter=PublicStatisticsTest
```

Jika test tersebut lulus, jalankan seluruh suite:

```bash
docker compose exec laravel.test php artisan test
```

Dengan kondisi suite yang sama seperti laporan sebelumnya, targetnya adalah **107 passed, 0 failed**.

## Catatan

- Patch ini tidak membutuhkan `php artisan migrate`.
- Tidak perlu rebuild Docker.
- Test sebelumnya menggunakan database testing terisolasi. Namun, jika pernah terjadi HTTP 500 pada pengajuan prestasi di database demo/utama, periksa daftar prestasi sebelum mengirim ulang agar tidak membuat entri duplikat.
