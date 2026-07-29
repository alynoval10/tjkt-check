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
                $query->where('nama', 'like', "%{$keyword}%")
                    ->orWhere('nis', 'like', "%{$keyword}%");
            })
            ->orderBy('nama')
            ->limit(10)
            ->get();

        return response()->json($siswas);
    }

    public function detail($id)
    {
        $siswa = Siswa::with([
            'rombel',
            'kelulusans.materi',
            'kelulusans.user',
        ])->findOrFail($id);

        $materis = Materi::query()
            ->when(
                $siswa->rombel,
                fn ($query) => $query->where('tingkat', $siswa->rombel->tingkat),
                fn ($query) => $query->whereRaw('1 = 0')
            )
            ->orderBy('nama')
            ->get();

        $totalMateri = $materis->count();
        $lulus = $siswa->kelulusans
            ->whereIn('materi_id', $materis->pluck('id'))
            ->where('nilai', '>=', 75)
            ->count();

        $persentase = $totalMateri > 0
            ? round(($lulus / $totalMateri) * 100)
            : 0;

        return view('detail-siswa', compact(
            'siswa',
            'materis',
            'totalMateri',
            'lulus',
            'persentase'
        ));
    }
}
