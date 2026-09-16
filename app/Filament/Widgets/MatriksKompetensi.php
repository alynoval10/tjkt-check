<?php

namespace App\Filament\Widgets;

use App\Models\Kelas;
use App\Models\Materi;
use App\Models\Siswa;
use App\Exports\RekapKompetensiExport;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    public ?int $materiId = null;

    public bool $hanyaRemedial = false;

    public array $materiOptions = [];

    protected function getViewData(): array
    {
        // Filter reaktif dari Dashboard diperbarui saat hidrasi, bukan updatedPageFilters.
        $this->muatData();

        return [];
    }

    // Filter mengubah matriks dan data yang akan diekspor secara bersamaan.
    public function updatedMateriId(): void
    {
        $this->muatData();
    }

    public function updatedHanyaRemedial(): void
    {
        $this->muatData();
    }

    public function exportExcel(): BinaryFileResponse
    {
        // Hitung ulang dari database agar ekspor tidak mempercayai data publik widget.
        $this->muatData();
        abort_unless($this->kelasIdAktif && $this->materis && $this->siswas, 422, 'Tidak ada data untuk diekspor.');

        return Excel::download(
            new RekapKompetensiExport($this->namaKelas, $this->materis, $this->siswas),
            'rekap-kompetensi-kelas-'.$this->kelasIdAktif.'.xlsx'
        );
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

        if ($this->kelasIdAktif !== $kelas->id) {
            $this->materiId = null;
        }

        $this->kelasIdAktif = $kelas->id;
        $this->namaKelas = $kelas->nama;
        $this->tingkatKelas = $kelas->tingkat;

        $materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        $this->materiOptions = $materis->pluck('nama', 'id')->all();
        if ($this->materiId) {
            $materis = $materis->where('id', $this->materiId);
        }

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
                $remedial = [];

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
                        $remedial[] = $materi->nama;
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
                    'nis' => $siswa->nis,
                    'matrix' => $matrix,
                    'progres' => $progres,
                    'remedial' => $remedial,
                ];
            })
            ->filter(fn ($siswa) => ! $this->hanyaRemedial || count($siswa['remedial']) > 0)
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
        $this->materiOptions = [];
        $this->materiId = null;
    }
}
