<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use ZipArchive;

class BackupService
{
    public function create(bool $includeEnv = false): string
    {
        return app(BackupOperationLock::class)->run(fn () => $this->createSnapshot($includeEnv));
    }

    private function createSnapshot(bool $includeEnv): string
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            throw new RuntimeException('Backup ini hanya mendukung SQLite.');
        }
        $root = storage_path('app/backups');
        File::ensureDirectoryExists($root, 0700);
        $id = now()->format('Y-m-d_H-i-s').'-'.Str::uuid();
        $stage = $root.'/.building-'.$id;
        File::ensureDirectoryExists($stage.'/database', 0700);
        $archive = $root.'/tjkt-check-'.$id.'.zip';
        $zip = new ZipArchive;
        $opened = false;
        try {
            // Snapshot SQLite konsisten, termasuk data committed yang masih berada di WAL.
            $snapshot = $stage.'/database/database.sqlite';
            $source = DB::connection()->getPdo();
            $source->exec('VACUUM INTO '.$source->quote($snapshot));
            $pdo = new PDO('sqlite:'.$snapshot);
            $tables = $this->inspectDatabase($pdo);
            $pdo = null;
            foreach (['public' => 'public', 'local' => 'private'] as $disk => $prefix) {
                if (config("filesystems.disks.$disk.driver") !== 'local') {
                    throw new RuntimeException('Disk unggahan harus lokal.');
                }
                $directory = config("filesystems.disks.$disk.root");
                if (is_link($directory)) {
                    throw new RuntimeException('Symlink unggahan tidak didukung.');
                }
                if (! is_dir($directory)) {
                    continue;
                }
                // Tolak konfigurasi disk yang mencakup staging/backup agar tidak menyalin diri sendiri.
                $sourceRoot = str_replace('\\', '/', realpath($directory)).'/';
                $backupRoot = str_replace('\\', '/', realpath($root)).'/';
                if (str_starts_with(strtolower($backupRoot), strtolower($sourceRoot))) {
                    throw new RuntimeException('Disk unggahan tidak boleh mencakup direktori backup.');
                }
                foreach (File::allFiles($directory, true) as $file) {
                    if ($file->isLink()) {
                        throw new RuntimeException('Symlink unggahan tidak didukung.');
                    }
                    $target = $stage.'/uploads/'.$prefix.'/'.str_replace('\\', '/', $file->getRelativePathname());
                    File::ensureDirectoryExists(dirname($target), 0700);
                    if (! copy($file->getPathname(), $target)) {
                        throw new RuntimeException('Gagal menyalin unggahan.');
                    }
                }
            }
            if ($includeEnv) {
                File::ensureDirectoryExists($stage.'/config', 0700);
                if (! is_file(base_path('.env')) || ! copy(base_path('.env'), $stage.'/config/.env')) {
                    throw new RuntimeException('Konfigurasi .env tidak dapat dicadangkan.');
                }
            }
            $manifest = ['format' => 1, 'created_at' => now()->toIso8601String(), 'tables' => $tables, 'files' => []];
            if ($zip->open($archive.'.partial', ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
                throw new RuntimeException('Gagal membuat arsip.');
            }
            $opened = true;
            foreach (File::allFiles($stage, true) as $file) {
                $name = str_replace('\\', '/', $file->getRelativePathname());
                $manifest['files'][$name] = ['bytes' => $file->getSize(), 'sha256' => hash_file('sha256', $file->getPathname())];
                if (! $zip->addFile($file->getPathname(), $name)) {
                    throw new RuntimeException('Gagal menambahkan file backup.');
                }
            }
            if (! $zip->addFromString('manifest.json', json_encode($manifest, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT))) {
                throw new RuntimeException('Gagal menulis manifest.');
            }
            $closed = $zip->close();
            $opened = false;
            if (! $closed) {
                throw new RuntimeException('Gagal menyelesaikan arsip.');
            }
            chmod($archive.'.partial', 0600);
            if (! rename($archive.'.partial', $archive)) {
                throw new RuntimeException('Gagal menerbitkan arsip lengkap.');
            }
            return $archive;
        } finally {
            if ($opened) { $zip->close(); }
            $pdo = null;
            // Hanya folder staging unik milik proses ini yang dihapus.
            File::deleteDirectory($stage);
            if (is_file($archive.'.partial')) { unlink($archive.'.partial'); }
        }
    }

    public function restoreTest(string $archive): array
    {
        return app(BackupOperationLock::class)->run(fn () => $this->extractAndVerify($archive));
    }

    private function extractAndVerify(string $archive): array
    {
        $zip = new ZipArchive;
        if ($zip->open($archive) !== true) {
            throw new RuntimeException('Backup tidak dapat dibuka.');
        }
        // Tidak ada opsi menimpa database aktif: setiap pengujian memakai folder baru.
        $destination = storage_path('app/restore-tests/'.Str::uuid());
        File::ensureDirectoryExists($destination, 0700);
        try {
            $stat = $zip->statName('manifest.json');
            if (! $stat || $stat['size'] > 2_000_000) {
                throw new RuntimeException('Manifest tidak tersedia atau terlalu besar. Buat backup dengan versi baru.');
            }
            $manifest = json_decode($zip->getFromName('manifest.json'), true, 512, JSON_THROW_ON_ERROR);
            if (($manifest['format'] ?? null) !== 1 || ! is_array($manifest['files'] ?? null)
                || ! isset($manifest['files']['database/database.sqlite']) || $zip->numFiles !== count($manifest['files']) + 1) {
                throw new RuntimeException('Format backup tidak sesuai.');
            }
            $total = 0;
            foreach ($manifest['files'] as $name => $expected) {
                // Validasi path mencegah isi ZIP keluar dari direktori uji.
                if (! $this->safePath($name)) {
                    throw new RuntimeException('Path file backup tidak aman.');
                }
                $stat = $zip->statName($name);
                $total += $stat['size'] ?? 0;
                if (! $stat || $stat['size'] !== ($expected['bytes'] ?? null) || $total > 2_000_000_000) {
                    throw new RuntimeException('Ukuran backup tidak sesuai atau melebihi 2 GB.');
                }
                $target = $destination.'/'.$name;
                File::ensureDirectoryExists(dirname($target), 0700);
                $input = $zip->getStream($name);
                $output = fopen($target, 'xb');
                if (! $input || ! $output) {
                    if (is_resource($input)) { fclose($input); }
                    if (is_resource($output)) { fclose($output); }
                    throw new RuntimeException('Gagal membaca file backup.');
                }
                try { $copied = stream_copy_to_stream($input, $output, $stat['size'] + 1); }
                finally { fclose($input); fclose($output); }
                chmod($target, 0600);
                if ($copied !== $stat['size'] || ! hash_equals($expected['sha256'], hash_file('sha256', $target))) {
                    throw new RuntimeException('Checksum backup tidak cocok.');
                }
            }
            $pdo = new PDO('sqlite:'.$destination.'/database/database.sqlite');
            $tables = $this->inspectDatabase($pdo);
            if ($tables !== ($manifest['tables'] ?? null)) {
                throw new RuntimeException('Jumlah data hasil restore tidak sesuai.');
            }
            File::put($destination.'/restore-report.json', json_encode([
                'verified_at' => now()->toIso8601String(), 'archive' => basename($archive),
                'tables' => $tables, 'files_verified' => count($manifest['files']),
            ], JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR));
            return ['directory' => $destination, 'tables' => $tables, 'files' => count($manifest['files'])];
        } catch (\Throwable $exception) {
            $pdo = null;
            File::deleteDirectory($destination);
            throw $exception;
        } finally {
            $pdo = null;
            $zip->close();
        }
    }

    private function safePath(string $name): bool
    {
        return ! str_contains($name, '\\') && ! str_contains($name, ':') && ! str_contains($name, "\0")
            && ! preg_match('~(^|/)(\.|\.\.)(/|$)~', $name)
            && (in_array($name, ['database/database.sqlite', 'config/.env'], true)
                || preg_match('~^uploads/(public|private)/[^/]+(?:/[^/]+)*$~', $name));
    }

    private function inspectDatabase(PDO $pdo): array
    {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->exec('PRAGMA query_only = ON');
        if ($pdo->query('PRAGMA integrity_check')->fetchColumn() !== 'ok' || $pdo->query('PRAGMA foreign_key_check')->fetch()) {
            throw new RuntimeException('Integritas database atau relasi tidak valid.');
        }
        $tables = [];
        foreach ($pdo->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN) as $name) {
            $tables[$name] = (int) $pdo->query('SELECT COUNT(*) FROM "'.str_replace('"', '""', $name).'"')->fetchColumn();
        }
        foreach (['migrations', 'siswas', 'kelas', 'materis', 'kelulusans', 'users'] as $required) {
            if (! array_key_exists($required, $tables)) {
                throw new RuntimeException('Tabel aplikasi tidak lengkap: '.$required);
            }
        }
        return $tables;
    }
}
