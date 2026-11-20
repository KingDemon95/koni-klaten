<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class ProductionDeploymentTest extends TestCase
{
    public function test_production_readiness_command_is_registered_and_rejects_testing_environment(): void
    {
        $this->artisan('koni:production-check')
            ->expectsOutputToContain('APP_ENV harus production.')
            ->assertExitCode(1);
    }

    public function test_production_readiness_command_detects_debug_and_insecure_cookie_settings(): void
    {
        config([
            'app.debug' => true,
            'app.url' => 'http://localhost',
            'session.secure' => false,
            'database.connections.mysql.password' => 'GANTI_DENGAN_PASSWORD_DATABASE_UNIK',
            'database.redis.default.password' => 'GANTI_DENGAN_PASSWORD_REDIS_UNIK',
        ]);

        $this->artisan('koni:production-check')
            ->expectsOutputToContain('APP_DEBUG harus false.')
            ->expectsOutputToContain('APP_URL harus menggunakan HTTPS.')
            ->expectsOutputToContain('APP_DOMAIN harus sama dengan host APP_URL dan bukan domain contoh.')
            ->expectsOutputToContain('SESSION_SECURE_COOKIE harus true.')
            ->expectsOutputToContain('DB_PASSWORD, DB_ROOT_PASSWORD, dan REDIS_PASSWORD harus memakai secret unik minimal 24 karakter.')
            ->assertExitCode(1);
    }

    public function test_production_readiness_output_never_displays_application_or_database_secrets(): void
    {
        config([
            'app.key' => 'base64:do-not-print-this-secret-key-value',
            'database.connections.mysql.password' => 'do-not-print-database-password',
        ]);

        Artisan::call('koni:production-check');
        $output = Artisan::output();

        $this->assertStringNotContainsString('do-not-print-this-secret-key-value', $output);
        $this->assertStringNotContainsString('do-not-print-database-password', $output);
    }

    public function test_production_compose_defines_separate_application_edge_and_background_services(): void
    {
        $compose = file_get_contents(base_path('compose.production.yaml'));

        foreach (['app:', 'nginx:', 'caddy:', 'mysql:', 'redis:', 'scheduler:', 'queue:'] as $service) {
            $this->assertStringContainsString("\n  ".$service, $compose);
        }

        $this->assertStringContainsString('condition: service_healthy', $compose);
        $this->assertStringContainsString("  backend:\n    driver: bridge", $compose);
    }

    public function test_only_caddy_publishes_public_ports_and_application_data_services_remain_private(): void
    {
        $compose = file_get_contents(base_path('compose.production.yaml'));

        foreach (['app', 'nginx', 'mysql', 'redis', 'scheduler', 'queue'] as $service) {
            $matched = preg_match(
                '/^  '.preg_quote($service, '/').':\R(.*?)(?=^  [a-zA-Z0-9_-]+:|\z)/ms',
                $compose,
                $matches
            );

            $this->assertSame(1, $matched, 'Service '.$service.' harus ada pada compose produksi.');
            $this->assertStringNotContainsString('ports:', $matches[1]);
        }

        $caddyStart = strpos($compose, "  caddy:\n");
        $mysqlStart = strpos($compose, "\n  mysql:\n");
        $this->assertNotFalse($caddyStart);
        $this->assertNotFalse($mysqlStart);
        $caddySection = substr($compose, $caddyStart, $mysqlStart - $caddyStart);

        $this->assertStringContainsString('      - "80:80"', $caddySection);
        $this->assertStringContainsString('      - "443:443"', $caddySection);
        $this->assertStringContainsString('      - "443:443/udp"', $caddySection);
    }

    public function test_production_dockerfile_builds_assets_and_installs_matching_mysql_and_redis_clients(): void
    {
        $dockerfile = file_get_contents(base_path('Dockerfile.production'));

        $this->assertStringContainsString('FROM php:8.5-fpm-bookworm AS app', $dockerfile);
        $this->assertStringContainsString('FROM node:22-bookworm-slim AS frontend', $dockerfile);
        $this->assertStringContainsString('npm ci', $dockerfile);
        $this->assertStringContainsString('npm run build', $dockerfile);
        $this->assertStringContainsString('mysql-community-client', $dockerfile);
        $this->assertStringContainsString('pecl install redis', $dockerfile);
        $this->assertStringContainsString('FROM nginx:1.29-alpine AS nginx', $dockerfile);
    }

    public function test_production_environment_example_uses_secure_non_demo_defaults(): void
    {
        $environment = file_get_contents(base_path('.env.production.example'));

        $this->assertStringContainsString('APP_ENV=production', $environment);
        $this->assertStringContainsString('APP_DEBUG=false', $environment);
        $this->assertStringContainsString('SESSION_SECURE_COOKIE=true', $environment);
        $this->assertStringContainsString('SESSION_ENCRYPT=true', $environment);
        $this->assertStringContainsString('QUEUE_CONNECTION=redis', $environment);
        $this->assertStringContainsString('DB_HOST=mysql', $environment);
        $this->assertStringContainsString('REDIS_HOST=redis', $environment);
        $this->assertStringContainsString('APP_KEY=', $environment);
        $this->assertStringNotContainsString('APP_KEY=base64:', $environment);
    }

    public function test_nginx_and_caddy_configuration_proxy_https_and_restrict_php_execution(): void
    {
        $nginx = file_get_contents(base_path('docker/production/nginx.conf'));
        $caddy = file_get_contents(base_path('docker/production/Caddyfile'));

        $this->assertStringContainsString('fastcgi_pass app:9000', $nginx);
        $this->assertStringContainsString('location ~ \\.php$', $nginx);
        $this->assertStringContainsString('return 404', $nginx);
        $this->assertStringContainsString('deny all', $nginx);
        $this->assertStringContainsString('reverse_proxy nginx:8080', $caddy);
        $this->assertStringContainsString('tls {$ACME_EMAIL}', $caddy);
    }

    public function test_entrypoint_does_not_run_migrations_or_seeders_automatically(): void
    {
        $entrypoint = file_get_contents(base_path('docker/production/entrypoint.sh'));
        $dockerignore = file_get_contents(base_path('.dockerignore'));

        $this->assertStringContainsString('gosu www-data', $entrypoint);
        $this->assertStringContainsString('config:cache', $entrypoint);
        $this->assertStringContainsString('view:cache', $entrypoint);
        $this->assertStringContainsString('KONI_SKIP_VIEW_CACHE', $entrypoint);
        $this->assertStringNotContainsString('artisan migrate', $entrypoint);
        $this->assertStringNotContainsString('artisan db:seed', $entrypoint);
        $this->assertStringContainsString('.env*', $dockerignore);
        $this->assertStringContainsString('storage/app', $dockerignore);
        $this->assertStringContainsString('bootstrap/cache', $dockerignore);
        $this->assertStringContainsString('auth.json', $dockerignore);
        $this->assertStringContainsString('*.zip', $dockerignore);
    }

    public function test_production_guide_documents_explicit_backup_migration_and_rollback_steps(): void
    {
        $guide = file_get_contents(base_path('docs/DEPLOYMENT-PRODUCTION.md'));

        $this->assertStringContainsString('APP_KEY', $guide);
        $this->assertStringContainsString('koni:backup', $guide);
        $this->assertStringContainsString('migrate --force', $guide);
        $this->assertStringContainsString('jangan menjalankan `db:seed`', strtolower($guide));
        $this->assertStringContainsString('rollback', strtolower($guide));
    }
}
