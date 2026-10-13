<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelulusan extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    | Menentukan field yang boleh diisi menggunakan create() / update().
    */

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'kelas_nama_snapshot',
        'tingkat_snapshot',
        'materi_id',
        'periode_akademik_id',
        'user_id',
        'tanggal_uji',
        'nilai',
        'catatan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_uji' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Kelulusan $kelulusan): void {
            if (! $kelulusan->kelas_id && $kelulusan->siswa_id) {
                $kelulusan->kelas_id = Siswa::query()->whereKey($kelulusan->siswa_id)->value('kelas_id');
            }

            if ($kelulusan->kelas_id && (! $kelulusan->kelas_nama_snapshot || ! $kelulusan->tingkat_snapshot)) {
                $kelas = Kelas::query()->find($kelulusan->kelas_id);
                $kelulusan->kelas_nama_snapshot ??= $kelas?->nama;
                $kelulusan->tingkat_snapshot ??= $kelas?->tingkat;
            }
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Siswa
    |--------------------------------------------------------------------------
    */

    public function siswa()
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Materi
    |--------------------------------------------------------------------------
    */

    public function materi()
    {
        return $this->belongsTo(Materi::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Penguji
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Relasi Periode Akademik
    |--------------------------------------------------------------------------
    | Menghubungkan hasil penilaian dengan tahun ajaran / semester tertentu.
    */

    public function periodeAkademik()
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
