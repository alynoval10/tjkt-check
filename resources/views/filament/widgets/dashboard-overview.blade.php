<x-filament-widgets::widget>
    <div class="space-y-5">
        <div class="grid gap-4 sm:grid-cols-2 2xl:grid-cols-4">
            @foreach ([
                ['label' => 'Siswa Aktif', 'value' => $jumlah_siswa, 'icon' => 'heroicon-o-users', 'tone' => 'blue', 'hint' => 'Siswa dalam rombel aktif'],
                ['label' => 'Rombel Aktif', 'value' => $jumlah_rombel, 'icon' => 'heroicon-o-building-library', 'tone' => 'green', 'hint' => 'Kelompok belajar berjalan'],
                ['label' => 'Materi Kompetensi', 'value' => $jumlah_materi, 'icon' => 'heroicon-o-book-open', 'tone' => 'violet', 'hint' => 'Sesuai tingkat yang dipilih'],
                ['label' => 'Siswa Perlu Remedial', 'value' => $jumlah_remedial, 'icon' => 'heroicon-o-exclamation-triangle', 'tone' => 'amber', 'hint' => 'Nilai terbaru di bawah 75'],
            ] as $stat)
                <article class="flex items-center justify-between gap-3 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-white/10 dark:bg-gray-900">
                    <div>
                        <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ $stat['label'] }}</p>
                        <p class="mt-2 text-3xl font-bold tracking-tight text-gray-950 dark:text-white">{{ number_format($stat['value']) }}</p>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $stat['hint'] }}</p>
                    </div>
                    <span @class([
                        'flex size-12 shrink-0 items-center justify-center rounded-2xl',
                        'bg-blue-100 text-blue-700 dark:bg-blue-400/15 dark:text-blue-300' => $stat['tone'] === 'blue',
                        'bg-emerald-100 text-emerald-700 dark:bg-emerald-400/15 dark:text-emerald-300' => $stat['tone'] === 'green',
                        'bg-violet-100 text-violet-700 dark:bg-violet-400/15 dark:text-violet-300' => $stat['tone'] === 'violet',
                        'bg-amber-100 text-amber-700 dark:bg-amber-400/15 dark:text-amber-300' => $stat['tone'] === 'amber',
                    ])>
                        <x-filament::icon :icon="$stat['icon']" class="size-6" />
                    </span>
                </article>
            @endforeach
        </div>

        <div class="grid gap-5 2xl:grid-cols-12">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm 2xl:col-span-8 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-5 flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-base font-semibold text-gray-950 dark:text-white">Progres Kompetensi per Materi</h2>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Status berdasarkan nilai pengujian terakhir siswa.</p>
                    </div>
                    <div class="flex flex-wrap gap-x-4 gap-y-2 text-xs text-gray-500 dark:text-gray-400">
                        <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-emerald-500"></i> Kompeten</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-amber-400"></i> Remedial</span>
                        <span class="inline-flex items-center gap-1.5"><i class="size-2.5 rounded-full bg-gray-300 dark:bg-gray-600"></i> Belum diuji</span>
                    </div>
                </div>

                @if ($materi->isEmpty())
                    <div class="rounded-xl border border-dashed border-gray-300 px-5 py-10 text-center text-sm text-gray-500 dark:border-white/15">
                        Belum ada data materi untuk kelas yang dipilih.
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach ($materi as $item)
                            @php
                                $total = max(1, $item['total_siswa']);
                            @endphp
                            <div class="grid gap-2 sm:grid-cols-[minmax(9rem,10rem)_minmax(0,1fr)] sm:items-center sm:gap-4">
                                <p class="truncate text-sm font-medium text-gray-700 dark:text-gray-200" title="{{ $item['label'] }}">{{ $item['label'] }}</p>
                                <div class="flex h-8 overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800" role="img" aria-label="{{ $item['label'] }}: {{ $item['lulus'] }} kompeten, {{ $item['remedial'] }} remedial, {{ $item['belum_diuji'] }} belum diuji">
                                    @if ($item['lulus'] > 0)
                                        <span class="flex items-center justify-center bg-emerald-500 px-1 text-xs font-semibold text-white" style="width: {{ ($item['lulus'] / $total) * 100 }}%">{{ $item['lulus'] }}</span>
                                    @endif
                                    @if ($item['remedial'] > 0)
                                        <span class="flex items-center justify-center bg-amber-400 px-1 text-xs font-semibold text-amber-950" style="width: {{ ($item['remedial'] / $total) * 100 }}%">{{ $item['remedial'] }}</span>
                                    @endif
                                    @if ($item['belum_diuji'] > 0)
                                        <span class="flex items-center justify-center bg-gray-200 px-1 text-xs font-medium text-gray-600 dark:bg-gray-700 dark:text-gray-300" style="width: {{ ($item['belum_diuji'] / $total) * 100 }}%">{{ $item['belum_diuji'] }}</span>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm 2xl:col-span-4 dark:border-white/10 dark:bg-gray-900">
                <div class="mb-4">
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Ringkasan Kelas</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Rata-rata progres kompetensi setiap rombel.</p>
                </div>

                @if ($kelas->isEmpty())
                    <p class="rounded-xl bg-gray-50 p-4 text-sm text-gray-500 dark:bg-white/5 dark:text-gray-400">Belum ada rombel aktif.</p>
                @else
                    <div class="space-y-3">
                        @foreach ($kelas as $rombel)
                            <div class="rounded-xl border border-gray-100 bg-gray-50/80 p-3.5 dark:border-white/5 dark:bg-white/[.03]">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="truncate text-sm font-semibold text-gray-900 dark:text-white">{{ $rombel['nama'] }}</p>
                                    <span class="shrink-0 text-xs text-gray-500 dark:text-gray-400">{{ $rombel['jumlah_siswa'] }} siswa</span>
                                </div>
                                <div class="mt-2.5 flex items-center gap-3">
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                        <div class="h-full rounded-full bg-gradient-to-r from-indigo-500 to-blue-500" style="width: {{ $rombel['progres'] }}%"></div>
                                    </div>
                                    <span class="w-10 text-right text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $rombel['progres'] }}%</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-gray-900">
            <div class="flex flex-wrap items-center justify-between gap-3 border-b border-gray-100 px-5 py-4 dark:border-white/10">
                <div>
                    <h2 class="text-base font-semibold text-gray-950 dark:text-white">Siswa Perlu Perhatian</h2>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Siswa dengan nilai remedial atau progres kompetensi di bawah 50%.</p>
                </div>
                <x-filament::button
                    tag="a"
                    :href="\App\Filament\Resources\Siswas\SiswaResource::getUrl('index')"
                    icon="heroicon-m-arrow-right"
                    icon-position="after"
                    color="gray"
                    size="sm"
                >
                    Lihat siswa
                </x-filament::button>
            </div>

            @if ($perhatian->isEmpty())
                <div class="px-5 py-8 text-center text-sm text-gray-500 dark:text-gray-400">Tidak ada siswa yang memerlukan perhatian pada filter ini.</div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[42rem] text-left text-sm">
                        <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:bg-white/[.03] dark:text-gray-400">
                            <tr>
                                <th class="px-5 py-3">Nama Siswa</th>
                                <th class="px-5 py-3">Kelas</th>
                                <th class="px-5 py-3">Progres Kompetensi</th>
                                <th class="px-5 py-3">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach ($perhatian as $siswa)
                                <tr class="transition hover:bg-gray-50/80 dark:hover:bg-white/[.03]">
                                    <td class="px-5 py-3.5">
                                        <p class="font-semibold text-gray-900 dark:text-white">{{ $siswa['nama'] }}</p>
                                        <p class="mt-0.5 text-xs text-gray-500 dark:text-gray-400">NIS {{ $siswa['nis'] }}</p>
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-600 dark:text-gray-300">{{ $siswa['kelas'] }}</td>
                                    <td class="px-5 py-3.5">
                                        <div class="flex items-center gap-3">
                                            <div class="h-2 w-32 overflow-hidden rounded-full bg-gray-200 dark:bg-gray-700">
                                                <div @class([
                                                    'h-full rounded-full',
                                                    'bg-rose-500' => $siswa['progres'] < 50,
                                                    'bg-amber-400' => $siswa['progres'] >= 50,
                                                ]) style="width: {{ $siswa['progres'] }}%"></div>
                                            </div>
                                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $siswa['progres'] }}%</span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <span @class([
                                            'inline-flex rounded-full px-2.5 py-1 text-xs font-semibold',
                                            'bg-rose-50 text-rose-700 dark:bg-rose-400/10 dark:text-rose-300' => $siswa['remedial'] > 0,
                                            'bg-amber-50 text-amber-700 dark:bg-amber-400/10 dark:text-amber-300' => $siswa['remedial'] === 0,
                                        ])>{{ $siswa['status'] }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </section>
    </div>
</x-filament-widgets::widget>
