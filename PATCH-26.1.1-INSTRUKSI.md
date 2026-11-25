# PATCH 26.1.1 - Hotfix Redirect Notifikasi Pesan

Patch ini dipasang SETELAH PATCH 26.1.

```bash
cd ~/koni-klaten
unzip -o PATCH-26.1.1-HOTFIX-MESSAGE-NOTIFICATION-REDIRECT.zip -d .
docker compose exec laravel.test php artisan optimize:clear
```

Tidak perlu menjalankan migration.

Uji ulang:

```bash
docker compose exec laravel.test php artisan test --filter=SuratMessagingTest
docker compose exec laravel.test php artisan test --filter=SuratWorkflowTest
docker compose exec laravel.test php artisan test --filter=WorkflowNotificationsTest
docker compose exec laravel.test php artisan test --filter=LeadershipRolesTest
```

Target:
- SuratMessagingTest: 2 passed
- SuratWorkflowTest: 5 passed
- WorkflowNotificationsTest: 5 passed
- LeadershipRolesTest: 4 passed

Jika semua lulus, jalankan seluruh test suite:

```bash
docker compose exec laravel.test php artisan test
```

Tidak ada migration baru. Jangan jalankan migrate:fresh dan jangan hapus volume/database.
