<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\Siswa;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class SiswaImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $nis = trim((string) ($row['nis'] ?? ''));
        $nama = trim((string) ($row['nama'] ?? ''));
        $namaKelas = trim((string) ($row['kelas'] ?? ''));

        if ($nis === '' || $nama === '' || $namaKelas === '') {
            throw ValidationException::withMessages([
                'file' => 'Kolom nis, nama, dan kelas wajib diisi.',
            ]);
        }

        $kelas = Kelas::query()->where('nama', $namaKelas)->first();

        if (! $kelas) {
            throw ValidationException::withMessages([
                'file' => "Kelas '{$namaKelas}' belum tersedia. Tambahkan melalui menu Kelas terlebih dahulu.",
            ]);
        }

        return Siswa::updateOrCreate(
            ['nis' => $nis],
            ['nama' => $nama, 'kelas_id' => $kelas->id]
        );
    }
}
