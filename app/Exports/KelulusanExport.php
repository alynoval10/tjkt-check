<?php

namespace App\Exports;

use App\Models\Kelulusan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class KelulusanExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Kelulusan::with([
            'siswa',
            'materi',
            'user'
        ])
        ->get()
        ->map(function($item){

            return [
                'Nama Siswa' => $item->siswa->nama,
                'NIS' => $item->siswa->nis,
                'Kelas' => $item->siswa->kelas,
                'Materi' => $item->materi->nama,
                'Penguji' => $item->user->name,
                'Tanggal Uji' => $item->tanggal_uji,
            ];

        });
    }


    public function headings(): array
    {
        return [
            'Nama Siswa',
            'NIS',
            'Kelas',
            'Materi',
            'Penguji',
            'Tanggal Uji',
        ];
    }
}