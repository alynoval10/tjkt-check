<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Matriks Kompetensi Siswa
        </x-slot>

        <x-slot name="description">
            Status penguasaan kompetensi seluruh siswa dalam satu kelas.
        </x-slot>

        {{-- Filter lokal matriks; kelas mengikuti Filter Dashboard. --}}
        <div style="display:flex;flex-wrap:wrap;align-items:center;gap:16px;margin-bottom:16px">
            <div style="flex:1;min-width:180px">
                <label for="rekap-materi">Materi</label>
                <x-filament::input.wrapper>
                    <x-filament::input.select id="rekap-materi" wire:model.live="materiId" :disabled="! $kelasIdAktif">
                        <option value="">Semua materi</option>
                        @foreach ($materiOptions as $id => $nama)
                            <option value="{{ $id }}">{{ $nama }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
            <label style="display:flex;align-items:center;gap:8px">
                <input type="checkbox" wire:model.live="hanyaRemedial">
                Hanya siswa perlu remedial
            </label>
            <x-filament::button wire:click="exportExcel" wire:loading.attr="disabled" icon="heroicon-o-arrow-down-tray" :disabled="empty($siswas) || empty($materis)">
                Ekspor Excel
            </x-filament::button>
        </div>

        @if (! $kelasIdAktif)

            <p>
                Tidak ada kelas yang tersedia.
            </p>

        @elseif (empty($materis))

            <p>
                Belum ada materi untuk tingkat {{ $tingkatKelas }}.
            </p>

        @elseif (empty($siswas))

            <p>
                {{ $hanyaRemedial ? 'Tidak ada siswa yang perlu remedial pada filter ini.' : 'Belum ada siswa di kelas '.$namaKelas.'.' }}
            </p>

        @else

            <div
                style="
                    display:flex;
                    flex-wrap:wrap;
                    gap:16px;
                    margin-bottom:16px;
                    font-size:14px;
                "
            >

                <span>
                    Kelas:
                    <strong>{{ $namaKelas }}</strong>
                </span>

                <span>
                    Jumlah Siswa:
                    <strong>{{ count($siswas) }}</strong>
                </span>

                <span>
                    Jumlah Materi:
                    <strong>{{ count($materis) }}</strong>
                </span>

            </div>

            <div
                style="
                    overflow-x:auto;
                    width:100%;
                "
            >

                <table
                    style="
                        width:100%;
                        min-width:900px;
                        border-collapse:collapse;
                        font-size:14px;
                    "
                >

                    <thead>

                        <tr>

                            <th
                                style="
                                    text-align:left;
                                    padding:12px;
                                    min-width:200px;
                                    border-bottom:1px solid rgba(128,128,128,.25);
                                "
                            >
                                Siswa
                            </th>

                            @foreach ($materis as $materi)

                                <th
                                    style="
                                        text-align:center;
                                        padding:12px;
                                        min-width:140px;
                                        border-bottom:1px solid rgba(128,128,128,.25);
                                    "
                                >
                                    {{ $materi['nama'] }}
                                </th>

                            @endforeach

                            <th
                                style="
                                    text-align:center;
                                    padding:12px;
                                    min-width:100px;
                                    border-bottom:1px solid rgba(128,128,128,.25);
                                "
                            >
                                Progres
                            </th>

                            <th style="text-align:left;padding:12px;min-width:200px">Materi Remedial</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($siswas as $siswa)

                            <tr wire:key="matrix-siswa-{{ $siswa['id'] }}">

                                <td
                                    style="
                                        padding:12px;
                                        font-weight:600;
                                        border-bottom:1px solid rgba(128,128,128,.15);
                                    "
                                >
                                    {{ $siswa['nama'] }}
                                    <div style="font-size:12px;font-weight:400">{{ $siswa['nis'] }}</div>
                                </td>

                                @foreach ($materis as $materi)

                                    @php
                                        $data = $siswa['matrix'][$materi['id']]
                                            ?? [
                                                'status' => 'belum_diuji',
                                                'nilai' => null,
                                            ];
                                    @endphp

                                    <td
                                        style="
                                            text-align:center;
                                            padding:10px;
                                            border-bottom:1px solid rgba(128,128,128,.15);
                                        "
                                    >

                                        @if ($data['status'] === 'lulus')

                                            <x-filament::badge color="success">
                                                ✓ {{ $data['nilai'] }}
                                            </x-filament::badge>

                                        @elseif ($data['status'] === 'belum_lulus')

                                            <x-filament::badge color="danger">
                                                ✕ {{ $data['nilai'] }}
                                            </x-filament::badge>

                                        @else

                                            <x-filament::badge color="gray">
                                                —
                                            </x-filament::badge>

                                        @endif

                                    </td>

                                @endforeach

                                <td
                                    style="
                                        text-align:center;
                                        padding:10px;
                                        border-bottom:1px solid rgba(128,128,128,.15);
                                    "
                                >

                                    @php
                                        $warna = $siswa['progres'] >= 75
                                            ? 'success'
                                            : (
                                                $siswa['progres'] >= 50
                                                    ? 'warning'
                                                    : 'danger'
                                            );
                                    @endphp

                                    <x-filament::badge :color="$warna">
                                        {{ $siswa['progres'] }}%
                                    </x-filament::badge>

                                </td>

                                <td style="padding:12px;border-bottom:1px solid rgba(128,128,128,.15)">
                                    {{ implode(', ', $siswa['remedial']) ?: '-' }}
                                </td>
                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div
                style="
                    display:flex;
                    flex-wrap:wrap;
                    gap:16px;
                    margin-top:16px;
                    font-size:13px;
                "
            >

                <span>✅ Lulus</span>
                <span>❌ Belum Lulus</span>
                <span>⬜ Belum Diuji</span>

            </div>

        @endif

    </x-filament::section>

</x-filament-widgets::widget>
