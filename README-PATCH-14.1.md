PATCH 14.1: MOBILE RESPONSIVE FIX
KONI Kabupaten Klaten

Tujuan
- Memastikan header website publik tidak melebar pada layar HP.
- Memindahkan tombol Portal Anggota ke dalam hamburger menu pada layar di bawah 640px.
- Membuat dropdown hamburger tetap berada di dalam batas viewport.
- Menyesuaikan topbar dashboard internal untuk layar HP.
- Membuat QR Code absensi mengikuti lebar container dan tidak melampaui layar.
- Menambahkan regression test untuk navigasi publik dan QR absensi.

File yang diperbarui
- resources/views/layouts/public.blade.php
- resources/views/layouts/admin.blade.php
- resources/views/admin/absensi/show.blade.php
- tests/Feature/MobileResponsiveLayoutTest.php

Pemasangan
1. Buat backup source code terlebih dahulu.
2. Ekstrak ZIP ini di folder proyek ~/koni-klaten.
3. Jalankan:

   cd ~/koni-klaten
   docker compose exec laravel.test php artisan optimize:clear
   docker compose exec laravel.test php artisan test --filter=MobileResponsiveLayoutTest

4. Jika tes baru lulus, jalankan seluruh tes:

   docker compose exec laravel.test php artisan test

Catatan
- Tidak ada migration database.
- Tidak ada perubahan controller, route, token QR, kamera, atau alur check-in.
- Tidak perlu npm run build karena aturan responsif patch ini berada pada CSS inline di Blade.
- Uji visual tetap perlu dilakukan pada HP atau mode perangkat seluler browser, terutama lebar 320px, 360px, 390px, dan 430px.
