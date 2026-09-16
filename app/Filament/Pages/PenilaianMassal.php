<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use UnitEnum;

class PenilaianMassal extends Page
{
    // Navigasi sidebar: penilaian massal berada setelah Kelulusan.
    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';
    protected static ?string $navigationLabel = 'Penilaian Massal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.penilaian-massal';

    public ?int $kelasId = null;

    public ?int $materiId = null;

    public string $tanggalUji;

    public array $nilai = [];

    public array $catatan = [];

    public Collection $siswas;

    public Collection $materis;

    public function mount(): void
    {
        $this->tanggalUji = now()->format('Y-m-d');

        $this->siswas = collect();
        $this->materis = collect();
    }

    public function updatedKelasId(): void
    {
        $this->materiId = null;
        $this->nilai = [];
        $this->catatan = [];

        $kelas = Kelas::find($this->kelasId);

        if (! $kelas) {
            $this->materis = collect();
            $this->siswas = collect();

            return;
        }

        $this->materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        $this->siswas = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();
    }

    public function updatedMateriId(): void
    {
        $this->loadExistingData();
    }

    protected function loadExistingData(): void
    {
        $this->nilai = [];
        $this->catatan = [];

        if (! $this->materiId || $this->siswas->isEmpty()) {
            return;
        }

        $existing = Kelulusan::query()
            ->where('materi_id', $this->materiId)
            ->whereIn('siswa_id', $this->siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        foreach ($this->siswas as $siswa) {
            $kelulusan = $existing->get($siswa->id);

            if (! $kelulusan) {
                continue;
            }

            $this->nilai[$siswa->id] = $kelulusan->nilai;
            $this->catatan[$siswa->id] = $kelulusan->catatan;
        }
    }

    public function isiSaranCatatan(
        int $siswaId,
        string $text
    ): void {
        $this->catatan[$siswaId] = $text;
    }

    public function simpan(): void
    {
        if (! $this->kelasId || ! $this->materiId) {
            Notification::make()
                ->title('Data belum lengkap')
                ->body('Pilih kelas dan materi terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        $jumlahDisimpan = 0;

        foreach ($this->siswas as $siswa) {
            $nilai = $this->nilai[$siswa->id] ?? null;

            if ($nilai === '' || is_null($nilai)) {
                continue;
            }

            $nilai = (int) $nilai;

            if ($nilai < 0 || $nilai > 100) {
                Notification::make()
                    ->title('Nilai tidak valid')
                    ->body(
                        "Nilai {$siswa->nama} harus antara 0 sampai 100."
                    )
                    ->danger()
                    ->send();

                return;
            }

            Kelulusan::updateOrCreate(
                [
                    'siswa_id' => $siswa->id,
                    'materi_id' => $this->materiId,
                ],
                [
                    'user_id' => auth()->id(),
                    'tanggal_uji' => $this->tanggalUji,
                    'nilai' => $nilai,
                    'catatan' => $this->catatan[$siswa->id] ?? null,
                ]
            );

            $jumlahDisimpan++;
        }

        Notification::make()
            ->title('Penilaian berhasil disimpan')
            ->body("{$jumlahDisimpan} penilaian berhasil diproses.")
            ->success()
            ->send();

        $this->loadExistingData();
    }

    public function getKelasOptionsProperty(): array
    {
        return Kelas::query()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }
}
