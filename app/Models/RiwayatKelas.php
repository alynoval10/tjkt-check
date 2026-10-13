<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatKelas extends Model
{
    protected $table = 'riwayat_kelas';

    protected $fillable = [
        'siswa_id',
        'kelas_asal_id',
        'kelas_tujuan_id',
        'periode_akademik_id',
        'jenis_perubahan',
        'catatan',
        'kelas_asal_nama',
        'kelas_asal_tingkat',
        'kelas_tujuan_nama',
        'kelas_tujuan_tingkat',
    ];

    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    public function kelasAsal(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_asal_id');
    }

    public function kelasTujuan(): BelongsTo
    {
        return $this->belongsTo(Kelas::class, 'kelas_tujuan_id');
    }

    public function periodeAkademik(): BelongsTo
    {
        return $this->belongsTo(PeriodeAkademik::class);
    }
}
