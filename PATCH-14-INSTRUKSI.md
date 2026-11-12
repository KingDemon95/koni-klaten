# PATCH 14 — Pengalaman pengguna & kesiapan demo klien

Patch ini diterapkan setelah Patch 13 untuk Laravel KONI Klaten.

## Perubahan

- Tautan **Langsung ke konten utama** untuk keyboard pada 6 layout.
- Fokus keyboard lebih jelas pada link, tombol, dan input.
- Menu ponsel lebih nyaman digulir pada layar pendek.
- Navigasi publik menandai halaman aktif secara semantik (`aria-current="page"`).
- Ringkasan kesalahan validasi dan pesan error/warning ditampilkan konsisten di kelima portal; pesan sukses halaman yang sudah ada tetap dipertahankan.
- Perintah **`php artisan koni:demo-check`** untuk pengecekan read-only: debug, APP_KEY, build Vite, storage, dan database.
- `docs/PANDUAN-DEMO-KLIEN.md` untuk alur presentasi dan keamanan demo.
- Empat pengujian fitur baru.

## Pemasangan

1. Backup file/kode atau commit ke Git. Simpan backup database sebelum perubahan besar.
2. Ekstrak `koni-klaten-patch-14.zip` ke `~/koni-klaten` (timpa file yang sama; jangan mengubah `.env`).
3. Karena CSS berubah, **harus build frontend**:

```bash
cd ~/koni-klaten
docker compose exec laravel.test npm run build
docker compose exec laravel.test php artisan optimize:clear
```

4. Jalankan tes baru dan tes penuh:

```bash
docker compose exec laravel.test php artisan test --filter=DemoExperienceTest
docker compose exec laravel.test php artisan test
```

Target 116 test passed jika 112 sebelumnya dan keempat tes tambahan lulus.

5. Jalankan pemeriksaan demo:

```bash
docker compose exec laravel.test php artisan koni:demo-check
```

Perintah tidak mengubah database. Jika ada `[PERIKSA]`, selesaikan dahulu sebelum demo publik.

## Catatan

- Tidak ada migration database baru.
- Tidak perlu membuat ulang container.
- Patch tidak menyertakan password, akun demo, database, `.env`, atau dokumen pribadi.
- Testing penuh Laravel tetap dijalankan di Docker pengguna; pemeriksaan sintaks lokal bukan pengganti test.
