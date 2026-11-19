# PATCH 16.1 - Perbaikan Pengujian Keamanan Inventaris

Patch ini memperbaiki satu ekspektasi pada `InventorySecurityTest`.

## Penyebab

Sekretaris memiliki route GET untuk melihat detail inventaris. Route PUT untuk URI detail yang sama tidak didaftarkan. Laravel mengembalikan HTTP 405 Method Not Allowed ketika klien mengirim PUT ke URI yang hanya mendukung GET. Respons ini menolak perubahan data dan merupakan perilaku routing yang benar.

Pengujian sebelumnya mengharapkan 404, sehingga gagal meskipun route PUT tidak tersedia.

## Perubahan

- Mengubah ekspektasi untuk permintaan PUT Sekretaris dari HTTP 404 menjadi HTTP 405.
- Menambahkan pemeriksaan bahwa nama barang tetap tidak berubah setelah permintaan tersebut.
- Tidak mengubah route, controller, policy, database, atau data aplikasi.
- Tidak memerlukan migrasi.

## Cara memasang

Ekstrak `koni-klaten-patch-16.1.zip` ke folder utama `~/koni-klaten` dan izinkan file yang sama ditimpa. Patch hanya mengganti file pengujian.

Jalankan:

```bash
docker compose exec laravel.test php artisan test --filter=InventorySecurityTest
```

Jika lulus, lanjutkan dengan:

```bash
docker compose exec laravel.test php artisan test
```
