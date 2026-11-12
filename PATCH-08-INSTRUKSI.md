# Patch 08 — Penyempurnaan Admin KONI

Patch ini melanjutkan proyek KONI Klaten setelah Patch 07.

## Isi perubahan

1. Dashboard Admin KONI sekarang mengambil ringkasan langsung dari database: cabor, atlet, pelatih, akun, prestasi terverifikasi, agenda, surat, dan publikasi.
2. Dashboard menampilkan ringkasan antrean pekerjaan lintas modul, komposisi akun berdasarkan role, aktivitas persuratan terbaru, dan konten terakhir diperbarui.
3. Menambahkan halaman Monitoring Prestasi untuk Admin KONI agar dapat melihat data semua cabor, memfilter status/cabor, dan mengunduh bukti secara privat. Keputusan verifikasi tetap menjadi kewenangan Admin Cabor.
4. Mengganti halaman placeholder Pengaturan menjadi formulir Profil KONI: identitas, slogan, deskripsi, sejarah, visi, misi, logo, alamat, kontak, dan media sosial.
5. Informasi Profil KONI digunakan di identitas, bagian profil, dan footer website publik.
6. Menambahkan pengujian dashboard, profil, monitoring prestasi, dan keamanan akses.

## Cara memasang

Jalankan dari folder proyek `~/koni-klaten`:

```bash
unzip -o koni-klaten-patch-08.zip -d .
docker compose exec laravel.test php artisan migrate
docker compose exec laravel.test php artisan optimize:clear
docker compose exec laravel.test npm run build
docker compose exec laravel.test php artisan test
```

Jika `public/storage` belum menjadi symbolic link, jalankan sekali:

```bash
docker compose exec laravel.test php artisan storage:link
```

## Target pengujian

Patch ini menambahkan 6 pengujian baru. Dengan 71 pengujian dari tahap sebelumnya, target total adalah **77 tests passed**.

## Catatan keamanan dan data

- Jangan jalankan `migrate:fresh`.
- Migration hanya menambahkan tabel `profil_koni` dan satu baris profil awal; data modul lain tidak dihapus.
- Upload logo memakai public disk karena logo memang ditampilkan pada website publik.
- Bukti prestasi tetap berada di local/private storage dan hanya dapat diunduh melalui route Admin KONI yang terlindungi autentikasi/role.
- Admin KONI hanya memantau status prestasi. Verifikasi publikasi prestasi tetap dilakukan Admin Cabor sesuai kesepakatan.
