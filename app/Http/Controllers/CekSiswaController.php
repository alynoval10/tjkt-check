<?php

namespace App\Http\Controllers;

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
        $keyword = $request->keyword;


        $siswas = Siswa::where('nama','like',"%$keyword%")
            ->orWhere('nis','like',"%$keyword%")
            ->limit(10)
            ->get();


        return response()->json($siswas);
    }



    public function detail($id)
{
    $siswa = Siswa::with([
        'kelulusans.materi',
        'kelulusans.user'
    ])->findOrFail($id);


    $totalMateri = \App\Models\Materi::count();

    $lulus = $siswa->kelulusans->count();


    $persentase = 0;

    if($totalMateri > 0){
        $persentase = round(
            ($lulus / $totalMateri) * 100
        );
    }


    return view('detail-siswa', compact(
        'siswa',
        'totalMateri',
        'lulus',
        'persentase'
    ));
}

}