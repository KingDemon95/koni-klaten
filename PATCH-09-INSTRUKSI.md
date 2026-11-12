# Patch 09 — Integrasi dan Pengujian Keamanan Seluruh Role

Patch ini merupakan tahap integrasi setelah modul Public, Agenda, Persuratan, Portal Atlet/Pelatih, Statistik, Publikasi, dan Admin KONI selesai dikembangkan.

## Cakupan pengujian tambahan

1. Pengalihan `/dashboard` untuk Admin KONI, Staff KONI, Admin Cabor, Atlet, Pelatih, serta role gabungan lama `atlet_pelatih`.
2. Halaman publik dapat diakses tamu, sedangkan panel pengelolaan tetap memerlukan autentikasi.
3. Admin KONI dapat memantau prestasi, tetapi tidak dapat mengambil alih kewenangan verifikasi Admin Cabor.
4. Admin Cabor tidak dapat mengunduh bukti prestasi atau dokumen izin latihan dari cabang olahraga lain.
5. Atlet tidak dapat mengunduh bukti prestasi milik atlet lain.
6. Media publikasi draft atau publikasi yang belum memiliki waktu publikasi tidak dapat diakses melalui route media publik.
7. Akun nonaktif tidak dapat melanjutkan akses ke panel terautentikasi.

## Pemasangan

Jalankan dari root proyek `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-09.zip -d .
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test php artisan test
```

Patch ini hanya menambahkan satu file pengujian. Tidak ada migration, perubahan `.env`, perubahan data, atau kebutuhan build asset.

## Target

77 pengujian sebelumnya + 7 pengujian integrasi/keamanan = **84 tests passed**.

Jalankan seluruh suite di Docker proyek. Jangan gunakan `migrate:fresh`.
