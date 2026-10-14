<x-filament-panels::page>
    <div class="mx-auto w-full max-w-5xl space-y-5">
        <x-filament::section>
            <div class="flex flex-col items-center gap-5 py-2 text-center sm:flex-row sm:items-center sm:text-left">
                @if ($appLogo)
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center overflow-hidden rounded-2xl border border-gray-200 bg-white p-2 shadow-sm dark:border-white/10 dark:bg-gray-900">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) }}"
                            alt="Logo {{ $appTitle }}"
                            class="h-full w-full object-contain"
                        >
                    </div>
                @else
                    <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-primary-600 text-2xl font-bold text-white shadow-sm">
                        {{ mb_strtoupper(mb_substr($appTitle, 0, 2)) }}
                    </div>
                @endif

                <div class="min-w-0">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-primary-600 dark:text-primary-400">Portal Kompetensi Siswa</p>
                    <h2 class="mt-1 text-2xl font-bold tracking-tight text-gray-950 dark:text-white">{{ $appTitle }}</h2>
                    <p class="mt-2 max-w-3xl text-sm leading-6 text-gray-600 dark:text-gray-400">
                        Sistem pemantauan kompetensi siswa TJKT untuk mengelola materi, penilaian praktik,
                        dan perkembangan belajar siswa.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
            <x-filament::section>
                <x-slot name="heading">Versi Aplikasi</x-slot>
                <p class="text-xl font-semibold text-gray-950 dark:text-white">{{ $appVersion }}</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Pembuat</x-slot>
                <p class="text-xl font-semibold text-gray-950 dark:text-white">Noval Aly, S.T.</p>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Fungsi Utama</x-slot>
            <div class="grid grid-cols-1 gap-3 text-sm text-gray-700 dark:text-gray-300 sm:grid-cols-2">
                @foreach ([
                    'Pengelolaan kelas, rombel, siswa, dan materi TJKT',
                    'Penilaian kompetensi per materi dan penilaian massal',
                    'Pemantauan hasil belajar berdasarkan periode akademik',
                    'Kenaikan kelas dan kelulusan siswa kelas XII menjadi alumni',
                    'Rapor kompetensi yang dapat dicetak atau disimpan sebagai PDF',
                    'Riwayat nilai materi yang tetap tersedia untuk alumni',
                    'Portal untuk mengecek kompetensi siswa',
                    'Ekspor data dan pencadangan aplikasi',
                ] as $fungsi)
                    <div class="flex items-start gap-3 rounded-xl border border-gray-200 bg-gray-50/70 px-4 py-3 dark:border-white/10 dark:bg-white/[0.03]">
                        <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-primary-100 text-xs font-bold text-primary-700 dark:bg-primary-500/15 dark:text-primary-300">✓</span>
                        <span class="leading-5">{{ $fungsi }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
