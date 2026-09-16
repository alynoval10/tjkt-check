<x-filament-panels::page>

    {{-- Lindungi isian lokal, termasuk yang belum dikirim ke Livewire. --}}
    <div class="space-y-6"
        x-data="{
            dirty: false,
            confirmLeave() {
                return !this.dirty || window.confirm('Perubahan belum disimpan. Tinggalkan perubahan?');
            },
            changeSelection(event) {
                if (!event.target.matches('select')) return;
                if (!['kelasId', 'materiId'].includes(event.target.getAttribute('wire:model.live'))) return;
                if (!this.confirmLeave()) {
                    event.stopImmediatePropagation();
                    event.target.value = event.target.getAttribute('wire:model.live') === 'kelasId' ? ($wire.kelasId ?? '') : ($wire.materiId ?? '');
                    return;
                }
                this.dirty = false;
            }
        }"
        x-on:input="if (!$event.target.matches('select') && !$event.target.closest('[data-filter-penilaian]')) dirty = true"
        x-on:change.capture="changeSelection($event)"
        x-on:penilaian-disimpan.window="dirty = false"
        x-on:beforeunload.window="if (dirty) { $event.preventDefault(); $event.returnValue = ''; }"
        x-on:livewire:navigate.document="if (!confirmLeave()) $event.preventDefault()"
    >
        @if ($errors->any())
            <div role="alert" class="text-danger-600">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Kunci isian saat permintaan berjalan agar nilai tidak berubah ketika disimpan. --}}
        <fieldset class="space-y-6" style="min-width:0" wire:loading.attr="disabled" wire:target="simpan,mountAction,callMountedAction,kelasId,materiId,isiSaranCatatan">

        <x-filament::section>

            <x-slot name="heading">
                Penilaian Massal per Materi
            </x-slot>

            <x-slot name="description">
                Pilih kelas dan materi, lalu masukkan nilai seluruh siswa sekaligus.
            </x-slot>

            <div
                style="
                    display:grid;
                    grid-template-columns:repeat(3,minmax(0,1fr));
                    gap:16px;
                "
            >

                {{-- KELAS --}}
                <div>
                    <label
                        style="
                            display:block;
                            font-size:14px;
                            font-weight:600;
                            margin-bottom:8px;
                        "
                    >
                        Kelas
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="kelasId">

                            <option value="">
                                Pilih kelas
                            </option>

                            @foreach ($this->kelasOptions as $id => $nama)
                                <option value="{{ $id }}">
                                    {{ $nama }}
                                </option>
                            @endforeach

                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>


                {{-- MATERI --}}
                <div>
                    <label
                        style="
                            display:block;
                            font-size:14px;
                            font-weight:600;
                            margin-bottom:8px;
                        "
                    >
                        Materi
                    </label>

                    <x-filament::input.wrapper>
                        {{-- Atribut komponen mengatur status materi sebelum kelas dipilih. --}}
                        <x-filament::input.select
                            wire:model.live="materiId"
                            :disabled="! $kelasId"
                        >

                            <option value="">
                                Pilih materi
                            </option>

                            @foreach ($materis as $materi)
                                <option value="{{ $materi->id }}">
                                    {{ $materi->nama }}
                                </option>
                            @endforeach

                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>


                {{-- TANGGAL --}}
                <div>
                    <label
                        style="
                            display:block;
                            font-size:14px;
                            font-weight:600;
                            margin-bottom:8px;
                        "
                    >
                        Tanggal Uji
                    </label>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="date"
                            wire:model="tanggalUji"
                        />
                    </x-filament::input.wrapper>
                </div>

            </div>

        </x-filament::section>


        @if ($kelasId && $materiId)

            <x-filament::section>

                <x-slot name="heading">
                    Daftar Siswa
                </x-slot>

                <x-slot name="description">
                    Nilai yang dikosongkan tidak akan disimpan.
                    Jika penilaian sudah ada, nilainya akan diperbarui.
                </x-slot>

                @if ($siswas->isEmpty())

                    <p style="font-size:14px;">
                        Belum ada siswa pada kelas ini.
                    </p>

                @else

                    {{-- Filter tidak menghapus isian siswa yang disembunyikan. --}}
                    <div data-filter-penilaian style="display:flex;flex-wrap:wrap;gap:16px;margin-bottom:16px">
                        <div style="flex:1;min-width:180px">
                            <label for="cari-siswa-massal">Cari nama atau NIS</label>
                            <x-filament::input.wrapper>
                                <x-filament::input id="cari-siswa-massal" type="search" wire:model.live.debounce.350ms="pencarian" />
                            </x-filament::input.wrapper>
                        </div>
                        <div style="flex:1;min-width:180px">
                            <label for="status-siswa-massal">Status tersimpan</label>
                            <x-filament::input.wrapper>
                                <x-filament::input.select id="status-siswa-massal" wire:model.live="filterStatus">
                                    <option value="semua">Semua</option>
                                    <option value="belum_diuji">Belum Diuji</option>
                                    <option value="remedial">Remedial</option>
                                    <option value="lulus">Lulus</option>
                                </x-filament::input.select>
                            </x-filament::input.wrapper>
                        </div>
                    </div>
                    <p role="status" style="margin-bottom:12px">{{ $this->siswasTampil->count() }} dari {{ $siswas->count() }} siswa</p>

                    <div style="overflow-x:auto;">

                        <table
                            style="
                                width:100%;
                                min-width:900px;
                                border-collapse:collapse;
                            "
                        >

                            <thead>
                                <tr>

                                    <th
                                        style="
                                            text-align:left;
                                            padding:12px;
                                            border-bottom:1px solid rgba(128,128,128,.25);
                                        "
                                    >
                                        Siswa
                                    </th>

                                    <th
                                        style="
                                            width:140px;
                                            text-align:center;
                                            padding:12px;
                                            border-bottom:1px solid rgba(128,128,128,.25);
                                        "
                                    >
                                        Nilai
                                    </th>

                                    <th
                                        style="
                                            text-align:left;
                                            padding:12px;
                                            border-bottom:1px solid rgba(128,128,128,.25);
                                        "
                                    >
                                        Catatan
                                    </th>

                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($this->siswasTampil as $siswa)

                                    <tr wire:key="siswa-{{ $siswa->id }}">

                                        {{-- NAMA SISWA --}}
                                        <td
                                            style="
                                                padding:12px;
                                                border-bottom:1px solid rgba(128,128,128,.15);
                                                font-weight:600;
                                                vertical-align:top;
                                            "
                                        >
                                            {{ $siswa->nama }}
                                            <div style="font-size:12px;font-weight:400">NIS: {{ $siswa->nis }}</div>
                                        </td>


                                        {{-- NILAI --}}
                                        <td
                                            style="
                                                padding:12px;
                                                border-bottom:1px solid rgba(128,128,128,.15);
                                                vertical-align:top;
                                            "
                                        >

                                            <x-filament::input.wrapper>

                                                <x-filament::input
                                                    type="number"
                                                    min="0"
                                                    max="100"
                                                    wire:model="nilai.{{ $siswa->id }}"
                                                />

                                            </x-filament::input.wrapper>

                                        </td>


                                        {{-- CATATAN --}}
                                        <td
                                            style="
                                                padding:12px;
                                                border-bottom:1px solid rgba(128,128,128,.15);
                                                vertical-align:top;
                                            "
                                        >

                                            <div
                                                x-on:click="if ($event.target.closest('button')) dirty = true"
                                                style="
                                                    display:flex;
                                                    flex-wrap:wrap;
                                                    gap:6px;
                                                    margin-bottom:8px;
                                                "
                                            >

                                                <x-filament::button
                                                    type="button"
                                                    size="xs"
                                                    color="success"
                                                    wire:click="isiSaranCatatan({{ $siswa->id }}, 'Kompetensi sudah dikuasai dengan baik.')"
                                                >
                                                    Dikuasai
                                                </x-filament::button>

                                                <x-filament::button
                                                    type="button"
                                                    size="xs"
                                                    color="warning"
                                                    wire:click="isiSaranCatatan({{ $siswa->id }}, 'Perlu meningkatkan ketelitian dalam praktik.')"
                                                >
                                                    Kurang Teliti
                                                </x-filament::button>

                                                <x-filament::button
                                                    type="button"
                                                    size="xs"
                                                    color="danger"
                                                    wire:click="isiSaranCatatan({{ $siswa->id }}, 'Perlu latihan kembali pada bagian konfigurasi.')"
                                                >
                                                    Perlu Latihan
                                                </x-filament::button>

                                            </div>

                                            <x-filament::input.wrapper>

                                                <textarea
                                                    wire:model="catatan.{{ $siswa->id }}"
                                                    rows="2"
                                                    placeholder="Catatan opsional..."
                                                    style="
                                                        width:100%;
                                                        border:0;
                                                        outline:0;
                                                        background:transparent;
                                                        resize:vertical;
                                                        padding:8px;
                                                    "
                                                ></textarea>

                                            </x-filament::input.wrapper>

                                        </td>

                                    </tr>

                                @empty
                                    <tr><td colspan="3" style="padding:16px;text-align:center">Tidak ada siswa yang cocok dengan pencarian dan status ini.</td></tr>
                                @endforelse

                            </tbody>

                        </table>

                    </div>


                    <div
                        style="
                            display:flex;
                            justify-content:flex-end;
                            margin-top:20px;
                        "
                    >

                        {{ $this->konfirmasiSimpanAction }}

                    </div>

                @endif

            </x-filament::section>

        @endif

        </fieldset>
    </div>

</x-filament-panels::page>
