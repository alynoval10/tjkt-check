<x-filament-panels::page>

    <div class="space-y-6">

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

                                @foreach ($siswas as $siswa)

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

                                @endforeach

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

                        <x-filament::button
                            type="button"
                            wire:click="simpan"
                            icon="heroicon-o-check"
                        >
                            Simpan Semua Penilaian
                        </x-filament::button>

                    </div>

                @endif

            </x-filament::section>

        @endif

    </div>

</x-filament-panels::page>
