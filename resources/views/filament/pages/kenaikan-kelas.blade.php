<x-filament-panels::page>
    <div class="space-y-6">
        <x-filament::section>
            <x-slot name="heading">Periode tujuan</x-slot>
            <x-slot name="description">Pilih periode untuk mencatat proses kenaikan atau kelulusan rombel.</x-slot>

            <div class="grid gap-5 lg:grid-cols-[minmax(18rem,0.8fr)_minmax(0,1.2fr)] lg:items-end">
                <div class="rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-white/10 dark:bg-white/[.03]">
                    <label for="periodeAkademikId" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">Periode akademik</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select id="periodeAkademikId" wire:model.live="periodeAkademikId">
                            <option value="">Pilih periode akademik</option>
                            @foreach ($this->periodeOptions as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>

                <div class="rounded-xl border border-primary-200 bg-primary-50 p-4 dark:border-primary-400/20 dark:bg-primary-400/10">
                    <div class="flex items-start gap-3 text-sm leading-6 text-primary-800 dark:text-primary-200">
                        <x-filament::icon icon="heroicon-o-information-circle" class="mt-0.5 size-5 shrink-0" />
                        <div>
                            <p class="font-semibold">Urutan proses kenaikan</p>
                            <p class="mt-1">Proses bertahap seperti administrasi Dapodik: luluskan tingkat XII, naikkan XI ke XII, kemudian X ke XI. Siswa tetap berada di rombelnya; yang berubah hanya tingkat dan nama rombel.</p>
                        </div>
                    </div>
                    <div class="mt-4 flex flex-wrap items-center gap-2 text-xs font-semibold">
                        <span class="rounded-full bg-white px-3 py-1.5 text-gray-700 shadow-sm dark:bg-gray-900 dark:text-gray-200">1 · XII — Luluskan</span>
                        <x-filament::icon icon="heroicon-m-arrow-right" class="size-4 text-primary-500" />
                        <span class="rounded-full bg-white px-3 py-1.5 text-gray-700 shadow-sm dark:bg-gray-900 dark:text-gray-200">2 · XI — Naik ke XII</span>
                        <x-filament::icon icon="heroicon-m-arrow-right" class="size-4 text-primary-500" />
                        <span class="rounded-full bg-white px-3 py-1.5 text-gray-700 shadow-sm dark:bg-gray-900 dark:text-gray-200">3 · X — Naik ke XI</span>
                    </div>
                </div>
            </div>

            @if ($this->periodeOptions === [])
                <div class="mt-4 rounded-xl border border-warning-300 bg-warning-50 p-4 text-sm text-warning-800 dark:border-warning-400/20 dark:bg-warning-400/10 dark:text-warning-200">
                    Belum ada periode akademik. Buat periode tujuan terlebih dahulu di menu Periode Akademik.
                </div>
            @endif
        </x-filament::section>

        <x-filament::section>
            <x-slot name="heading">Proses rombel</x-slot>
            <x-slot name="description">Pilih tingkat, periksa jumlah siswa, lalu jalankan kenaikan atau kelulusan untuk setiap rombel.</x-slot>
            <x-slot name="afterHeader">
                <x-filament::button
                    tag="a"
                    :href="\App\Filament\Resources\Kelas\KelasResource::getUrl('create')"
                    icon="heroicon-o-plus"
                    color="gray"
                    size="sm"
                >
                    Buat rombel
                </x-filament::button>
            </x-slot>

            @if ($this->kelas->isEmpty())
                <div class="rounded-xl border border-dashed border-gray-300 px-6 py-10 text-center dark:border-white/15">
                    <x-filament::icon icon="heroicon-o-academic-cap" class="mx-auto size-9 text-gray-400" />
                    <p class="mt-3 font-medium text-gray-950 dark:text-white">Belum ada rombel aktif</p>
                    <p class="mt-1 text-sm text-gray-500">Buat rombel terlebih dahulu untuk memulai proses akademik.</p>
                </div>
            @else
                @php($semuaRombel = $this->kelas)
                <div class="space-y-6">
                    @foreach ([
                        'XII' => ['step' => '01', 'title' => 'Kelulusan kelas XII', 'description' => 'Siswa yang diluluskan menjadi alumni. Riwayat nilai dan materi tetap tersimpan.', 'tone' => 'success'],
                        'XI' => ['step' => '02', 'title' => 'Kenaikan kelas XI', 'description' => 'Naikkan rombel XI ke tingkat XII sebelum memproses kelas X.', 'tone' => 'info'],
                        'X' => ['step' => '03', 'title' => 'Kenaikan kelas X', 'description' => 'Naikkan rombel X ke tingkat XI setelah nama rombel tujuan tersedia.', 'tone' => 'warning'],
                    ] as $tingkat => $bagian)
                        @php($rombelTingkat = $semuaRombel->where('tingkat', $tingkat))
                        <section class="overflow-hidden rounded-2xl border border-gray-200 dark:border-white/10">
                            <div class="flex flex-col gap-3 border-b border-gray-200 bg-gray-50 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-white/10 dark:bg-white/[.03]">
                                <div class="flex items-start gap-3">
                                    <span class="flex size-9 shrink-0 items-center justify-center rounded-xl bg-primary-100 text-sm font-bold text-primary-700 dark:bg-primary-400/15 dark:text-primary-300">{{ $bagian['step'] }}</span>
                                    <div>
                                        <h3 class="font-semibold text-gray-950 dark:text-white">{{ $bagian['title'] }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">{{ $bagian['description'] }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex w-fit items-center rounded-full bg-white px-3 py-1.5 text-xs font-medium text-gray-600 shadow-sm dark:bg-gray-900 dark:text-gray-300">
                                    {{ $rombelTingkat->count() }} rombel · {{ $rombelTingkat->sum('siswa_aktif_count') }} siswa aktif
                                </span>
                            </div>

                            @if ($rombelTingkat->isEmpty())
                                <div class="px-5 py-6 text-sm text-gray-500">Belum ada rombel tingkat {{ $tingkat }}.</div>
                            @else
                                <div class="grid gap-4 bg-white p-4 sm:p-5 xl:grid-cols-2 dark:bg-gray-950">
                                    @foreach ($rombelTingkat as $kelas)
                                        @php
                                            $jumlahAktif = $kelas->siswa_aktif_count;
                                            $namaBaruDefault = $this->namaRombelTujuanDefault($kelas);
                                            $tingkatTujuan = $kelas->tingkat === 'X' ? 'XI' : 'XII';
                                        @endphp

                                        <article class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm transition duration-200 hover:border-primary-300 hover:shadow-md dark:border-white/10 dark:bg-white/[.03]">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex min-w-0 items-center gap-3">
                                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-700 dark:bg-primary-400/10 dark:text-primary-300">
                                        <x-filament::icon icon="heroicon-o-academic-cap" class="size-5" />
                                    </span>
                                    <div class="min-w-0">
                                        <h3 class="truncate text-base font-semibold text-gray-950 dark:text-white">{{ $kelas->nama }}</h3>
                                        <p class="mt-1 text-sm text-gray-500">Tingkat {{ $kelas->tingkat }}</p>
                                    </div>
                                </div>
                                <span class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-gray-100 px-3 py-1.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">
                                    <x-filament::icon icon="heroicon-m-users" class="size-4" />
                                    {{ $jumlahAktif }} siswa
                                </span>
                            </div>

                            <div class="mt-5 border-t border-gray-100 pt-5 dark:border-white/10">
                                @if ($kelas->tingkat === 'XII')
                                    <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                                        <div>
                                            <p class="font-medium text-gray-950 dark:text-white">Siap diluluskan</p>
                                            <p class="mt-1 text-sm leading-5 text-gray-500">Siswa menjadi alumni dan rombel ini diarsipkan.</p>
                                        </div>
                                        <x-filament::button
                                            icon="heroicon-o-academic-cap"
                                            color="success"
                                            :disabled="! $periodeAkademikId || $jumlahAktif === 0"
                                            wire:click="luluskanKelas({{ $kelas->id }})"
                                            wire:confirm="Yakin meluluskan {{ $jumlahAktif }} siswa dari {{ $kelas->nama }}? Mereka akan berstatus alumni dan riwayat nilainya tetap tersimpan."
                                        >
                                            Luluskan rombel
                                        </x-filament::button>
                                    </div>
                                @else
                                    <div class="space-y-3">
                                        <div>
                                            <label for="nama-rombel-{{ $kelas->id }}" class="mb-2 block text-sm font-medium text-gray-700 dark:text-gray-300">
                                                Nama rombel setelah naik ke {{ $tingkatTujuan }}
                                            </label>
                                            <x-filament::input.wrapper>
                                                <x-filament::input
                                                    id="nama-rombel-{{ $kelas->id }}"
                                                    type="text"
                                                    wire:model.live="namaRombelBaru.{{ $kelas->id }}"
                                                    placeholder="{{ $namaBaruDefault }}"
                                                />
                                            </x-filament::input.wrapper>
                                        </div>
                                        <div class="flex flex-wrap items-center justify-between gap-3">
                                            <p class="text-xs text-gray-500">Siswa tetap berada di rombel yang sama.</p>
                                            <x-filament::button
                                                icon="heroicon-o-arrow-up-circle"
                                                :disabled="! $periodeAkademikId || $jumlahAktif === 0"
                                                wire:click="prosesKenaikan({{ $kelas->id }})"
                                                wire:confirm="Yakin mengubah {{ $kelas->nama }} menjadi {{ $namaRombelBaru[$kelas->id] ?? $namaBaruDefault }}? Semua siswa tetap di rombel ini."
                                            >
                                                Naikkan rombel
                                            </x-filament::button>
                                        </div>
                                    </div>
                                @endif
                            </div>
                                        </article>
                                    @endforeach
                                </div>
                            @endif
                        </section>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
