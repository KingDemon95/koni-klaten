<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Support\Backups\DatabaseBackupManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Str;
use Tests\TestCase;

class DatabaseBackupTest extends TestCase
{
    use RefreshDatabase;

    private string $backupDirectory;

    private string $dumpBinary;

    private string $mysqlBinary;

    private string $capturePath;

    protected function setUp(): void
    {
        parent::setUp();

        $this->backupDirectory = storage_path('framework/testing/koni-backups-'.Str::random(8));
        $this->capturePath = storage_path('framework/testing/koni-restore-capture-'.Str::random(8).'.sql');

        File::ensureDirectoryExists(dirname($this->backupDirectory));
        $this->dumpBinary = $this->makeExecutable(<<<'PHP'
#!/usr/bin/env php
<?php
$result = null;
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--result-file=')) {
        $result = substr($argument, 14);
    }
}
if (! is_string($result) || $result === '') {
    fwrite(STDERR, "Missing result file\n");
    exit(2);
}
$sql = "-- KONI Klaten backup fixture\nCREATE TABLE `backup_fixture` (`id` int);\nINSERT INTO `backup_fixture` VALUES (1);\n";
if (file_put_contents($result, $sql) === false) {
    exit(3);
}
PHP);

        $capture = var_export($this->capturePath, true);
        $this->mysqlBinary = $this->makeExecutable(<<<PHP
#!/usr/bin/env php
<?php
file_put_contents({$capture}, stream_get_contents(STDIN));
PHP);

        config([
            'database.connections.mysql' => [
                'driver' => 'mysql',
                'host' => '127.0.0.1',
                'port' => 3306,
                'database' => 'koni_test',
                'username' => 'koni_test_user',
                'password' => 'test-password',
                'unix_socket' => '',
            ],
            'koni-backups.connection' => 'mysql',
            'koni-backups.directory' => $this->backupDirectory,
            'koni-backups.mysqldump_binary' => $this->dumpBinary,
            'koni-backups.mysql_binary' => $this->mysqlBinary,
            'koni-backups.process_timeout' => 30,
            'koni-backups.retention_days' => 30,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDirectory);
        @unlink($this->dumpBinary);
        @unlink($this->mysqlBinary);
        @unlink($this->capturePath);

        parent::tearDown();
    }

    public function test_backup_creates_private_sql_manifest_and_audit_event(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);

        $files = glob($this->backupDirectory.'/koni-db-*.sql') ?: [];
        $this->assertCount(1, $files);

        $path = $files[0];
        $filename = basename($path);
        $manifest = json_decode((string) file_get_contents($path.'.json'), true, 512, JSON_THROW_ON_ERROR);

        $this->assertFileExists($path);
        $this->assertSame(0600, fileperms($path) & 0777);
        $this->assertSame(0600, fileperms($path.'.json') & 0777);
        $this->assertSame($filename, $manifest['file']);
        $this->assertSame(hash_file('sha256', $path), $manifest['sha256']);
        $this->assertFalse(str_starts_with($path, public_path()));
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'backup_created',
            'actor_name' => 'System Backup',
            'subject_label' => $filename,
        ]);
        $this->assertSame(
            [],
            glob(storage_path('framework/cache/koni-db-client-*')) ?: [],
            'File kredensial sementara harus dihapus setelah proses backup.'
        );
    }

    public function test_backup_rejects_a_non_mysql_connection(): void
    {
        config(['koni-backups.connection' => null, 'database.default' => 'sqlite']);

        $this->artisan('koni:backup')->assertExitCode(1);

        $this->assertSame([], glob($this->backupDirectory.'/koni-db-*.sql') ?: []);
    }

    public function test_verify_detects_a_modified_backup_file(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);

        $path = (glob($this->backupDirectory.'/koni-db-*.sql') ?: [])[0];
        file_put_contents($path, "\n-- tampered\n", FILE_APPEND);

        $this->artisan('koni:backup:verify', ['backup' => basename($path)])
            ->expectsOutputToContain('Integritas backup gagal')
            ->assertExitCode(1);
    }

    public function test_retention_removes_only_backups_older_than_the_configured_period(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);

        $oldPath = (glob($this->backupDirectory.'/koni-db-*.sql') ?: [])[0];
        touch($oldPath, now()->subDays(10)->getTimestamp());
        touch($oldPath.'.json', now()->subDays(10)->getTimestamp());

        $this->artisan('koni:backup', ['--retention' => 7])->assertExitCode(0);

        $this->assertFileDoesNotExist($oldPath);
        $this->assertFileDoesNotExist($oldPath.'.json');
        $this->assertCount(1, glob($this->backupDirectory.'/koni-db-*.sql') ?: []);
        $this->assertDatabaseHas('audit_logs', ['event' => 'backup_pruned']);
    }

    public function test_restore_rejects_path_traversal_and_unknown_file_names(): void
    {
        $this->artisan('koni:backup:restore', [
            'backup' => '../.env',
            '--force' => true,
        ])->assertExitCode(1);

        $this->assertFileDoesNotExist($this->capturePath);
    }

    public function test_restore_requires_confirmation_and_cancel_leaves_database_untouched(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);
        $path = (glob($this->backupDirectory.'/koni-db-*.sql') ?: [])[0];

        $this->artisan('koni:backup:restore', ['backup' => basename($path)])
            ->expectsConfirmation('Lanjutkan pemulihan database?', 'no')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($this->capturePath);
        $this->assertDatabaseMissing('audit_logs', ['event' => 'backup_restored']);
    }

    public function test_production_restore_requires_an_explicit_force_option(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);
        $path = (glob($this->backupDirectory.'/koni-db-*.sql') ?: [])[0];
        $this->app['env'] = 'production';

        $this->artisan('koni:backup:restore', ['backup' => basename($path)])
            ->expectsOutputToContain('wajib menggunakan opsi --force')
            ->assertExitCode(1);

        $this->assertFileDoesNotExist($this->capturePath);
    }

    public function test_restore_executes_only_a_verified_backup_and_records_the_event(): void
    {
        $this->artisan('koni:backup')->assertExitCode(0);
        $path = (glob($this->backupDirectory.'/koni-db-*.sql') ?: [])[0];

        $this->artisan('koni:backup:restore', [
            'backup' => basename($path),
            '--force' => true,
        ])->assertExitCode(0);

        $this->assertFileExists($this->capturePath);
        $this->assertStringContainsString('CREATE TABLE `backup_fixture`', file_get_contents($this->capturePath));
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'backup_restored',
            'subject_label' => basename($path),
        ]);

        file_put_contents($path, "\n-- tampered after restore\n", FILE_APPEND);
        @unlink($this->capturePath);

        $this->artisan('koni:backup:restore', [
            'backup' => basename($path),
            '--force' => true,
        ])->assertExitCode(1);

        $this->assertFileDoesNotExist($this->capturePath);
    }

    public function test_scheduler_registers_daily_backup_and_weekly_integrity_check(): void
    {
        $events = app(Schedule::class)->events();
        $commands = collect($events)->map(fn ($event): string => (string) ($event->command ?? ''));

        $this->assertTrue($commands->contains(fn (string $command): bool => str_contains($command, 'koni:backup')));
        $this->assertTrue($commands->contains(fn (string $command): bool => str_contains($command, 'koni:backup:verify')));
    }

    private function makeExecutable(string $contents): string
    {
        $path = storage_path('framework/testing/koni-fake-'.Str::random(10));

        file_put_contents($path, $contents);
        chmod($path, 0700);

        return $path;
    }
}
