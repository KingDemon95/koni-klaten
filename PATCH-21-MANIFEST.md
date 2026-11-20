# Patch 21 Manifest

## Tujuan
Menambahkan konfigurasi produksi dan pemeriksaan kesiapan deployment tanpa mengubah stack pengembangan lokal atau database aplikasi.

## File baru
- `.dockerignore`
- `.env.production.example`
- `Dockerfile.production`
- `compose.production.yaml`
- `app/Console/Commands/CheckProductionReadiness.php`
- `docker/production/Caddyfile`
- `docker/production/entrypoint.sh`
- `docker/production/nginx.conf`
- `docker/production/php-fpm.conf`
- `docker/production/php.ini`
- `docs/DEPLOYMENT-PRODUCTION.md`
- `tests/Feature/ProductionDeploymentTest.php`

## Perubahan database
Tidak ada migration.

## Perubahan environment lokal
Tidak ada. Jangan menimpa `.env` lokal.

## Perintah baru
`php artisan koni:production-check`

## Deployment
Gunakan `compose.production.yaml` secara eksplisit bersama `--env-file .env.production`. Jangan gunakan file ini sebelum domain dan VPS siap.

## Validasi paket
Sintaks PHP, YAML, shell, struktur Docker, dan integritas ZIP perlu diperiksa. Pengujian PHPUnit dan Docker image build tetap harus dijalankan pada mesin pengembang atau VPS yang memiliki Docker dan dependency proyek.
