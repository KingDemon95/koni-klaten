#!/bin/sh
set -eu

cd /var/www/html

mkdir -p \
    storage/app/private/backups \
    storage/app/public \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

if [ "$(id -u)" -eq 0 ]; then
    storage_owner="$(stat -c '%u' storage 2>/dev/null || echo 0)"
    if [ "$storage_owner" != "33" ]; then
        chown -R www-data:www-data storage
    fi

    for directory in \
        storage \
        storage/app \
        storage/app/private \
        storage/app/private/backups \
        storage/app/public \
        storage/framework \
        storage/framework/cache \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache
    do
        chown www-data:www-data "$directory"
        case "$directory" in
            storage|storage/app|storage/app/public)
                chmod 0755 "$directory"
                ;;
            storage/app/private|storage/app/private/backups)
                chmod 0700 "$directory"
                ;;
            *)
                chmod 0770 "$directory"
                ;;
        esac
    done

    gosu www-data php artisan package:discover --ansi
    gosu www-data php artisan config:cache
    if [ "${KONI_SKIP_VIEW_CACHE:-false}" != "true" ]; then
        gosu www-data php artisan view:cache
    fi

    exec gosu www-data "$@"
fi

exec "$@"
