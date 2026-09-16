<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\Siswa;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use UnitEnum;

class PenilaianMassal extends Page
{
    // Navigasi sidebar: penilaian massal berada setelah Kelulusan.
    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';
    protected static ?string $navigationLabel = 'Penilaian Massal';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;
    protected static ?int $navigationSort = 5;

    protected string $view = 'filament.pages.penilaian-massal';

    public ?int $kelasId = null;

    public ?int $materiId = null;

    public string $tanggalUji;

    public array $nilai = [];

    public array $catatan = [];

    public string $pencarian = '';

    public string $filterStatus = 'semua';

    public array $statusTersimpan = [];

    // Hanya memilih baris tampilan; array nilai dan catatan tetap utuh.
    public function getSiswasTampilProperty(): Collection
    {
        $keyword = mb_strtolower(trim($this->pencarian));

        return $this->siswas->filter(function ($siswa) use ($keyword) {
            $cocokNama = $keyword === '' || str_contains(mb_strtolower($siswa->nama), $keyword)
                || str_contains(mb_strtolower((string) $siswa->nis), $keyword);
            $status = $this->statusTersimpan[$siswa->id] ?? 'belum_diuji';

            return $cocokNama && ($this->filterStatus === 'semua' || $this->filterStatus === $status);
        });
    }

    public Collection $siswas;

    public Collection $materis;

    public function mount(): void
    {
        $this->tanggalUji = now()->format('Y-m-d');

        $this->siswas = collect();
        $this->materis = collect();
    }

    public function updatedKelasId(): void
    {
        $this->reset('pencarian', 'filterStatus', 'statusTersimpan');
        $this->resetValidation();
        $this->materiId = null;
        $this->nilai = [];
        $this->catatan = [];

        $kelas = Kelas::find($this->kelasId);

        if (! $kelas) {
            $this->materis = collect();
            $this->siswas = collect();

            return;
        }

        $this->materis = Materi::query()
            ->where('tingkat', $kelas->tingkat)
            ->orderBy('nama')
            ->get();

        $this->siswas = Siswa::query()
            ->where('kelas_id', $kelas->id)
            ->orderBy('nama')
            ->get();
    }

    public function updatedMateriId(): void
    {
        $this->reset('pencarian', 'filterStatus');
        $this->resetValidation();
        $this->loadExistingData();
    }

    protected function loadExistingData(): void
    {
        $this->statusTersimpan = [];
        $this->nilai = [];
        $this->catatan = [];

        if (! $this->materiId || $this->siswas->isEmpty()) {
            return;
        }

        $existing = Kelulusan::query()
            ->where('materi_id', $this->materiId)
            ->whereIn('siswa_id', $this->siswas->pluck('id'))
            ->get()
            ->keyBy('siswa_id');

        foreach ($this->siswas as $siswa) {
            $kelulusan = $existing->get($siswa->id);

            if (! $kelulusan) {
                continue;
            }

            $this->nilai[$siswa->id] = $kelulusan->nilai;
            $this->catatan[$siswa->id] = $kelulusan->catatan;
            // Status tetap stabil selama guru mengedit nilai yang belum disimpan.
            $this->statusTersimpan[$siswa->id] = is_null($kelulusan->nilai)
                ? 'belum_diuji'
                : ($kelulusan->nilai >= 75 ? 'lulus' : 'remedial');
        }
    }

    public function isiSaranCatatan(
        int $siswaId,
        string $text
    ): void {
        $this->catatan[$siswaId] = $text;
    }

