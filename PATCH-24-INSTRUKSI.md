# PATCH 24: Website Visual & Content Editor

Patch ini memperluas menu **Admin KONI > Tampilan Website Publik** menjadi editor tampilan dan konten per halaman.

## Fitur

- Backdrop terpisah untuk Beranda, daftar dan detail Cabor, Struktur Organisasi, daftar dan detail Agenda, Statistik, daftar dan detail Berita, serta daftar dan detail Dokumentasi.
- Warna banner, judul, deskripsi, label, latar konten, judul konten, aksen, dan tombol untuk setiap halaman.
- Pengubahan judul, deskripsi, label, dan teks tombol pada setiap bagian halaman.
- Pengaturan tampil/sembunyi dan urutan bagian dengan tombol panah.
- Pengaturan teks navigasi dan footer yang berlaku untuk seluruh website publik.
- Simpan draft, preview draft, lalu publikasikan seluruh draft.
- Dashboard internal tetap menggunakan pengaturan dashboard internal dan tidak berubah.

Nama atlet, nama cabang olahraga, berita, agenda, prestasi, dan data dinamis lain tetap berasal dari modul data masing-masing. Pada halaman detail, kosongkan judul/deskripsi pengganti agar sistem memakai data asli.

## Cara memasang

Jalankan dari WSL pada folder project `~/koni-klaten`.

```bash
cd ~/koni-klaten

# Buat backup file sebelum mengganti kode.
tar -czf ~/koni-klaten-before-patch24.tar.gz \
  app/Models/WebsiteAppearanceSetting.php \
  app/Http/Controllers/Admin/WebsiteAppearanceController.php \
  app/Providers/AppServiceProvider.php \
  routes/web.php \
  resources/views/admin/website-appearance \
  resources/views/layouts/public.blade.php \
  resources/views/public \
  tests/Feature/WebsiteAppearanceTest.php

# Ekstrak isi ZIP patch ke root project.
unzip -o ~/Downloads/PATCH-24-WEBSITE-VISUAL-CONTENT-EDITOR.zip -d .

# Bersihkan cache Laravel.
docker compose exec laravel.test php artisan optimize:clear

# Jalankan tes khusus editor website.
docker compose exec laravel.test php artisan test --filter=WebsiteAppearanceTest
```

Tidak ada perubahan skema database dan tidak perlu menjalankan migration baru. Patch menggunakan kolom JSON pengaturan website yang sudah tersedia. Patch juga tidak menambah asset CSS/JS terpisah, sehingga `npm run build` tidak diperlukan.

Setelah tes khusus lulus, jalankan seluruh regression test:

```bash
docker compose exec laravel.test php artisan test
```

## Cara menggunakan

1. Login sebagai Admin KONI.
2. Buka menu **Tampilan Website Publik**.
3. Pilih halaman yang ingin diedit dari daftar halaman.
4. Atur backdrop dan warna halaman tersebut.
5. Buka bagian yang ingin diedit. Ubah tulisan, tombol, visibilitas, atau urutannya.
6. Klik **Simpan Draft**.
7. Klik **Preview draft halaman** untuk memeriksa hasilnya.
8. Jika sudah benar, klik **Publikasikan Semua Draft**.

Pengaturan **Tema umum** dan teks navigasi/footer berlaku untuk semua halaman publik. Pengaturan backdrop, warna halaman, dan bagian konten berlaku hanya untuk halaman yang sedang dipilih.

Untuk halaman detail Cabor, Agenda, Berita, dan Dokumentasi, preview menggunakan data publik pertama yang tersedia. Jika belum ada data publik, sistem mengarahkan preview ke halaman daftar terkait.

## Catatan pengujian

Pemeriksaan sintaks PHP untuk seluruh file patch lulus. PHPUnit belum dapat dijalankan di lingkungan pembuat patch karena lingkungan tersebut tidak memiliki Docker, Composer, maupun folder `vendor`. Jalankan tes melalui container Laravel milik project setelah pemasangan.
