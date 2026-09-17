<?php

namespace App\Filament\Pages;

use App\Models\Kelas;
use App\Models\Kelulusan;
use App\Models\Materi;
use App\Models\PeriodeAkademik;
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
    /*
    |--------------------------------------------------------------------------
    | Navigasi Sidebar
    |--------------------------------------------------------------------------
    | Penilaian Massal berada di grup Penilaian setelah menu Kelulusan.
    */

    protected static string|UnitEnum|null $navigationGroup = 'Penilaian';

    protected static ?string $navigationLabel = 'Penilaian Massal';

    protected static string|BackedEnum|null $navigationIcon =
        Heroicon::OutlinedClipboardDocumentCheck;

    protected static ?int $navigationSort = 2;

    /*
    |--------------------------------------------------------------------------
    | View
    |--------------------------------------------------------------------------
    */

    protected string $view = 'filament.pages.penilaian-massal';

    /*
    |--------------------------------------------------------------------------
    | Filter Utama
    |--------------------------------------------------------------------------
    */

    public ?int $kelasId = null;

    public ?int $materiId = null;

    public string $tanggalUji;

    /*
    |--------------------------------------------------------------------------
    | Data Penilaian
    |--------------------------------------------------------------------------
    */

    public array $nilai = [];

    public array $catatan = [];

    /*
    |--------------------------------------------------------------------------
    | Filter Tampilan Siswa
    |--------------------------------------------------------------------------
    */

    public string $pencarian = '';

    public string $filterStatus = 'semua';

    /*
    |--------------------------------------------------------------------------
    | Status Penilaian yang Sudah Tersimpan
    |--------------------------------------------------------------------------
    | Status tidak berubah hanya karena guru sedang mengetik nilai baru.
    */

    public array $statusTersimpan = [];

    /*
    |--------------------------------------------------------------------------
    | ID Record Existing
    |--------------------------------------------------------------------------
    | Menyimpan ID kelulusan yang dimuat dari database.
    |
    | Ini penting agar:
    | - data lama periode NULL tidak diduplikasi
    | - data periode aktif diperbarui pada record yang sama
    | - record baru tetap menggunakan periode akademik aktif
    */

    public array $recordIdTersimpan = [];

    /*
    |--------------------------------------------------------------------------
    | Collection Data
    |--------------------------------------------------------------------------
    */

    public Collection $siswas;

    public Collection $materis;

    /*
    |--------------------------------------------------------------------------
    | Siswa yang Ditampilkan
    |--------------------------------------------------------------------------
    | Search dan filter hanya mempengaruhi tampilan.
    | Array nilai dan catatan tetap utuh.
    */

    public function getSiswasTampilProperty(): Collection
    {
        $keyword = mb_strtolower(
            trim($this->pencarian)
        );

        return $this->siswas->filter(
            function ($siswa) use ($keyword) {
                /*
                |--------------------------------------------------------------------------
                | Pencarian Nama / NIS
                |--------------------------------------------------------------------------
                */

                $cocokNama =
                    $keyword === ''
                    || str_contains(
                        mb_strtolower($siswa->nama),
                        $keyword
                    )
                    || str_contains(
                        mb_strtolower((string) $siswa->nis),
                        $keyword
                    );

                /*
                |--------------------------------------------------------------------------
                | Status Existing
                |--------------------------------------------------------------------------
                */

                $status = $this->statusTersimpan[$siswa->id]
                    ?? 'belum_diuji';

                /*
                |--------------------------------------------------------------------------
                | Filter Status
                |--------------------------------------------------------------------------
                */

                $cocokStatus =
                    $this->filterStatus === 'semua'
                    || $this->filterStatus === $status;

                return $cocokNama && $cocokStatus;
            }
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Mount
    |--------------------------------------------------------------------------
    | Menyiapkan data awal saat halaman pertama kali dibuka.
    */

    public function mount(): void
    {
        $this->tanggalUji = now()->format('Y-m-d');

        $this->siswas = collect();

        $this->materis = collect();
    }

    /*
    |--------------------------------------------------------------------------
    | Saat Kelas Berubah
    |--------------------------------------------------------------------------
    */

    public function updatedKelasId(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Reset Filter
        |--------------------------------------------------------------------------
        */

        $this->pencarian = '';

        $this->filterStatus = 'semua';

        $this->statusTersimpan = [];

        $this->recordIdTersimpan = [];

        $this->resetValidation();

        /*
        |--------------------------------------------------------------------------
        | Reset Materi dan Penilaian
        |--------------------------------------------------------------------------
        */

        $this->materiId = null;

        $this->nilai = [];

        $this->catatan = [];

        /*
        |--------------------------------------------------------------------------
        | Ambil Kelas
        |--------------------------------------------------------------------------
        */

        $kelas = Kelas::find($this->kelasId);

        if (! $kelas) {
            $this->materis = collect();

            $this->siswas = collect();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Materi Sesuai Tingkat
        |--------------------------------------------------------------------------
        */

        $this->materis = Materi::query()
            ->where(
                'tingkat',
                $kelas->tingkat
            )
            ->orderBy('nama')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Siswa Sesuai Kelas
        |--------------------------------------------------------------------------
        */

        $this->siswas = Siswa::query()
            ->where(
                'kelas_id',
                $kelas->id
            )
            ->orderBy('nama')
            ->get();
    }

    /*
    |--------------------------------------------------------------------------
    | Saat Materi Berubah
    |--------------------------------------------------------------------------
    */

    public function updatedMateriId(): void
    {
        $this->pencarian = '';

        $this->filterStatus = 'semua';

        $this->resetValidation();

        $this->loadExistingData();
    }

    /*
    |--------------------------------------------------------------------------
    | Apakah Periode Aktif Merupakan Periode Pertama?
    |--------------------------------------------------------------------------
    | Data lama sebelum fitur periode dibuat memiliki periode NULL.
    |
    | Data NULL hanya dianggap sebagai bagian dari periode pertama.
    | Ketika nantinya masuk semester/tahun ajaran berikutnya, data NULL
    | tidak lagi ikut dimuat.
    */

    protected function isPeriodePertama(
        PeriodeAkademik $periode
    ): bool {
        $periodePertamaId = PeriodeAkademik::query()
            ->orderBy('id')
            ->value('id');

        return (int) $periodePertamaId === (int) $periode->id;
    }

    /*
    |--------------------------------------------------------------------------
    | Muat Penilaian Existing
    |--------------------------------------------------------------------------
    */

    protected function loadExistingData(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Bersihkan State Lama
        |--------------------------------------------------------------------------
        */

        $this->statusTersimpan = [];

        $this->recordIdTersimpan = [];

        $this->nilai = [];

        $this->catatan = [];

        /*
        |--------------------------------------------------------------------------
        | Pastikan Materi dan Siswa Sudah Ada
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->materiId
            || $this->siswas->isEmpty()
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Periode Akademik Aktif
        |--------------------------------------------------------------------------
        */

        $periode = PeriodeAkademik::aktif();

        if (! $periode) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Tentukan Apakah Data Legacy NULL Perlu Dibaca
        |--------------------------------------------------------------------------
        */

        $gunakanDataLama =
            $this->isPeriodePertama($periode);

        /*
        |--------------------------------------------------------------------------
        | Ambil Penilaian dari Database
        |--------------------------------------------------------------------------
        | Selalu ambil record periode aktif.
        |
        | Untuk periode pertama, record lama dengan periode NULL juga ikut
        | dibaca agar data existing tetap tampil seperti sebelumnya.
        */

        $existing = Kelulusan::query()
            ->where(
                'materi_id',
                $this->materiId
            )
            ->whereIn(
                'siswa_id',
                $this->siswas->pluck('id')
            )
            ->where(
                function ($query) use (
                    $periode,
                    $gunakanDataLama
                ): void {
                    $query->where(
                        'periode_akademik_id',
                        $periode->id
                    );

                    if ($gunakanDataLama) {
                        $query->orWhereNull(
                            'periode_akademik_id'
                        );
                    }
                }
            )
            ->get()
            ->groupBy('siswa_id');

        /*
        |--------------------------------------------------------------------------
        | Isi Nilai, Catatan, Status dan ID Record
        |--------------------------------------------------------------------------
        */

        foreach ($this->siswas as $siswa) {
            $records = $existing->get($siswa->id);

            if (! $records || $records->isEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Prioritaskan Record Periode Aktif
            |--------------------------------------------------------------------------
            | Kalau ternyata sudah terdapat record aktif dan record legacy,
            | record aktif yang digunakan.
            */

            $kelulusan =
                $records->first(
                    fn ($record) =>
                        (int) $record->periode_akademik_id
                        === (int) $periode->id
                )
                ?? $records->first(
                    fn ($record) =>
                        is_null(
                            $record->periode_akademik_id
                        )
                );

            if (! $kelulusan) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Simpan ID Record Existing
            |--------------------------------------------------------------------------
            */

            $this->recordIdTersimpan[$siswa->id] =
                $kelulusan->id;

            /*
            |--------------------------------------------------------------------------
            | Nilai
            |--------------------------------------------------------------------------
            */

            $this->nilai[$siswa->id] =
                $kelulusan->nilai;

            /*
            |--------------------------------------------------------------------------
            | Catatan
            |--------------------------------------------------------------------------
            */

            $this->catatan[$siswa->id] =
                $kelulusan->catatan;

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            $this->statusTersimpan[$siswa->id] =
                is_null($kelulusan->nilai)
                    ? 'belum_diuji'
                    : (
                        $kelulusan->nilai >= 75
                            ? 'lulus'
                            : 'remedial'
                    );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Isi Saran Catatan
    |--------------------------------------------------------------------------
    */

    public function isiSaranCatatan(
        int $siswaId,
        string $text
    ): void {
        $this->catatan[$siswaId] = $text;
    }

    /*
    |--------------------------------------------------------------------------
    | Simpan Penilaian Massal
    |--------------------------------------------------------------------------
    */

    public function simpan(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Validasi Pilihan Dasar
        |--------------------------------------------------------------------------
        */

        if (
            ! $this->kelasId
            || ! $this->materiId
        ) {
            Notification::make()
                ->title('Data belum lengkap')
                ->body(
                    'Pilih kelas dan materi terlebih dahulu.'
                )
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Periode Akademik Aktif
        |--------------------------------------------------------------------------
        */

        $periode = PeriodeAkademik::aktif();

        if (! $periode) {
            Notification::make()
                ->title('Periode akademik belum aktif')
                ->body(
                    'Aktifkan tahun ajaran dan semester terlebih dahulu.'
                )
                ->danger()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Ambil Kelas
        |--------------------------------------------------------------------------
        */

        $kelas = Kelas::find($this->kelasId);

        if (! $kelas) {
            Notification::make()
                ->title('Kelas tidak ditemukan')
                ->danger()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi Semua Input
        |--------------------------------------------------------------------------
        | Validasi dilakukan sebelum database mulai ditulis.
        */

        $this->validate(
            [
                'kelasId' => [
                    'required',
                    Rule::exists('kelas', 'id'),
                ],

                'materiId' => [
                    'required',
                    Rule::exists('materis', 'id')
                        ->where(
                            'tingkat',
                            $kelas->tingkat
                        ),
                ],

                'tanggalUji' => [
                    'required',
                    'date_format:Y-m-d',
                ],

                'nilai' => [
                    'array',
                ],

                'nilai.*' => [
                    'nullable',
                    'integer',
                    'between:0,100',
                ],

                'catatan' => [
                    'array',
                ],

                'catatan.*' => [
                    'nullable',
                    'string',
                    'max:5000',
                ],
            ],
            [
                'nilai.*.integer' =>
                    'Nilai harus berupa bilangan bulat.',

                'nilai.*.between' =>
                    'Nilai harus antara 0 sampai 100.',

                'tanggalUji.date_format' =>
                    'Tanggal uji tidak valid.',

                'materiId.exists' =>
                    'Materi tidak sesuai dengan tingkat kelas.',

                'catatan.*.max' =>
                    'Catatan maksimal 5000 karakter.',
            ]
        );

        /*
        |--------------------------------------------------------------------------
        | Ambil Ulang Siswa dari Database
        |--------------------------------------------------------------------------
        | Jangan hanya mempercayai state yang dikirim browser.
        */

        $siswas = Siswa::query()
            ->where(
                'kelas_id',
                $this->kelasId
            )
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Ambil Siswa yang Memiliki Nilai
        |--------------------------------------------------------------------------
        */

        $dinilai = $siswas->filter(
            fn ($siswa) =>
                array_key_exists(
                    $siswa->id,
                    $this->nilai
                )
                && $this->nilai[$siswa->id] !== ''
                && ! is_null(
                    $this->nilai[$siswa->id]
                )
        );

        /*
        |--------------------------------------------------------------------------
        | Tidak Ada Nilai
        |--------------------------------------------------------------------------
        */

        if ($dinilai->isEmpty()) {
            Notification::make()
                ->title(
                    'Belum ada nilai untuk disimpan'
                )
                ->warning()
                ->send();

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Simpan dalam Transaction
        |--------------------------------------------------------------------------
        | Jika satu proses gagal, seluruh perubahan dibatalkan.
        */

        DB::transaction(
            function () use (
                $dinilai,
                $periode
            ): void {
                foreach ($dinilai as $siswa) {
                    /*
                    |--------------------------------------------------------------------------
                    | Cek Record Existing
                    |--------------------------------------------------------------------------
                    | Jika sebelumnya record sudah dimuat, update record yang
                    | sama. Termasuk record lama dengan periode NULL.
                    */

                    $recordId =
                        $this->recordIdTersimpan[$siswa->id]
                        ?? null;

                    $kelulusan = null;

                    if ($recordId) {
                        $kelulusan = Kelulusan::query()
                            ->whereKey($recordId)
                            ->where(
                                'siswa_id',
                                $siswa->id
                            )
                            ->where(
                                'materi_id',
                                $this->materiId
                            )
                            ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Jika Tidak Ada Existing Record
                    |--------------------------------------------------------------------------
                    | Cari record periode aktif untuk menghindari duplikat.
                    */

                    if (! $kelulusan) {
                        $kelulusan = Kelulusan::query()
                            ->where(
                                'siswa_id',
                                $siswa->id
                            )
                            ->where(
                                'materi_id',
                                $this->materiId
                            )
                            ->where(
                                'periode_akademik_id',
                                $periode->id
                            )
                            ->first();
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Data yang Akan Disimpan
                    |--------------------------------------------------------------------------
                    */

                    $data = [
                        'user_id' => auth()->id(),

                        'tanggal_uji' =>
                            $this->tanggalUji,

                        'nilai' =>
                            (int) $this->nilai[$siswa->id],

                        'catatan' =>
                            $this->catatan[$siswa->id]
                            ?? null,
                    ];

                    /*
                    |--------------------------------------------------------------------------
                    | Update Existing
                    |--------------------------------------------------------------------------
                    | Periode legacy NULL TIDAK diubah.
                    | Artinya data lama tetap aman seperti sebelumnya.
                    */

                    if ($kelulusan) {
                        $kelulusan->update($data);

                        continue;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Buat Penilaian Baru
                    |--------------------------------------------------------------------------
                    | Hanya record yang benar-benar baru yang mendapatkan
                    | periode akademik aktif.
                    */

                    Kelulusan::create([
                        'siswa_id' =>
                            $siswa->id,

                        'materi_id' =>
                            $this->materiId,

                        'periode_akademik_id' =>
                            $periode->id,

                        ...$data,
                    ]);
                }
            }
        );

        /*
        |--------------------------------------------------------------------------
        | Jumlah Data yang Diproses
        |--------------------------------------------------------------------------
        */

        $jumlahDisimpan =
            $dinilai->count();

        /*
        |--------------------------------------------------------------------------
        | Notifikasi Berhasil
        |--------------------------------------------------------------------------
        */

        Notification::make()
            ->title(
                'Penilaian berhasil disimpan'
            )
            ->body(
                "{$jumlahDisimpan} penilaian berhasil diproses."
            )
            ->success()
            ->send();

        /*
        |--------------------------------------------------------------------------
        | Refresh Data Existing
        |--------------------------------------------------------------------------
        */

        $this->loadExistingData();

        /*
        |--------------------------------------------------------------------------
        | Event untuk Blade / JavaScript
        |--------------------------------------------------------------------------
        */

        $this->dispatch(
            'penilaian-disimpan'
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Konfirmasi Simpan
    |--------------------------------------------------------------------------
    | Modal Filament menampilkan ringkasan sebelum database ditulis.
    */

    public function konfirmasiSimpanAction(): Action
    {
        return Action::make(
            'konfirmasiSimpan'
        )
            ->label(
                'Simpan Semua Penilaian'
            )
            ->icon(
                'heroicon-o-check'
            )
            ->requiresConfirmation()
            ->modalHeading(
                'Simpan Penilaian?'
            )
            ->modalWidth('lg')
            ->modalDescription(null)
            ->modalSubmitActionLabel(
                'Ya, Simpan Semua'
            )
            ->modalCancelActionLabel(
                'Kembali Periksa'
            )
            ->modalContent(
                function () {
                    /*
                    |--------------------------------------------------------------------------
                    | Data Ringkasan
                    |--------------------------------------------------------------------------
                    */

                    $kelas =
                        Kelas::find(
                            $this->kelasId
                        );

                    $materi =
                        Materi::find(
                            $this->materiId
                        );

                    $periode =
                        PeriodeAkademik::aktif();

                    /*
                    |--------------------------------------------------------------------------
                    | Semua ID Siswa di Kelas
                    |--------------------------------------------------------------------------
                    */

                    $ids = Siswa::query()
                        ->where(
                            'kelas_id',
                            $this->kelasId
                        )
                        ->pluck('id');

                    /*
                    |--------------------------------------------------------------------------
                    | Siswa yang Memiliki Nilai
                    |--------------------------------------------------------------------------
                    */

                    $diisi = $ids->filter(
                        fn ($id) =>
                            array_key_exists(
                                $id,
                                $this->nilai
                            )
                            && $this->nilai[$id] !== ''
                            && ! is_null(
                                $this->nilai[$id]
                            )
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Siswa yang Sedang Terlihat
                    |--------------------------------------------------------------------------
                    */

                    $terlihat =
                        $this->siswasTampil
                            ->pluck('id');

                    /*
                    |--------------------------------------------------------------------------
                    | Nilai yang Tersembunyi oleh Filter
                    |--------------------------------------------------------------------------
                    */

                    $tersembunyi =
                        $diisi
                            ->diff($terlihat)
                            ->count();

                    /*
                    |--------------------------------------------------------------------------
                    | Siswa Belum Memiliki Nilai
                    |--------------------------------------------------------------------------
                    */

                    $kosong =
                        $ids->count()
                        - $diisi->count();

                    /*
                    |--------------------------------------------------------------------------
                    | Render Modal
                    |--------------------------------------------------------------------------
                    */

                    return view(
                        'filament.pages.konfirmasi-penilaian',
                        [
                            'kelas' =>
                                $kelas?->nama
                                ?? '-',

                            'materi' =>
                                $materi?->nama
                                ?? '-',

                            'tanggal' =>
                                $this->tanggalUji,

                            'jumlah' =>
                                $diisi->count(),

                            'tersembunyi' =>
                                $tersembunyi,

                            'kosong' =>
                                $kosong,

                            /*
                             * Boleh digunakan oleh Blade modal jika nanti
                             * ingin menampilkan periode akademik.
                             */
                            'periode' =>
                                $periode
                                    ? "{$periode->tahun_ajaran} - {$periode->semester}"
                                    : 'Tidak ada periode aktif',
                        ]
                    );
                }
            )
            ->action(
                fn () => $this->simpan()
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Pilihan Kelas
    |--------------------------------------------------------------------------
    */

    public function getKelasOptionsProperty(): array
    {
        return Kelas::query()
            ->orderBy('tingkat')
            ->orderBy('nama')
            ->pluck(
                'nama',
                'id'
            )
            ->toArray();
    }
}