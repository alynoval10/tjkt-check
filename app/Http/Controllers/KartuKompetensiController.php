<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\PeriodeAkademik;
use App\Models\Siswa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KartuKompetensiController extends Controller
{
    public function __invoke(Request $request, Siswa $siswa)
    {
        $request->validate([
            'periode_id' => ['nullable', 'integer', Rule::exists('periode_akademiks', 'id')],
        ]);

        $periodeId = $request->has('periode_id')
            ? ($request->filled('periode_id') ? (int) $request->input('periode_id') : null)
            : PeriodeAkademik::aktif()?->id;
        $periode = $periodeId ? PeriodeAkademik::query()->find($periodeId) : null;
        $periodes = PeriodeAkademik::query()
            ->orderByDesc('tahun_ajaran')
            ->orderBy('semester')
            ->get();

        $siswa->load('rombel');
        $penilaianQuery = $siswa->kelulusans()->with(['user', 'periodeAkademik']);

        if ($periode) {
            $semesterDipilih = mb_strtolower($periode->semester);
            $semesterSebelumnya = in_array($semesterDipilih, ['genap', '2', 'ii'], true)
                ? ['ganjil', 'genap']
                : [$semesterDipilih];

            // Rapor semester berikutnya mewarisi capaian semester sebelumnya
            // dalam tahun ajaran yang sama. Jika materi diuji ulang, nilai dari
            // semester terbaru yang ditampilkan tanpa menghapus catatan lama.
            $queryPeriodeTahunAjaran = static function ($query) use ($periode, $semesterSebelumnya): void {
                $query->where('tahun_ajaran', $periode->tahun_ajaran)
                    ->whereIn('semester', $semesterSebelumnya);
            };

            $penilaianQuery->where(function ($query) use ($queryPeriodeTahunAjaran, $periodeId): void {
                $query->whereHas('periodeAkademik', $queryPeriodeTahunAjaran);

                // Data sebelum fitur periode tetap dimunculkan pada periode
                // akademik pertama, mengikuti perilaku migrasi sebelumnya.
                $periodePertamaId = PeriodeAkademik::query()
                    ->orderBy('tahun_ajaran')
                    ->orderByRaw("CASE WHEN semester = 'Ganjil' THEN 1 ELSE 2 END")
                    ->value('id');

                if ((int) $periodeId === (int) $periodePertamaId) {
                    $query->orWhereNull('periode_akademik_id');
                }
            });
        } elseif ($periodeId) {
            $penilaianQuery->where('periode_akademik_id', $periodeId);
        }

        $semuaPenilaian = $penilaianQuery->get();
        $urutanSemester = static function ($record) use ($periode): int {
            if (! $periode) {
                return 0;
            }

            return match (mb_strtolower($record->periodeAkademik?->semester ?? '')) {
                'ganjil', '1', 'i' => 1,
                'genap', '2', 'ii' => 2,
                default => 0,
            };
        };
        $semuaPenilaian = $semuaPenilaian
            ->sortByDesc(fn ($record) => sprintf(
                '%02d-%s-%020d',
                $urutanSemester($record),
                $record->tanggal_uji?->format('Ymd') ?? '',
                $record->id,
            ))
            ->values();

        $tingkatSnapshot = $semuaPenilaian->first(fn ($record) => filled($record->tingkat_snapshot))?->tingkat_snapshot;
        $tingkatLaporan = $siswa->status === 'alumni'
            ? null
            : ($periodeId ? ($tingkatSnapshot ?? $siswa->rombel?->tingkat) : $siswa->rombel?->tingkat);

        // Materi mengikuti tingkat siswa pada periode laporan jika ada snapshot nilai.
        $materis = Materi::query()
            ->when(
                $tingkatLaporan !== null,
                fn ($query) => $query->where('tingkat', $tingkatLaporan),
            )
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $penilaian = $semuaPenilaian
            ->whereIn('materi_id', $materis->pluck('id'))
            ->unique('materi_id')
            ->keyBy('materi_id');

        $kelasSnapshot = $semuaPenilaian->first(fn ($record) => filled($record->kelas_nama_snapshot))?->kelas_nama_snapshot;
        $kelasLaporan = $siswa->status === 'alumni'
            ? 'Alumni'
            : ($periodeId ? ($kelasSnapshot ?? $siswa->rombel?->nama ?? '-') : ($siswa->rombel?->nama ?? '-'));

        return response()->view('kartu-kompetensi', compact(
            'siswa', 'materis', 'penilaian', 'periodes', 'periode', 'periodeId', 'kelasLaporan', 'tingkatLaporan'
        ))
            ->header('Cache-Control', 'private, no-store');
    }
}
