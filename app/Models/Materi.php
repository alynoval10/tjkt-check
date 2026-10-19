<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Materi extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'tingkat',
        'deskripsi',
        'pdf_file',
    ];

    protected static function booted(): void
    {
        static::creating(function (Materi $materi): void {
            $materi->kode = static::kodeBerikutnya((string) $materi->tingkat);
        });

        static::updating(function (Materi $materi): void {
            if ($materi->isDirty('tingkat')) {
                $materi->kode = static::kodeBerikutnya((string) $materi->tingkat, $materi->id);
            }
        });
    }

    public static function kodeBerikutnya(string $tingkat, ?int $kecualiId = null): string
    {
        $prefix = strtoupper(trim($tingkat));
        $kodeYangAda = static::query()
            ->where('tingkat', $prefix)
            ->when($kecualiId, fn ($query) => $query->whereKeyNot($kecualiId))
            ->pluck('kode');

        $nomorTerbesar = $kodeYangAda
            ->map(function (?string $kode) use ($prefix): int {
                return preg_match('/^' . preg_quote($prefix, '/') . '-(\d+)$/', (string) $kode, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        return sprintf('%s-%03d', $prefix, $nomorTerbesar + 1);
    }

    public function kelulusans(): HasMany
    {
        return $this->hasMany(Kelulusan::class);
    }
}
