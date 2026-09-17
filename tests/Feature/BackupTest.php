<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\User;
use App\Filament\Pages\Backup;
use App\Services\BackupCatalog;
use App\Services\BackupWorkflow;
use App\Services\BackupOperationLock;
use Filament\Facades\Filament;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use App\Services\BackupService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;
use ZipArchive;

class BackupTest extends TestCase
{
    use DatabaseMigrations;

    private string $originalStorage;
    private string $testStorage;

    protected function setUp(): void
    {
        parent::setUp();
        // Semua file tes ditempatkan dalam folder unik, bukan unggahan pengguna.
        $this->originalStorage = storage_path();
        $this->testStorage = storage_path('app/backups/test-'.Str::uuid());
        app()->useStoragePath($this->testStorage);
        config(['filesystems.disks.public.root' => storage_path('app/public'), 'filesystems.disks.local.root' => storage_path('app/private')]);
        File::ensureDirectoryExists(storage_path('app/public/materi'));
        File::ensureDirectoryExists(storage_path('app/private'));
        File::put(storage_path('app/public/materi/contoh.pdf'), 'fixture upload');
        File::put(storage_path('app/private/.hidden'), 'private fixture');
        Kelas::create(['nama' => 'X Backup', 'tingkat' => 'X']);
    }

    protected function tearDown(): void
    {
        app()->useStoragePath($this->originalStorage);
        File::deleteDirectory($this->testStorage);
        parent::tearDown();
    }

    public function test_round_trip_preserves_data_uploads_and_leaves_active_database_unchanged(): void
    {
        $service = app(BackupService::class);
        $archive = $service->create();
        $result = $service->restoreTest($archive);
        $this->assertSame(1, $result['tables']['kelas']);
        $this->assertSame('fixture upload', File::get($result['directory'].'/uploads/public/materi/contoh.pdf'));
        $this->assertSame('private fixture', File::get($result['directory'].'/uploads/private/.hidden'));
        $this->assertFileDoesNotExist($result['directory'].'/config/.env');
        $this->assertFileExists($result['directory'].'/restore-report.json');
        $this->assertDatabaseHas('kelas', ['nama' => 'X Backup']);
        $this->artisan('backup:restore-test', ['archive' => $archive])->assertSuccessful();
    }

