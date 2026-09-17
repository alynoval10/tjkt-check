<?php

namespace App\Filament\Pages;

use App\Services\BackupCatalog;
use App\Services\BackupWorkflow;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Hidden;
use Illuminate\Support\Facades\Gate;
use UnitEnum;

class Backup extends Page
{
    protected string $view = 'filament.pages.backup';
    protected static ?string $title = 'Backup dan Restore';
    protected static ?string $navigationLabel = 'Backup';
    protected static string|UnitEnum|null $navigationGroup = 'Pemeliharaan';
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-circle-stack';
    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Gate::allows('manage-backups');
    }

    protected function getHeaderActions(): array
    {
        return [$this->buatBackupAction(), $this->uploadBackupAction(), $this->bersihkanHasilUjiAction()];
    }

    public function hapusBackupAction(): Action
    {
        return Action::make('hapusBackup')->label('Hapus Backup')->color('danger')->icon('heroicon-o-trash')
            ->requiresConfirmation()->modalHeading('Hapus cadangan ini permanen?')
            ->modalDescription(fn (array $arguments) => 'File: '.($arguments['name'] ?? '').'. ZIP dan laporan pemeriksaannya akan dihapus permanen. Data aktif tidak berubah. Pastikan salinan yang diperlukan sudah disimpan di tempat lain.')
            ->modalSubmitActionLabel('Ya, Hapus Backup')->modalCancelActionLabel('Batal')
            ->action(function (array $arguments): void {
                Gate::authorize('manage-backups');
                try {
                    app(BackupCatalog::class)->delete((string) ($arguments['name'] ?? ''));
                    Notification::make()->title('Backup dihapus')->success()->send();
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Gagal menghapus backup')->body($exception->getMessage())->danger()->send();
                }
            });
    }

    public function bersihkanHasilUjiAction(): Action
    {
        return Action::make('bersihkanHasilUji')->label('Bersihkan Hasil Uji')->color('gray')->icon('heroicon-o-trash')
            ->requiresConfirmation()->modalHeading('Bersihkan seluruh hasil uji selesai?')
            ->modalDescription('Salinan database dan unggahan dalam folder hasil uji akan dihapus permanen. ZIP backup, ringkasan pemeriksaan, data aktif, dan folder pengaman pemulihan tetap disimpan.')
            ->modalSubmitActionLabel('Ya, Bersihkan')->modalCancelActionLabel('Batal')
            ->action(function (): void {
                Gate::authorize('manage-backups');
                try {
                    $count = app(BackupCatalog::class)->cleanRestoreTests();
                    Notification::make()->title($count.' folder hasil uji dibersihkan')->success()->send();
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Pembersihan gagal')->body($exception->getMessage())->danger()->send();
                }
            });
    }

    public function buatBackupAction(): Action
    {
        return Action::make('buatBackup')->label('Buat Backup')->icon('heroicon-o-circle-stack')
            ->modalHeading('Buat cadangan sekarang?')
            ->modalDescription('Database dan unggahan akan dicadangkan. Aplikasi dikunci sementara selama proses; .env tidak disertakan.')
            ->schema([$this->writersStoppedField()])
            ->modalSubmitActionLabel('Buat Backup')
            ->action(function (): void {
                Gate::authorize('manage-backups');
                try {
                    app(BackupWorkflow::class)->create();
                    Notification::make()->title('Backup berhasil dibuat')->success()->send();
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Backup gagal')->body($exception->getMessage())->danger()->send();
                }
            });
    }

    public function uploadBackupAction(): Action
    {
        return Action::make('uploadBackup')->label('Upload Backup')->color('gray')->icon('heroicon-o-arrow-up-tray')
            ->modalDescription('Upload tidak mengganti data aktif. Arsip diperiksa terlebih dahulu. Batas upload 12 MB.')
            ->schema([
                FileUpload::make('archive')->label('File ZIP backup TJKT')->required()
                    ->acceptedFileTypes(['application/zip', 'application/x-zip-compressed'])
                    ->maxSize(12288)->storeFiles(false),
            ])
            ->action(function (array $data): void {
                Gate::authorize('manage-backups');
                try {
                    app(BackupWorkflow::class)->upload($data['archive']->getRealPath());
                    Notification::make()->title('Backup diunggah dan berhasil diverifikasi')->success()->send();
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Upload ditolak')->body('Arsip tidak valid atau pemeriksaan gagal. Data aktif tetap utuh.')->danger()->send();
                }
            });
    }

    private function writersStoppedField(): Checkbox
    {
        return Checkbox::make('writers_stopped')->label('Worker, scheduler, dan proses impor di luar aplikasi sudah dihentikan atau tidak berjalan.')
            ->accepted()->required();
    }

    public function pulihkanAction(): Action
    {
        return Action::make('pulihkan')->label('Pulihkan Data')->color('danger')->icon('heroicon-o-arrow-path')
            ->modalHeading('Ganti data aktif dengan backup?')
            ->modalDescription('Seluruh database dan unggahan akan kembali ke kondisi backup ini. Data sesudahnya tidak muncul lagi. Backup kondisi sekarang dibuat otomatis. Setelah berhasil, login ulang dengan akun dari backup. Konfigurasi .env tetap.')
            ->modalContent(function (array $arguments) {
                Gate::authorize('manage-backups');
                return view('filament.pages.backup-report', ['report' => app(BackupCatalog::class)->report((string) ($arguments['name'] ?? ''))]);
            })
            ->schema([
                $this->writersStoppedField(),
                Hidden::make('backup_hash')->default(function () {
                    Gate::authorize('manage-backups');
                    $arguments = $this->getMountedAction()?->getArguments() ?? [];
                    return hash_file('sha256', app(BackupCatalog::class)->path((string) ($arguments['name'] ?? '')));
                }),
                TextInput::make('confirmation')->label('Ketik PULIHKAN untuk menyetujui')->required()->rules(['in:PULIHKAN']),
            ])
            ->modalSubmitActionLabel('Pulihkan Data Aktif')
            ->action(function (array $arguments, array $data): void {
                Gate::authorize('manage-backups');
                try {
                    $path = app(BackupCatalog::class)->path((string) ($arguments['name'] ?? ''));
                    if (! hash_equals((string) ($data['backup_hash'] ?? ''), hash_file('sha256', $path))) {
                        throw new \RuntimeException('Backup berubah sejak konfirmasi dibuka. Tutup dan periksa ulang.');
                    }
                    app(BackupWorkflow::class)->restore((string) ($arguments['name'] ?? ''));
                } catch (\Throwable $exception) {
                    report($exception);
                    Notification::make()->title('Pemulihan gagal')->body($exception->getMessage())->danger()->send();
                    return;
                }
                // Jangan mempertahankan autentikasi dari database sebelum pemulihan.
                auth()->logout();
                session()->invalidate();
                session()->regenerateToken();
                $this->redirect(route('filament.admin.auth.login'));
            });
    }

    protected function getViewData(): array
    {
        Gate::authorize('manage-backups');
        return ['backups' => app(BackupCatalog::class)->all()];
    }

    public function ujiRestoreAction(): Action
    {
        return Action::make('ujiRestore')->label('Uji Restore')->icon('heroicon-o-shield-check')
            ->requiresConfirmation()->modalHeading('Uji pemulihan backup?')
            ->modalDescription('Backup akan dipulihkan ke folder uji terpisah. Data aplikasi tetap utuh.')
            ->modalSubmitActionLabel('Jalankan Pengujian')
            ->action(function (array $arguments): void {
                // Otorisasi diulang pada aksi, bukan hanya menyembunyikan menu.
                Gate::authorize('manage-backups');
                $report = app(BackupCatalog::class)->verify((string) ($arguments['name'] ?? ''));
                $notification = Notification::make()->title($report['status'] === 'passed' ? 'Uji restore berhasil' : 'Uji restore gagal');
                $report['status'] === 'passed' ? $notification->success() : $notification->danger();
                $notification->send();
            });
    }

    public function hasilAction(): Action
    {
        return Action::make('hasil')->label('Hasil')->icon('heroicon-o-document-text')
            ->modalHeading('Hasil Uji Restore')->modalSubmitAction(false)->modalCancelActionLabel('Tutup')
            ->modalContent(function (array $arguments) {
                Gate::authorize('manage-backups');
                return view('filament.pages.backup-report', [
                    'report' => app(BackupCatalog::class)->report((string) ($arguments['name'] ?? '')),
                ]);
            });
    }
}
