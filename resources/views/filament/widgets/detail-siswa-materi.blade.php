<x-filament-widgets::widget>

    <x-filament::section>

        <x-slot name="heading">
            Detail Siswa per Materi
        </x-slot>

        <x-slot name="description">
            Lihat siswa yang lulus, belum lulus, dan belum diuji pada materi tertentu.
        </x-slot>

        @if (! $kelas)

            <p>Tidak ada kelas yang tersedia.</p>

        @elseif ($materis->isEmpty())

            <p>
                Belum ada materi untuk tingkat {{ $kelas->tingkat }}.
            </p>

        @else

            <div style="margin-bottom: 24px; max-width: 420px;">

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

                    <x-filament::input.select
                        wire:model.live="materiId"
                        wire:key="materi-{{ $kelas->id }}"
                    >

                        @foreach ($materis as $item)

                            <option value="{{ $item->id }}">
                                {{ $item->nama }}
                            </option>

                        @endforeach

                    </x-filament::input.select>

                </x-filament::input.wrapper>

            </div>

            @if ($materi)

                <div
                    style="
                        margin-bottom:20px;
                        font-size:14px;
                    "
                >
                    Kelas:
                    <strong>{{ $kelas->nama }}</strong>

                    &nbsp;•&nbsp;

                    Materi:
                    <strong>{{ $materi->nama }}</strong>

                    &nbsp;•&nbsp;

                    Total siswa:
                    <strong>
                        {{ $lulus->count() + $belumLulus->count() + $belumDiuji->count() }}
                    </strong>
                </div>

                <style>
                    .detail-kompetensi-grid {
                        display: grid;
                        grid-template-columns: repeat(3, minmax(0, 1fr));
                        gap: 16px;
                    }

                    .detail-kompetensi-list {
                        display: flex;
                        flex-direction: column;
                        gap: 8px;
                        margin-top: 14px;
                    }

                    .detail-kompetensi-row {
                        display: flex;
                        justify-content: space-between;
                        align-items: center;
                        gap: 12px;
                        padding: 9px 0;
                        border-bottom: 1px solid rgba(128, 128, 128, .18);
                    }

                    .detail-kompetensi-row:last-child {
                        border-bottom: 0;
                    }

                    @media (max-width: 900px) {
                        .detail-kompetensi-grid {
                            grid-template-columns: 1fr;
                        }
                    }
                </style>

                <div class="detail-kompetensi-grid">

                    {{-- LULUS --}}
                    <x-filament::section>

                        <x-slot name="heading">
                            Lulus
                        </x-slot>

                        <x-slot name="afterHeader">
                            <x-filament::badge color="success">
                                {{ $lulus->count() }} siswa
                            </x-filament::badge>
                        </x-slot>

                        @if ($lulus->isEmpty())

                            <p style="font-size:14px; opacity:.7;">
                                Belum ada siswa.
                            </p>

                        @else

                            <div class="detail-kompetensi-list">

                                @foreach ($lulus as $siswa)

                                    <div class="detail-kompetensi-row">

                                        <span>
                                            {{ $siswa['nama'] }}
                                        </span>

                                        <x-filament::badge color="success">
                                            {{ $siswa['nilai'] }}
                                        </x-filament::badge>

                                    </div>

                                @endforeach

                            </div>

                        @endif

                    </x-filament::section>


                    {{-- BELUM LULUS --}}
                    <x-filament::section>

                        <x-slot name="heading">
                            Belum Lulus
                        </x-slot>

                        <x-slot name="afterHeader">
                            <x-filament::badge color="danger">
                                {{ $belumLulus->count() }} siswa
                            </x-filament::badge>
                        </x-slot>

                        @if ($belumLulus->isEmpty())

                            <p style="font-size:14px; opacity:.7;">
                                Tidak ada siswa.
                            </p>

                        @else

                            <div class="detail-kompetensi-list">

                                @foreach ($belumLulus as $siswa)

                                    <div class="detail-kompetensi-row">

                                        <span>
                                            {{ $siswa['nama'] }}
                                        </span>

                                        <x-filament::badge color="danger">
                                            {{ $siswa['nilai'] }}
                                        </x-filament::badge>

                                    </div>

                                @endforeach

                            </div>

                        @endif

                    </x-filament::section>


                    {{-- BELUM DIUJI --}}
                    <x-filament::section>

                        <x-slot name="heading">
                            Belum Diuji
                        </x-slot>

                        <x-slot name="afterHeader">
                            <x-filament::badge color="gray">
                                {{ $belumDiuji->count() }} siswa
                            </x-filament::badge>
                        </x-slot>

                        @if ($belumDiuji->isEmpty())

                            <p style="font-size:14px; opacity:.7;">
                                Semua siswa sudah diuji.
                            </p>

                        @else

                            <div class="detail-kompetensi-list">

                                @foreach ($belumDiuji as $siswa)

                                    <div class="detail-kompetensi-row">

                                        <span>
                                            {{ $siswa['nama'] }}
                                        </span>

                                        <x-filament::badge color="gray">
                                            Belum
                                        </x-filament::badge>

                                    </div>

                                @endforeach

                            </div>

                        @endif

                    </x-filament::section>

                </div>

            @endif

        @endif

    </x-filament::section>

</x-filament-widgets::widget>