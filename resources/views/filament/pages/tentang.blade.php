<x-filament-panels::page>
    <style>
        .about-page { width: 100%; max-width: 64rem; margin: 0 auto; display: flex; flex-direction: column; gap: 1.25rem; }
        .about-intro { display: flex; align-items: center; gap: 1.25rem; padding: .5rem 0; }
        .about-logo { width: 5rem; height: 5rem; flex: 0 0 5rem; display: flex; align-items: center; justify-content: center; overflow: hidden; box-sizing: border-box; padding: .5rem; border: 1px solid #e5e7eb; border-radius: 1rem; background: #fff; }
        .about-logo img { display: block; width: 100%; height: 100%; object-fit: contain; }
        .about-initials { width: 5rem; height: 5rem; flex: 0 0 5rem; display: flex; align-items: center; justify-content: center; border-radius: 1rem; background: #2563eb; color: #fff; font-size: 1.5rem; font-weight: 700; }
        .about-eyebrow { margin: 0; color: #2563eb; font-size: .75rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; }
        .about-title { margin: .25rem 0 0; color: #111827; font-size: 1.5rem; font-weight: 700; line-height: 1.25; }
        .about-description { max-width: 48rem; margin: .5rem 0 0; color: #6b7280; font-size: .875rem; line-height: 1.5rem; }
        .about-meta-grid, .about-feature-grid { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1.25rem; }
        .about-meta-value { margin: 0; color: #111827; font-size: 1.125rem; font-weight: 600; }
        .about-feature { display: flex; align-items: flex-start; gap: .75rem; padding: .75rem 1rem; border: 1px solid #e5e7eb; border-radius: .75rem; background: #f9fafb; color: #374151; font-size: .875rem; line-height: 1.35rem; }
        .about-check { width: 1.25rem; height: 1.25rem; flex: 0 0 1.25rem; display: flex; align-items: center; justify-content: center; border-radius: 9999px; background: #dbeafe; color: #1d4ed8; font-size: .75rem; font-weight: 700; }
        @media (max-width: 640px) {
            .about-intro { flex-direction: column; align-items: flex-start; }
            .about-meta-grid, .about-feature-grid { grid-template-columns: 1fr; gap: 1rem; }
        }
        .dark .about-title, .dark .about-meta-value { color: #f9fafb; }
        .dark .about-description, .dark .about-feature { color: #d1d5db; }
        .dark .about-logo { border-color: #374151; }
        .dark .about-feature { border-color: #374151; background: #111827; }
        .dark .about-check { background: #1e3a8a; color: #bfdbfe; }
    </style>

    <div class="about-page">
        <x-filament::section>
            <div class="about-intro">
                @if ($appLogo)
                    <div class="about-logo">
                        <img
                            src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) }}"
                            alt="Logo {{ $appTitle }}"
                        >
                    </div>
                @else
                    <div class="about-initials">
                        {{ mb_strtoupper(mb_substr($appTitle, 0, 2)) }}
                    </div>
                @endif

                <div style="min-width: 0;">
                    <p class="about-eyebrow">Portal Kompetensi Siswa</p>
                    <h2 class="about-title">{{ $appTitle }}</h2>
                    <p class="about-description">
                        Sistem pemantauan kompetensi siswa TJKT untuk mengelola materi, penilaian praktik,
                        dan perkembangan belajar siswa.
                    </p>
                </div>
            </div>
        </x-filament::section>

        <div class="about-meta-grid">
            <x-filament::section>
                <x-slot name="heading">Versi Aplikasi</x-slot>
                <p class="about-meta-value">{{ $appVersion }}</p>
            </x-filament::section>

            <x-filament::section>
                <x-slot name="heading">Pembuat</x-slot>
                <p class="about-meta-value">Noval Aly, S.T.</p>
            </x-filament::section>
        </div>

        <x-filament::section>
            <x-slot name="heading">Fungsi Utama</x-slot>
            <div class="about-feature-grid">
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
                    <div class="about-feature">
                        <span class="about-check">✓</span>
                        <span>{{ $fungsi }}</span>
                    </div>
                @endforeach
            </div>
        </x-filament::section>
    </div>
</x-filament-panels::page>
