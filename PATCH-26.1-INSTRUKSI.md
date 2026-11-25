# PATCH 26.1 - Persuratan dan Pesan

Patch ini menggunakan project `koni-klaten-main.zip` sebagai baseline. Jangan menimpa seluruh project dengan ZIP klien.

## 1. Backup file lama

Jalankan dari root project:

```bash
cd ~/koni-klaten

mkdir -p backup-patch26.1

cp app/Http/Controllers/SuratController.php backup-patch26.1/
cp app/Models/Surat.php backup-patch26.1/
cp routes/web.php backup-patch26.1/
cp resources/views/surat/show.blade.php backup-patch26.1/
cp resources/views/layouts/admin.blade.php backup-patch26.1/
cp resources/views/layouts/cabor.blade.php backup-patch26.1/
cp resources/views/layouts/staff.blade.php backup-patch26.1/
cp resources/views/layouts/sekretaris.blade.php backup-patch26.1/
```

## 2. Ekstrak patch

Letakkan ZIP di `~/koni-klaten`, lalu:

```bash
cd ~/koni-klaten
unzip -o PATCH-26.1-SURAT-MESSAGING-AND-LEADERSHIP-FIX.zip -d .
```

## 3. Jalankan migration

```bash
docker compose exec laravel.test php artisan migrate
```

Migration ini hanya membuat tabel `surat_messages`. Jangan menghapus database atau volume Docker.

## 4. Bersihkan cache

```bash
docker compose exec laravel.test php artisan optimize:clear
```

## 5. Jalankan regression test

```bash
docker compose exec laravel.test php artisan test --filter=SuratMessagingTest

docker compose exec laravel.test php artisan test --filter=SuratWorkflowTest

docker compose exec laravel.test php artisan test --filter=WorkflowNotificationsTest

docker compose exec laravel.test php artisan test --filter=LeadershipRolesTest
```

Jika seluruh test khusus lulus, jalankan:

```bash
docker compose exec laravel.test php artisan test
```

## 6. Uji manual

1. Login sebagai Staff KONI.
2. Buka surat yang sudah mendapat keputusan Ketua atau Sekretaris.
3. Teruskan disposisi kepada Admin Cabor.
4. Login sebagai Admin Cabor dari cabang tersebut.
5. Buka menu Pesan dan periksa notifikasi.
6. Buka percakapan, kirim komentar dan unggah surat balasan.
7. Login kembali sebagai Staff KONI dan periksa balasan di menu Pesan.
8. Login sebagai Ketua dan Sekretaris. Pastikan keduanya dapat mengambil keputusan.
9. Login sebagai Admin KONI. Pastikan Admin dapat memantau surat, tetapi tidak melihat form keputusan dan tidak dapat mengirim keputusan melalui request langsung.

## Catatan
- Patch tidak mengubah modul absensi QR, inventaris, master data, atau editor tampilan website.
- Patch tidak menghapus fitur lama.
- PHP syntax lint telah diperiksa.
- Regression test Laravel belum dijalankan pada Docker lokal pengguna. Jalankan perintah di atas sebelum menganggap patch siap digunakan.
