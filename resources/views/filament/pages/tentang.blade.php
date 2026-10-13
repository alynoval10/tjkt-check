<x-filament-panels::page>
    <div class="mx-auto w-full max-w-5xl space-y-5">
        <x-filament::section>
            <div class="mx-auto max-w-3xl py-4 text-center">
                @if ($appLogo)
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) }}"
                        alt="Logo {{ $appTitle }}"
                        class="mx-auto mb-4 h-20 w-20 rounded-2xl object-contain shadow-sm"
                    >
                @else
                    <div class="mx-auto mb-4 flex h-20 w-20 items-center justify-center rounded-2xl bg-primary-600 text-2xl font-bold text-white shadow-sm">
                        {{ mb_strtoupper(mb_substr($appTitle, 0, 2)) }}
                    </div>
                @endif

                <h2 class="text-2xl font-bold text-gray-950 dark:text-white">{{ $appTitle }}</h2>
                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    Sistem pemantauan kompetensi siswa TJKT untuk mengelola materi, penilaian praktik,
                    dan perkembangan belajar siswa.
                </p>
            </div>
        </x-filament::section>

        <div class="grid gap-5 md:grid-cols-2">
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
            <div class="grid gap-x-8 gap-y-3 text-sm text-gray-600 dark:text-gray-400 md:grid-cols-2">
                <p>• Pengelolaan kelas, rombel, siswa, dan materi TJKT</p>
                <p>• Penilaian kompetensi per materi dan penilaian massal</p>
                <p>• Pemantauan hasil belajar berdasarkan periode akademik</p>
                <p>• Kenaikan kelas dan kelulusan siswa kelas XII menjadi alumni</p>
                <p>• Rapor kompetensi yang dapat dicetak atau disimpan sebagai PDF</p>
                <p>• Riwayat nilai materi yang tetap tersedia untuk alumni</p>
                <p>• Portal untuk mengecek kompetensi siswa</p>
                <p>• Ekspor data dan pencadangan aplikasi</p>
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