    public function test_corrupted_file_is_rejected_and_test_directory_is_cleaned(): void
    {
        $service = app(BackupService::class);
        $archive = $service->create();
        $zip = new ZipArchive;
        $zip->open($archive);
        $zip->addFromString('uploads/public/materi/contoh.pdf', 'changed upload');
        $zip->close();
        try {
            $service->restoreTest($archive);
            $this->fail('Corruption must fail');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Checksum', $exception->getMessage());
        }
        $this->assertSame([], File::directories(storage_path('app/restore-tests')));
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_path_traversal_in_manifest_is_rejected(): void
    {
        $service = app(BackupService::class);
        $archive = $service->create();
        $zip = new ZipArchive;
        $zip->open($archive);
        $manifest = json_decode($zip->getFromName('manifest.json'), true);
        $name = 'uploads/public/../../../escape.txt';
        $manifest['files'][$name] = ['bytes' => 1, 'sha256' => hash('sha256', 'x')];
        $zip->addFromString($name, 'x');
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->close();
        $this->expectExceptionMessage('Path file backup tidak aman');
        $service->restoreTest($archive);
    }

    public function test_create_command_refuses_without_maintenance(): void
    {
        $this->artisan('backup:create')->assertFailed();
    }

    public function test_snapshot_includes_committed_wal_data(): void
    {
        $connection = DB::connection();
        $original = $connection->getPdo();
        $path = storage_path('wal-test.sqlite');
        $original->exec('VACUUM INTO '.$original->quote($path));
        $wal = new \PDO('sqlite:'.$path);
        $wal->exec('PRAGMA journal_mode=WAL');
        $wal->exec('PRAGMA wal_autocheckpoint=0');
        $wal->exec("INSERT INTO kelas (nama, tingkat) VALUES ('XI WAL', 'XI')");
        $connection->setPdo($wal);
        try {
            $this->assertFileExists($path.'-wal');
            $service = app(BackupService::class);
            $result = $service->restoreTest($service->create());
            $this->assertSame(2, $result['tables']['kelas']);
        } finally {
            $connection->setPdo($original);
            $wal = null;
        }
    }

    public function test_admin_can_list_download_verify_and_inspect_report(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        $archive = app(BackupService::class)->create();
        $name = basename($archive);
        $this->get(route('backup.download', $name))->assertOk()->assertDownload($name);
        $page = Livewire::test(Backup::class)->assertSee($name)->assertSee('Belum diuji');
        $page->mountAction('ujiRestore', ['name' => $name])->callMountedAction()
            ->assertSee('Lulus uji restore');
        $this->assertSame('passed', app(BackupCatalog::class)->report($name)['status']);
        $page->mountAction('hasil', ['name' => $name])->assertActionMounted('hasil');
        $this->assertStringContainsString('Database aktif tidak diganti', $page->instance()->getMountedAction()->getModalContent()->render());
    }

    public function test_download_requires_admin_access_and_rejects_arbitrary_files(): void
    {
        $archive = app(BackupService::class)->create();
        $url = route('backup.download', basename($archive));
        $this->get($url)->assertRedirect(route('filament.admin.auth.login'));
        $this->actingAs(User::factory()->create());
        $this->get(route('backup.download', '.env'))->assertNotFound();
        Gate::define('manage-backups', fn () => false);
        $this->get($url)->assertForbidden();
        $this->assertFalse(Backup::canAccess());
    }

    public function test_catalog_marks_failure_and_invalidates_changed_backup_report(): void
    {
        $archive = app(BackupService::class)->create();
        $catalog = app(BackupCatalog::class);
        $this->assertSame('passed', $catalog->verify(basename($archive))['status']);
        File::put($archive, 'broken');
        $this->assertNull($catalog->report(basename($archive)));
        $this->assertSame('failed', $catalog->verify(basename($archive))['status']);
        $this->assertDatabaseCount('kelas', 1);
    }

    private function withFileDatabase(callable $test): void
    {
        // Ganti hanya koneksi tes dengan file SQLite terisolasi; bukan database pengguna.
        $connectionName = config('database.default');
        $config = config('database.connections.'.$connectionName);
        $original = DB::connection()->getPdo();
        $path = storage_path('active-test.sqlite');
        $original->exec('VACUUM INTO '.$original->quote($path));
        config(['database.connections.'.$connectionName.'.database' => $path]);
        DB::purge();
        try { $test(); }
        finally {
            DB::purge();
            config(['database.connections.'.$connectionName => $config]);
            DB::connection()->setPdo($original);
        }
    }

    public function test_real_restore_replaces_test_database_and_uploads_with_safety_backup(): void
    {
        $this->withFileDatabase(function () {
            $service = app(BackupService::class);
            DB::statement('PRAGMA journal_mode=WAL');
            DB::statement('PRAGMA wal_autocheckpoint=0');
            $archive = $service->create();
            Kelas::create(['nama' => 'Data Baru', 'tingkat' => 'XI']);
            File::put(storage_path('app/public/materi/contoh.pdf'), 'new upload');
            $safety = app(BackupWorkflow::class)->restore(basename($archive));

            $this->assertDatabaseCount('kelas', 1);
            $this->assertDatabaseMissing('kelas', ['nama' => 'Data Baru']);
            $this->assertSame('fixture upload', File::get(storage_path('app/public/materi/contoh.pdf')));
            $beforeRestore = $service->restoreTest($safety);
            $this->assertSame(2, $beforeRestore['tables']['kelas']);
            $this->assertSame('new upload', File::get($beforeRestore['directory'].'/uploads/public/materi/contoh.pdf'));
            $this->assertFalse(app()->maintenanceMode()->active());
        });
    }

    public function test_failed_file_restore_rolls_back_uploaded_files(): void
    {
        $this->withFileDatabase(function () {
            $archive = app(BackupService::class)->create();
            File::put(storage_path('app/public/materi/contoh.pdf'), 'latest upload');
            config(['filesystems.disks.local.root' => storage_path('app/nonstandard')]);
            try {
                app(BackupWorkflow::class)->restore(basename($archive));
                $this->fail('Expected directory mismatch');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('direktori standar', $exception->getMessage());
            }
            $this->assertSame('latest upload', File::get(storage_path('app/public/materi/contoh.pdf')));
            $this->assertDatabaseCount('kelas', 1);
        });
    }

    public function test_upload_is_verified_without_replacing_data(): void
    {
        $archive = app(BackupService::class)->create();
        $name = app(BackupWorkflow::class)->upload($archive);
        $this->assertStringStartsWith('tjkt-check-upload-', $name);
        $this->assertSame('passed', app(BackupCatalog::class)->report($name)['status']);
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_create_button_and_restore_confirmation_validation(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        $page = Livewire::test(Backup::class)->assertSee('Buat Backup')->assertSee('Upload Backup');
        $page->mountAction('buatBackup')->setActionData(['writers_stopped' => true])->callMountedAction();
        $this->assertCount(1, app(BackupCatalog::class)->all());
        $name = app(BackupCatalog::class)->all()[0]['name'];
        $page->mountAction('pulihkan', ['name' => $name])
            ->setActionData(['writers_stopped' => true, 'confirmation' => 'salah'])
            ->callMountedAction()->assertHasActionErrors(['confirmation']);
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_exclusive_operation_is_refused_while_another_request_is_running(): void
    {
        $request = new BackupOperationLock;
        $operation = new BackupOperationLock;
        $request->beginRequest();
        try {
            $operation->run(fn () => $this->fail('Must not execute'));
            $this->fail('Expected lock refusal');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Masih ada aktivitas', $exception->getMessage());
        } finally {
            $request->release();
            $operation->release();
        }
    }

    public function test_upload_button_accepts_verified_zip(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('admin'));
        $this->actingAs(User::factory()->create());
        $archive = app(BackupService::class)->create();
        $upload = \Illuminate\Http\UploadedFile::fake()->createWithContent('cadangan.zip', File::get($archive));
        Livewire::test(Backup::class)->mountAction('uploadBackup')
            ->setActionData(['archive' => $upload])->callMountedAction()->assertHasNoActionErrors();
        $this->assertCount(2, app(BackupCatalog::class)->all());
        $this->assertDatabaseCount('kelas', 1);
    }

    public function test_restore_action_requires_confirmation_then_logs_out(): void
    {
        $this->withFileDatabase(function () {
            Filament::setCurrentPanel(Filament::getPanel('admin'));
            $this->actingAs(User::factory()->create());
            $archive = app(BackupService::class)->create();
            Kelas::create(['nama' => 'Setelah backup', 'tingkat' => 'XI']);
            Livewire::test(Backup::class)->mountAction('pulihkan', ['name' => basename($archive)])
                ->setActionData(['writers_stopped' => true, 'confirmation' => 'PULIHKAN'])
                ->callMountedAction()->assertRedirect(route('filament.admin.auth.login'));
            $this->assertGuest();
            $this->assertDatabaseCount('kelas', 1);
        });
    }

    public function test_schema_version_mismatch_is_rejected_before_restore(): void
    {
        $this->withFileDatabase(function () {
            $archive = app(BackupService::class)->create();
            DB::table('migrations')->insert(['migration' => 'future_migration', 'batch' => 999]);
            try {
                app(BackupWorkflow::class)->restore(basename($archive));
                $this->fail('Expected incompatible schema rejection');
            } catch (\RuntimeException $exception) {
                $this->assertStringContainsString('Versi database', $exception->getMessage());
            }
            $this->assertDatabaseHas('migrations', ['migration' => 'future_migration']);
        });
    }
}
