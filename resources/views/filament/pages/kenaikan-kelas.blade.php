<x-filament-panels::page>
    <div class="space-y-5">
        <x-filament::section>
            <div class="grid gap-4 md:grid-cols-[minmax(0,1fr)_2fr] md:items-end">
                <div>
                    <label for="periodeAkademikId" class="mb-2 block text-sm font-medium">Periode tujuan</label>
                    <x-filament::input.wrapper>
                        <x-filament::input.select id="periodeAkademikId" wire:model.live="periodeAkademikId">
                            <option value="">Pilih periode akademik</option>
                            @foreach ($this->periodeOptions as $id => $label)
                                <option value="{{ $id }}">{{ $label }}</option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
                <div class="text-sm text-gray-500">
                    Rombel dan siswa tetap bersama. Sistem hanya mengubah tingkat dan nama rombel.
                    Proses dari kelas XII, lalu XI, lalu X agar nama tujuan tidak bertabrakan.
                </div>
            </div>
            @if ($this->periodeOptions === [])
                <p class="mt-3 text-sm text-warning-600">Belum ada periode akademik. Buat periode tujuan terlebih dahulu di menu Periode Akademik.</p>
            @endif
        </x-filament::section>

        <x-filament::section>
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 class="font-semibold">Rombel aktif</h2>
                    <p class="text-sm text-gray-500">Tinjau nama baru sebelum menaikkan rombel.</p>
                </div>
                <a href="{{ \App\Filament\Resources\Kelas\KelasResource::getUrl('create') }}" class="inline-flex items-center rounded-lg bg-primary-600 px-3 py-2 text-sm font-semibold text-white hover:bg-primary-500">
                    Buat rombel baru
                </a>
            </div>

            @if ($this->kelas->isEmpty())
                <p class="text-sm text-gray-500">Belum ada rombel aktif. Buat rombel terlebih dahulu.</p>
            @else
                <div class="divide-y divide-gray-200 dark:divide-white/10">
                    @foreach ($this->kelas as $kelas)
                        @php
                            $jumlahAktif = $kelas->siswa_aktif_count;
                            $namaBaruDefault = $this->namaRombelTujuanDefault($kelas);
                        @endphp
                        <article class="flex flex-col gap-4 py-4 first:pt-0 last:pb-0 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-48">
                                <h3 class="font-semibold">{{ $kelas->nama }}</h3>
                                <p class="text-sm text-gray-500">Kelas {{ $kelas->tingkat }} · {{ $jumlahAktif }} siswa aktif</p>
                            </div>

                            @if ($kelas->tingkat === 'XII')
                                <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                                    <p class="text-sm text-gray-500">Luluskan seluruh rombel dan arsipkan kelas XII.</p>
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
                                <div class="flex w-full flex-col gap-2 sm:flex-row sm:items-end lg:max-w-2xl">
                                    <div class="min-w-0 flex-1">
                                        <label for="nama-rombel-{{ $kelas->id }}" class="mb-1 block text-xs font-medium text-gray-500">Nama rombel setelah naik ke kelas {{ $kelas->tingkat === 'X' ? 'XI' : 'XII' }}</label>
                                        <x-filament::input.wrapper>
                                            <x-filament::input
                                                id="nama-rombel-{{ $kelas->id }}"
                                                type="text"
                                                wire:model.live="namaRombelBaru.{{ $kelas->id }}"
                                                placeholder="{{ $namaBaruDefault }}"
                                            />
                                        </x-filament::input.wrapper>
                                    </div>
                                    <x-filament::button
                                        icon="heroicon-o-arrow-up-circle"
                                        color="primary"
                                        :disabled="! $periodeAkademikId || $jumlahAktif === 0"
                                        wire:click="prosesKenaikan({{ $kelas->id }})"
                                        wire:confirm="Yakin mengubah {{ $kelas->nama }} menjadi {{ $namaRombelBaru[$kelas->id] ?? $namaBaruDefault }}? Semua siswa tetap di rombel ini."
                                    >
                                        Naikkan rombel
                                    </x-filament::button>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </x-filament::section>
    </div>
</x-filament-panels::page>
