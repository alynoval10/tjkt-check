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
        ])
        ->findOrFail($id);



        return view('detail-siswa', compact('siswa'));

    }

}