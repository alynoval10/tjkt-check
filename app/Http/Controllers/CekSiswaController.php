<?php

namespace App\Http\Controllers;

use App\Models\Materi;
use App\Models\Siswa;
use Illuminate\Http\Request;

class CekSiswaController extends Controller
{
    public function index()
    {
        return view('cek');
    }

    public function search(Request $request)
    {
        $keyword = trim((string) $request->input('keyword', ''));

        if (mb_strlen($keyword) < 2) {
            return response()->json([]);
        }

        $siswas = Siswa::query()
            ->with('rombel:id,tingkat,nama')
            ->where(function ($query) use ($keyword) {
                $query
                    ->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('nis', 'like', "%{$keyword}%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        return response()->json($siswas);
    }

    public function detail($publicId)
    {
        $siswa = Siswa::with([
            'rombel',
            'kelulusans.materi',
            'kelulusans.user',
            'kelulusans.kelas',
            'kelulusans.periodeAkademik',
            'riwayatKelas.periodeAkademik',
        ])
            ->where('public_id', $publicId)
            ->firstOrFail();

        $materis = Materi::query()
            ->when(
                $siswa->status !== 'alumni',
                fn ($query) => $query->where('tingkat', $siswa->rombel?->tingkat),
            )
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->get();

        $riwayatNilai = $siswa->kelulusans
            ->sortByDesc(fn ($kelulusan) => [$kelulusan->tanggal_uji?->format('Ymd') ?? '', $kelulusan->id])
            ->values();

        $penilaianByMateri = $riwayatNilai
            ->whereIn('materi_id', $materis->pluck('id'))
            ->unique('materi_id')
            ->keyBy('materi_id');

        /*
        |--------------------------------------------------------------------------
        | Urutkan materi
        |--------------------------------------------------------------------------
        | 1. Materi LULUS (nilai >= 75)
        | 2. Materi yang sudah dinilai tapi belum lulus
        | 3. Materi yang belum diuji
        |
        | Dalam kelompok yang sama tetap diurutkan berdasarkan nama materi.
        */
        $materis = $materis
            ->sortBy(function ($materi) use ($penilaianByMateri) {
                $penilaian = $penilaianByMateri->get($materi->id);

                if (
                    $penilaian &&
                    ! is_null($penilaian->nilai) &&
                    $penilaian->nilai >= 75
                ) {
                    $status = 1; // Lulus
                } elseif (
                    $penilaian &&
                    ! is_null($penilaian->nilai)
                ) {
                    $status = 2; // Sudah diuji tetapi belum lulus
                } else {
                    $status = 3; // Belum diuji
                }

                return sprintf(
                    '%d-%s',
                    $status,
                    strtolower($materi->nama)
                );
            })
            ->values();

        // Kirim daftar terurut dengan nama eksplisit agar view tidak perlu
        // membuat variabel lokal sebelum menampilkannya.
        $materisUrut = $materis;

        $totalMateri = $materis->count();

        $sudahDinilai = $penilaianByMateri->count();

        $lulus = $penilaianByMateri
            ->filter(
                fn ($kelulusan) =>
                    ! is_null($kelulusan->nilai)
                    && $kelulusan->nilai >= 75
            )
            ->count();

        $persentase = $totalMateri > 0
            ? round(($lulus / $totalMateri) * 100)
            : 0;

        $riwayatLulus = $siswa->riwayatKelas
            ->where('jenis_perubahan', 'lulus')
            ->sortByDesc('id')
            ->first();
        $namaKelasTampil = $siswa->status === 'alumni'
            ? ($riwayatLulus?->kelas_asal_nama ?? $siswa->rombel?->nama ?? '-')
            : ($siswa->rombel?->nama ?? '-');
        $tingkatTampil = $siswa->status === 'alumni'
            ? ($riwayatLulus?->kelas_asal_tingkat ?? $siswa->rombel?->tingkat ?? '-')
            : ($siswa->rombel?->tingkat ?? '-');

        return view('detail-siswa', compact(
            'siswa',
            'materis',
            'materisUrut',
            'penilaianByMateri',
            'totalMateri',
            'sudahDinilai',
            'lulus',
            'persentase',
            'riwayatNilai',
            'namaKelasTampil',
            'tingkatTampil',
        ));
    }
}
