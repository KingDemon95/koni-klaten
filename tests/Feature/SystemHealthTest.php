<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\User;
use App\Support\Backups\RemoteBackupManager;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    private string $backupDirectory;
    private string $dumpBinary;
    private string $heartbeatPath;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['admin_koni', 'ketua_koni', 'sekretaris_koni', 'admin_cabor', 'staff_koni', 'atlet', 'pelatih'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->backupDirectory = storage_path('framework/testing/koni-health-backups-'.Str::random(8));
        $this->heartbeatPath = storage_path('framework/testing/koni-health-heartbeat-'.Str::random(8));
        File::ensureDirectoryExists($this->backupDirectory);

        $this->dumpBinary = storage_path('framework/testing/koni-health-dump-'.Str::random(8));
        file_put_contents($this->dumpBinary, <<<'PHP'
#!/usr/bin/env php
<?php
$result = null;
foreach (array_slice($argv, 1) as $argument) {
    if (str_starts_with($argument, '--result-file=')) {
        $result = substr($argument, 14);
    }
}
if (! is_string($result) || $result === '') {
    exit(2);
}
$sql = "-- KONI off-site fixture\nCREATE TABLE `health_fixture` (`id` int);\nINSERT INTO `health_fixture` VALUES (1);\n";
file_put_contents($result, $sql);
PHP);
        chmod($this->dumpBinary, 0700);

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
            'koni-backups.process_timeout' => 30,
            'koni-backups.remote_disk' => null,
            'koni-backups.remote_prefix' => 'test/koni-backups',
            'koni-backups.scheduler_heartbeat_path' => $this->heartbeatPath,
        ]);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->backupDirectory);
        @unlink($this->dumpBinary);
        @unlink($this->heartbeatPath);

        parent::tearDown();
    }

    private function admin(): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole('admin_koni');

        return $user;
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create(['is_active' => true]);
        $user->assignRole($role);

        return $user;
    }

    private function createBackup(): string
    {
        $this->artisan('koni:backup')->assertExitCode(0);
        $files = glob($this->backupDirectory.'/koni-db-*.sql') ?: [];
        $this->assertCount(1, $files);

        return basename($files[0]);
    }

    private function enableFakeRemoteDisk(): void
    {
        Storage::fake('koni_remote_test');
        config(['koni-backups.remote_disk' => 'koni_remote_test']);
    }

    public function test_remote_sync_uploads_backup_and_manifest_then_records_audit_event(): void
    {
        $filename = $this->createBackup();
        $this->enableFakeRemoteDisk();

        $result = app(RemoteBackupManager::class)->sync($filename);

        $remoteBase = 'test/koni-backups/'.$filename;
        Storage::disk('koni_remote_test')->assertExists($remoteBase);
        Storage::disk('koni_remote_test')->assertExists($remoteBase.'.json');
        $this->assertSame($filename, $result['filename']);
        $this->assertDatabaseHas('audit_logs', [
            'event' => 'backup_remote_synced',
            'subject_label' => $filename,
        ]);
    }

    public function test_remote_sync_rejects_path_traversal(): void
    {
        $this->enableFakeRemoteDisk();

        $this->artisan('koni:backup:sync', ['backup' => '../.env'])
            ->assertExitCode(1);

        Storage::disk('koni_remote_test')->assertMissing('test/koni-backups/.env');
    }

    public function test_remote_sync_is_a_safe_no_op_when_off_site_storage_is_not_configured(): void
    {
        config(['koni-backups.remote_disk' => null]);

        $this->artisan('koni:backup:sync')
            ->expectsOutputToContain('belum diaktifkan')
            ->assertExitCode(0);
    }

    public function test_remote_status_detects_missing_and_modified_remote_copies(): void
    {
        $filename = $this->createBackup();
        $this->enableFakeRemoteDisk();
        $manager = app(RemoteBackupManager::class);

        $this->assertSame('warning', $manager->status($filename)['status']);
        $manager->sync($filename);
        $this->assertSame('ok', $manager->status($filename)['status']);

        Storage::disk('koni_remote_test')->put('test/koni-backups/'.$filename, 'tampered');
        $this->assertSame('error', $manager->status($filename)['status']);
    }

    public function test_admin_koni_can_open_system_health_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.system-health'))
            ->assertOk()
            ->assertSeeText('Monitoring kesehatan sistem')
            ->assertSeeText('Database')
            ->assertDontSeeText('test-password');
    }

    public function test_non_admin_roles_cannot_open_system_health_page(): void
    {
        foreach (['ketua_koni', 'sekretaris_koni', 'admin_cabor', 'staff_koni', 'atlet', 'pelatih'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->get('/admin/kesehatan-sistem')
                ->assertForbidden();
        }
    }

    public function test_scheduler_heartbeat_command_writes_a_private_timestamp(): void
    {
        $this->artisan('koni:scheduler-heartbeat')->assertExitCode(0);

        $this->assertFileExists($this->heartbeatPath);
        $this->assertSame(0600, fileperms($this->heartbeatPath) & 0777);
        $this->assertNotSame('', trim((string) file_get_contents($this->heartbeatPath)));
    }

    public function test_scheduler_registers_remote_sync_and_heartbeat_jobs(): void
    {
        $commands = collect(app(Schedule::class)->events())
            ->map(fn ($event): string => (string) ($event->command ?? ''));

        $this->assertTrue($commands->contains(fn (string $command): bool => str_contains($command, 'koni:backup:sync')));
        $this->assertTrue($commands->contains(fn (string $command): bool => str_contains($command, 'koni:scheduler-heartbeat')));
    }

    public function test_audit_log_has_labels_for_off_site_backup_events(): void
    {
        $labels = AuditLog::eventLabels();

        $this->assertSame('Menyinkronkan backup off-site', $labels['backup_remote_synced']);
        $this->assertSame('Gagal menyinkronkan backup off-site', $labels['backup_remote_failed']);
    }
}
