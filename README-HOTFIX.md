# KONI Klaten Patch 21 Hotfix 1

## Tujuan

Memperbaiki migration absensi yang gagal pada MySQL karena nama index gabungan otomatis melebihi batas 64 karakter. Migration ini juga dapat melanjutkan kondisi parsial setelah MySQL berhasil membuat tabel tetapi gagal saat menambahkan index.

## Pemasangan

Ekstrak ZIP ini dari root project `~/koni-klaten` agar file migration lama tertimpa:

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-21-hotfix-1.zip -d .
```

Jalankan kembali migration yang belum tercatat:

```bash
docker compose exec laravel.test php artisan migrate --force
```

Migration memeriksa keberadaan tabel terlebih dahulu, sehingga tabel `attendance_sessions` dan `attendance_records` yang sudah terbentuk pada percobaan sebelumnya tidak dibuat ulang. Migration menambahkan index dengan nama pendek `att_rec_session_cabor_idx`.

## Verifikasi

```bash
docker compose exec laravel.test php artisan migrate:status

docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Jangan menghapus database, volume MySQL, atau tabel absensi untuk memasang hotfix ini.
