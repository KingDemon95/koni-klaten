# PATCH 23.2.2 HOTFIX: Hentikan Refresh QR Berulang

## Penyebab

Halaman Absensi Saya dan Detail Sesi menghitung waktu refresh dengan urutan `diffInMilliseconds()` terbalik. Pada Carbon 3, selisih bertanda dapat bernilai negatif. JavaScript memperlakukan nilai negatif pada `setTimeout()` sebagai nol, sehingga halaman langsung reload berulang kali.

## Perbaikan

- Menghitung selisih dari waktu sekarang menuju awal hari berikutnya, sehingga nilainya positif.
- Memberi batas minimum 1 detik sebagai pengaman.
- Mempertahankan refresh otomatis hanya sekali saat pergantian hari agar QR harian diperbarui.
- Menambahkan regression test untuk halaman Absensi Saya dan Detail Sesi.
- Tidak mengubah alur unduh QR, pemindaian dari galeri, kamera selfie, check-in, atau check-out.

## Cara memasang

Pastikan Patch 23.2 dan Hotfix 23.2.1 sudah terpasang. Ekstrak ZIP ini ke root project `~/koni-klaten`.

```bash
cd ~/koni-klaten
unzip -o koni-klaten-patch-23.2.2-hotfix.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Jalankan tes:

```bash
docker compose exec laravel.test php artisan test --filter=AttendanceWorkflowTest
```

Lalu jalankan seluruh tes:

```bash
docker compose exec laravel.test php artisan test
```

## Catatan

- Tidak ada migration database.
- Tidak perlu rebuild Docker.
- Tidak perlu menjalankan `npm run build`.
- Tidak mengubah data absensi atau token harian.
