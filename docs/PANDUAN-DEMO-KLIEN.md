# Panduan demo website KONI Klaten

## Persiapan (sebelum presentasi)

1. Pastikan `docker compose ps` menampilkan Laravel, MySQL, dan Redis dalam keadaan berjalan. Redis dan MySQL **tidak** boleh dipublikasikan ke `0.0.0.0`.
2. Jalankan `docker compose exec laravel.test php artisan koni:demo-check`.
3. Jika build Vite belum ada atau CSS berubah, jalankan `docker compose exec laravel.test npm run build`.
4. Pastikan `APP_DEBUG=false`. Jangan pernah mengirim `.env`, `APP_KEY`, password asli, atau database asli kepada klien.
5. Gunakan **data fiktif** untuk atlet, pelatih, pengurus, surat, prestasi, dan izin latihan. Akun demo harus terpisah dari akun nyata. Password bersama pada Patch 11 hanya untuk demonstrasi dan tidak boleh dipakai dalam produksi.
6. Gunakan Cloudflare Quick Tunnel hanya sebagai *demo sementara*. Link berubah saat tunnel dimulai ulang; jangan menganggapnya URL produksi permanen.
7. Periksa halaman beranda, agenda, berita, dokumentasi, struktur, statistik, dan semua navigasi di ponsel.

## Alur presentasi yang disarankan

1. **Publik:** buka beranda, daftar cabor, agenda/kejuaraan, berita, dokumentasi, statistik, dan pengurus.
2. **Admin KONI:** tampilkan dashboard, data master, laporan CSV, dan pengaturan profil KONI.
3. **Admin Cabor:** tampilkan cabor sendiri, verifikasi prestasi, keputusan izin latihan, agenda, dan pengajuan surat.
4. **Atlet/Pelatih:** tunjukkan biodata sendiri, pengajuan prestasi, alasan revisi/penolakan, serta izin latihan.
5. **Staff KONI:** tampilkan pengelolaan agenda/publikasi dan alur persuratan sebagai perantara menuju Admin KONI.
6. **Notifikasi:** tunjukkan bahwa status proses muncul di akun yang relevan.

## Pengujian cepat tampilan

- Pada lebar layar ponsel, coba menu navigasi dan gulir sampai bagian bawah setiap halaman.
- Gunakan `Tab` untuk memeriksa fokus tombol/link serta tautan **Langsung ke konten utama**.
- Coba form dengan input yang salah untuk memeriksa pesan error; jangan gunakan data klien asli.
- Pastikan halaman publik tidak menampilkan bukti prestasi yang belum diverifikasi atau dokumen pribadi.

## Sebelum demo dibagikan ke luar

- Nonaktifkan semua akun demo yang tidak diperlukan.
- Pastikan tidak ada data pribadi yang tidak semestinya dipublikasikan.
- Matikan tunnel dengan `Ctrl+C` setelah presentasi.
- Perbarui browser jika alamat tunnel berganti dan periksa bahwa URL CSS/JS menggunakan `https://`.

## Menangani masalah

- `curl -I http://localhost` tidak `200`: periksa `docker compose ps` dan `storage/logs/laravel.log` (jangan kirim log mentah berisi rahasia).
- Cloudflare tampil polos: periksa URL CSS/JS. Aset harus memakai hostname tunnel `https://`, bukan `http://` atau `localhost`.
- `php artisan koni:demo-check` menandai `Asset Vite`: jalankan `npm run build` lalu ulangi pemeriksaan.
- Tidak ada notifikasi: pastikan role penerima sudah dibuat dan pengguna memiliki cabor yang benar.
