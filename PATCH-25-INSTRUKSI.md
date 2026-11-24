PATCH 25: PERBAIKAN EDITOR WEBSITE PUBLIK

Isi patch
1. Tombol "Simpan & Lihat Preview" mengirim dan menyimpan semua perubahan formulir terlebih dahulu, lalu membuka halaman publik dalam mode preview draft.
2. Tombol "Simpan & Publikasikan" menyimpan perubahan terbaru dari formulir yang sama, lalu menerbitkan konfigurasi tersebut. Admin tidak lagi perlu menyimpan draft secara terpisah sebelum preview atau publikasi.
3. Upload gambar sekarang diberi label "Gambar banner judul halaman". Gambar hanya dipasang pada area hero/judul halaman, bukan sebagai background seluruh halaman.
4. URL gambar memakai path /storage relatif terhadap host yang sedang dibuka. Ini menghindari URL gambar mengarah ke APP_URL lama saat demo melalui localhost, LAN, atau Cloudflare Tunnel.
5. Section publik yang menggunakan mx-auto kini memiliki lebar penuh sambil tetap menghormati max-w-* masing-masing. Ini memperbaiki container yang sebelumnya menyusut.
6. Menambahkan regression tests untuk alur simpan-preview, simpan-publikasi, dan lebar section.
7. Dashboard internal tidak disentuh.

Cara menerapkan
Jalankan dari folder repository di WSL:

    cd ~/koni-klaten
    unzip -o PATCH-25-WEBSITE-APPEARANCE-FIX.zip -d .
    docker compose exec laravel.test php artisan optimize:clear
    docker compose exec laravel.test php artisan storage:link
    docker compose exec laravel.test php artisan test --filter=WebsiteAppearanceTest

Tidak ada migration baru dan tidak perlu menghapus volume/database.

Cara menggunakan setelah patch
1. Buka /admin/tampilan-website.
2. Pilih halaman yang memang ingin diubah, misalnya Beranda atau Cabang Olahraga. Setiap halaman menyimpan banner dan warna masing-masing.
3. Pilih gambar pada "Gambar banner judul halaman". Gambar akan terlihat pada pratinjau kecil di panel admin.
4. Atur warna dan tulisan.
5. Klik "Simpan & Lihat Preview". Tombol ini menyimpan seluruh perubahan terbaru sebelum membuka halaman publik dalam mode preview.
6. Jika hasil sudah benar, kembali ke panel admin dan klik "Simpan & Publikasikan". Tombol ini juga menyimpan perubahan formulir terbaru sebelum menerbitkannya.

Catatan
- Gambar banner tampil di area judul/hero halaman yang dipilih. Gambar tidak menjadi background semua isi halaman.
- Jika file gambar tidak tampil, periksa apakah public/storage sudah terhubung. Perintah storage:link di atas membuat symlink jika belum tersedia.
- Patch ini tidak mengubah tampilan dashboard internal.
