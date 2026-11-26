PATCH: PUBLIC BANNER RENDER FIX

Basis:
koni-klaten-main(1).zip

Diagnosis from source:
- WebsiteAppearanceController already stores uploaded banners in the public disk.
- The publish action already copies draft_config into published_config.
- AppServiceProvider already exposes a host-relative /storage/... URL from the published page configuration.
- The public layout already defines the banner URL CSS variable.
- The home hero relied on CSS background-image only. This patch adds an explicit image element in the home hero so the uploaded banner is rendered directly, with the configured overlay above it.
- Adds a regression assertion that the published home banner is rendered as an actual img src.

Files:
- resources/views/public/home.blade.php
- tests/Feature/WebsiteAppearanceTest.php

Apply from WSL Ubuntu, in the Laravel project root:
    unzip -o KONI_KLATEN_PATCH_BANNER_RENDER_FIX.zip -d .
    docker compose exec laravel.test php artisan optimize:clear
    docker compose exec laravel.test php artisan storage:link
    docker compose exec laravel.test php artisan test --filter=WebsiteAppearanceTest

No migration. Do not delete Docker volumes or databases.

After applying:
1. Open Admin KONI > Tampilan Website > Beranda.
2. Upload banner again if the published configuration currently has no backdrop_path.
3. Click Simpan & Publikasikan.
4. Hard refresh the public homepage with Ctrl+Shift+R.

If the image is still missing, inspect:
    docker compose exec laravel.test ls -la public/storage
    docker compose exec laravel.test find storage/app/public/website-backdrops -type f -maxdepth 3

The upload is limited by the controller to 4 MB and JPG/JPEG/PNG/WEBP.