    public function simpan(): void
    {
        if (! $this->kelasId || ! $this->materiId) {
            Notification::make()
                ->title('Data belum lengkap')
                ->body('Pilih kelas dan materi terlebih dahulu.')
                ->warning()
                ->send();

            return;
        }

        // Validasi seluruh isian sebelum penulisan pertama ke database.
        $kelas = Kelas::find($this->kelasId);
        $this->validate([
            'kelasId' => ['required', Rule::exists('kelas', 'id')],
            'materiId' => ['required', Rule::exists('materis', 'id')->where('tingkat', $kelas?->tingkat)],
            'tanggalUji' => ['required', 'date_format:Y-m-d'],
            'nilai' => ['array'],
            'nilai.*' => ['nullable', 'integer', 'between:0,100'],
            'catatan' => ['array'],
            'catatan.*' => ['nullable', 'string', 'max:5000'],
        ], [
            'nilai.*.integer' => 'Nilai harus berupa bilangan bulat.',
            'nilai.*.between' => 'Nilai harus antara 0 sampai 100.',
            'tanggalUji.date_format' => 'Tanggal uji tidak valid.',
            'materiId.exists' => 'Materi tidak sesuai dengan tingkat kelas.',
            'catatan.*.max' => 'Catatan maksimal 5000 karakter.',
        ]);

        // Ambil ulang anggota kelas; jangan mengandalkan daftar dari browser.
        $siswas = Siswa::where('kelas_id', $this->kelasId)->get();
        $dinilai = $siswas->filter(fn ($siswa) => isset($this->nilai[$siswa->id]) && $this->nilai[$siswa->id] !== '');

        if ($dinilai->isEmpty()) {
            Notification::make()->title('Belum ada nilai untuk disimpan')->warning()->send();

            return;
        }

        // Semua baris berhasil bersama, atau seluruh perubahan dibatalkan.
        DB::transaction(function () use ($dinilai): void {
            foreach ($dinilai as $siswa) {
                Kelulusan::updateOrCreate(
                    [
                        'siswa_id' => $siswa->id,
                        'materi_id' => $this->materiId,
                    ],
                    [
                        'user_id' => auth()->id(),
                        'tanggal_uji' => $this->tanggalUji,
                        'nilai' => (int) $this->nilai[$siswa->id],
                        'catatan' => $this->catatan[$siswa->id] ?? null,
                    ]
                );
            }
        });

        $jumlahDisimpan = $dinilai->count();

        Notification::make()
            ->title('Penilaian berhasil disimpan')
            ->body("{$jumlahDisimpan} penilaian berhasil diproses.")
            ->success()
            ->send();

        $this->loadExistingData();
        $this->dispatch('penilaian-disimpan');
    }

    // Modal bawaan Filament membuka ringkasan tanpa menulis ke database.
    public function konfirmasiSimpanAction(): Action
    {
        return Action::make('konfirmasiSimpan')
            ->label('Simpan Semua Penilaian')
            ->icon('heroicon-o-check')
            ->requiresConfirmation()
            ->modalHeading('Simpan Penilaian?')
            ->modalWidth('lg')
            ->modalDescription(null)
            ->modalSubmitActionLabel('Ya, Simpan Semua')
            ->modalCancelActionLabel('Kembali Periksa')
            ->modalContent(function () {
                $kelas = Kelas::find($this->kelasId);
                $materi = Materi::find($this->materiId);
                $ids = Siswa::where('kelas_id', $this->kelasId)->pluck('id');
                $diisi = $ids->filter(fn ($id) => isset($this->nilai[$id]) && $this->nilai[$id] !== '');
                $terlihat = $this->siswasTampil->pluck('id');
                $tersembunyi = $diisi->diff($terlihat)->count();
                $kosong = $ids->count() - $diisi->count();

                return view('filament.pages.konfirmasi-penilaian', [
                    'kelas' => $kelas?->nama ?? '-',
                    'materi' => $materi?->nama ?? '-',
                    'tanggal' => $this->tanggalUji,
                    'jumlah' => $diisi->count(),
                    'tersembunyi' => $tersembunyi,
                    'kosong' => $kosong,
                ]);
            })
            ->action(fn () => $this->simpan());
    }

    public function getKelasOptionsProperty(): array
    {
        return Kelas::query()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->pluck('nama', 'id')
            ->toArray();
    }
}
