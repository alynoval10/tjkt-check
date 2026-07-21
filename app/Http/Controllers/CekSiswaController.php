<?php

namespace App\Http\Controllers;

use App\Models\Siswa;
use Illuminate\Http\Request;

class CekSiswaController extends Controller
{
    public function index(Request $request)
    {
        $keyword = $request->search;

        $siswas = Siswa::when($keyword, function($query) use ($keyword){

            $query->where('nama','like','%'.$keyword.'%')
                  ->orWhere('nis','like','%'.$keyword.'%');

        })
        ->limit(20)
        ->get();


        return view('cek-siswa.index', compact('siswas'));
    }


    public function detail($id)
    {
        $siswa = Siswa::with([
            'kelulusans.materi',
            'kelulusans.user'
        ])
        ->findOrFail($id);


        return view('cek-siswa.detail', compact('siswa'));
    }
}