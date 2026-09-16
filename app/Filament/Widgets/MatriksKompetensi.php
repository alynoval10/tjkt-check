<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Siswa;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\Widget;

class MatriksKompetensi extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.matriks-kompetensi';

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 40;

    public ?int $kelasIdAktif = null;

    public ?string $namaKelas = null;

    public ?string $tingkatKelas = null;

    public array $materis = [];

    public array $siswas = [];

    public function mount(): void
    {
        $this->muatData();
    }

    public function updatedPageFilters(): void
    {
        $this->muatData();
    }

    protected function muatData(): void
    {
        $kelasId = $this->pageFilters['kelas_id'] ?? null;

        $kelas = $kelasId
            ? Kelas::find($kelasId)
            : Kelas::query()
                ->orderBy('tingkat')
                ->orderBy('nama')
                ->first();

        if (! $kelas) {
            $this->resetData();

            return;
        }

        $this->kelasIdAktif = $kelas->id;
        $this->namaKelas = $kelas->nama;
        $this->tingkatKelas = $kelas->tingkat;

        $materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        $materiIds = $materis->pluck('id');

        $siswas = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->with([
                'kelulusans' => fn ($query) =>
                    $query->whereIn('materi_id', $materiIds),
            ])
            ->orderBy('nama')
            ->get();

        $this->materis = $materis
            ->map(fn ($materi) => [
                'id' => $materi->id,
                'nama' => $materi->nama,
            ])
            ->values()
            ->toArray();

        $this->siswas = $siswas
            ->map(function ($siswa) use ($materis) {
                $penilaianByMateri = $siswa->kelulusans
                    ->keyBy('materi_id');

                $matrix = [];
                $jumlahLulus = 0;

                foreach ($materis as $materi) {
                    $kelulusan = $penilaianByMateri->get($materi->id);

                    if (! $kelulusan || is_null($kelulusan->nilai)) {
                        $status = 'belum_diuji';
                        $nilai = null;
                    } elseif ($kelulusan->nilai >= 75) {
                        $status = 'lulus';
                        $nilai = $kelulusan->nilai;
                        $jumlahLulus++;
                    } else {
                        $status = 'belum_lulus';
                        $nilai = $kelulusan->nilai;
                    }

                    $matrix[$materi->id] = [
                        'status' => $status,
                        'nilai' => $nilai,
                    ];
                }

                $progres = $materis->count() > 0
                    ? round(($jumlahLulus / $materis->count()) * 100)
                    : 0;

                return [
                    'id' => $siswa->id,
                    'nama' => $siswa->nama,
                    'matrix' => $matrix,
                    'progres' => $progres,
                ];
            })
            ->values()
            ->toArray();
    }

    protected function resetData(): void
    {
        $this->kelasIdAktif = null;
        $this->namaKelas = null;
        $this->tingkatKelas = null;
        $this->materis = [];
        $this->siswas = [];
    }
}