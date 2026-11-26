PATCH WEBSITE PUBLIK KONI KLATEN

Basis wajib: koni-klaten-main(1).zip (versi yang sudah memuat hotfix notifikasi).

Perubahan:
- Menyelaraskan hero beranda dengan referensi visual ZIP klien: charcoal gradient, radial accent merah, dan aksen merah pada kartu portal.
- Warna tetap mengikuti konfigurasi Website Appearance melalui CSS variables.
- Tidak menghapus managed sections, editor tampilan website, routing, controller, maupun data dinamis.

Pemasangan:
1. Backup resources/views/public/home.blade.php.
2. Ekstrak ZIP ini ke root proyek Laravel.
3. Jalankan: docker compose exec laravel.test php artisan view:clear
4. Periksa beranda desktop dan mobile.
5. Uji editor Tampilan Website: ubah warna aksen, warna hero, tombol, dan preview. Pastikan nilai konfigurasi tetap diterapkan.

Catatan:
- Tidak ada migration.
- Tidak mengubah alur notifikasi atau persuratan.
- Syntax PHP pada file aplikasi dan route sudah diperiksa dengan php -l.
- Blade render test dan browser visual test belum dapat dijalankan di lingkungan ini karena Docker tidak tersedia. Verifikasi pada lingkungan proyek sebelum deploy.
