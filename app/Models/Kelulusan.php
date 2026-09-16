<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelulusan extends Model
{
    public function riwayat(): HasMany
    {
        return $this->hasMany(RiwayatPenilaian::class);
    }

    // Berlaku untuk form satuan dan massal; kegagalan riwayat membatalkan nilai.
    public function save(array $options = [])
    {
        return $this->getConnection()->transaction(function () use ($options) {
            $baru = ! $this->exists;
            $berubah = $this->isDirty(['siswa_id', 'materi_id', 'user_id', 'tanggal_uji', 'nilai', 'catatan']);
            $tersimpan = parent::save($options);

            if ($tersimpan && ($baru || $berubah)) {
                $this->riwayat()->create([
                    'siswa' => $this->siswa()->value('nama') ?? '-',
                    'materi' => $this->materi()->value('nama') ?? '-',
                    'penguji' => $this->user()->value('name') ?? '-',
                    'diubah_oleh' => auth()->user()?->name,
                    'tanggal_uji' => $this->tanggal_uji,
                    'nilai' => $this->nilai,
                    'catatan' => $this->catatan,
                    'jenis' => $baru ? 'penilaian_awal' : 'perubahan',
                    'dicatat_pada' => now(),
                ]);
            }

            return $tersimpan;
        });
    }

    protected $fillable = [
    'siswa_id',
    'materi_id',
    'user_id',
    'tanggal_uji',
    'nilai',
    'catatan',
];


    public function siswa()
{
    return $this->belongsTo(Siswa::class);
}


public function materi()
{
    return $this->belongsTo(Materi::class);
}


public function user()
{
    return $this->belongsTo(User::class);
}
}
