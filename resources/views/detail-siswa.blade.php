<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Hasil Kompetensi {{ $siswa->nama }} - {{ $appTitle }}
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        .print-only {
            display: none;
        }

        @media print {
            @page {
                size: A4 portrait;
                margin: 12mm;
            }

            html,
            body {
                background: white !important;
                color: #0f172a !important;
                font-size: 10pt;
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }

            .no-print {
                display: none !important;
            }

            .print-only {
                display: block !important;
            }

            .print-hide-value {
                display: none !important;
            }

            .print-signature {
                width: 240px;
                max-width: 48%;
                margin: 28pt 0 0 auto;
                padding-top: 4pt;
                text-align: center;
                break-inside: avoid;
            }

            .print-signature-line {
                margin-top: 54pt;
                padding-top: 5pt;
                border-top: 1px solid #64748b;
                font-weight: 600;
            }

            .print-shadow-none {
                box-shadow: none !important;
            }

            .print-break-inside-avoid {
                break-inside: avoid;
                page-break-inside: avoid;
            }

            .print-container {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }

            main > section {
                margin-top: 12pt !important;
            }

            main > section:first-of-type {
                margin-top: 0 !important;
            }

            main > section > div.grid {
                gap: 6pt !important;
            }

            main > section > div.grid > div,
            main > section > div.mt-4 {
                padding: 9pt !important;
                box-shadow: none !important;
            }

            main > section > div.space-y-4 {
                display: block;
            }

            main > section > div.space-y-4 > article {
                margin-bottom: 8pt;
                border-color: #cbd5e1 !important;
                box-shadow: none !important;
            }

            main > section > div.space-y-4 > article > div > div:last-child {
                padding: 9pt !important;
            }

            h2,
            h3,
            p {
                orphans: 3;
                widows: 3;
            }

            footer {
                display: none !important;
            }
        }

    </style>
</head>

<body class="min-h-screen bg-slate-50 text-slate-800 antialiased">

    {{-- Navbar --}}
    <header class="no-print sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 text-slate-900 shadow-sm backdrop-blur-xl">
        <div class="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 sm:px-6 lg:px-8">

            <a
                href="{{ route('cek-siswa.index') }}"
                class="flex items-center gap-3"
            >
                @if ($appLogo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($appLogo) }}" alt="Logo {{ $appTitle }}" class="h-11 w-11 rounded-2xl border border-slate-200 bg-white object-contain p-1 shadow-sm">
                @else
                    <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-blue-50 text-xl font-bold text-blue-700 ring-1 ring-blue-100">
                        {{ mb_strtoupper(mb_substr($appTitle, 0, 1)) }}
                    </div>
                @endif

                <div>
                    <h1 class="text-base font-extrabold leading-tight tracking-tight text-slate-950 sm:text-lg">
                        {{ $appTitle }}
                    </h1>

                    <p class="text-xs text-slate-500 sm:text-sm">
                        Portal Kompetensi Siswa
                    </p>
                </div>
            </a>

            <a
                href="{{ route('cek-siswa.index') }}"
                class="hidden items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-100 sm:inline-flex"
            >
                Cari Siswa Lain
            </a>
        </div>
    </header>

    <main class="print-container mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-9 lg:px-8">

        <div class="print-only mb-5 border-b-2 border-blue-700 pb-3">
            <p class="text-xs font-bold uppercase tracking-widest text-blue-700">{{ $appTitle }}</p>
            <h1 class="mt-1 text-2xl font-bold text-slate-900">Rapor Kompetensi Siswa</h1>
            <p class="mt-1 text-xs text-slate-500">Rekap capaian kompetensi praktik program keahlian TJKT</p>
        </div>

        {{-- Tombol navigasi --}}
        <div class="no-print mb-5 flex gap-3">
            <a
                href="{{ route('cek-siswa.index') }}"
                class="inline-flex flex-1 items-center justify-center rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 hover:text-blue-800 sm:flex-none"
            >
                ← Kembali
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex flex-1 items-center justify-center rounded-xl bg-blue-700 px-4 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 sm:flex-none"
            >
                🖨 Cetak
            </button>
        </div>

        {{-- Profil siswa --}}
        <section class="print-shadow-none overflow-hidden rounded-3xl border border-slate-200/80 bg-white shadow-[0_18px_55px_-35px_rgba(15,23,42,0.3)]">

            <div class="bg-gradient-to-r from-white via-blue-50/70 to-indigo-50/80 px-5 py-6 text-slate-900 sm:px-8 sm:py-8">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-blue-700 text-2xl font-extrabold text-white shadow-lg shadow-blue-700/20 ring-4 ring-white sm:h-20 sm:w-20 sm:text-3xl">
                            {{ mb_strtoupper(mb_substr($siswa->nama, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">
                                {{ $siswa->status === 'alumni' ? 'Alumni' : 'Data Peserta Didik' }}
                            </p>

                            <h2 class="mt-1 break-words text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                                {{ $siswa->nama }}
                            </h2>

                            <p class="mt-1 text-sm text-slate-500 sm:text-base">
                                Teknik Jaringan Komputer dan Telekomunikasi
                            </p>
                        </div>
                    </div>

                    <div class="flex min-w-44 items-center gap-4 rounded-2xl border border-blue-100 bg-white/80 px-5 py-4 shadow-sm sm:justify-between">
                        <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-slate-500">
                            Progres Kelulusan
                        </p>

                        <p class="mt-1 text-4xl font-extrabold tracking-tight text-blue-700">
                            {{ $persentase }}%
                        </p>
                        </div>
                        <div class="h-14 w-14 shrink-0 rounded-full p-[5px]" style="background: conic-gradient(#2563eb {{ min(max($persentase, 0), 100) }}%, #e2e8f0 0)">
                            <div class="h-full w-full rounded-full border-4 border-white bg-blue-50"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid gap-px border-t border-slate-200 bg-slate-200 sm:grid-cols-2 xl:grid-cols-4">

                <div class="bg-white p-5 sm:px-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nomor Induk Siswa
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $siswa->nis }}
                    </p>
                </div>

                <div class="bg-white p-5 sm:px-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Kelas
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $namaKelasTampil }}
                    </p>
                </div>

                <div class="bg-white p-5 sm:px-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Tingkat
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $tingkatTampil }}
                    </p>
                </div>

                <div class="bg-white p-5 sm:px-6">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">Status</p>
                    <p class="mt-2 font-bold {{ $siswa->status === 'alumni' ? 'text-indigo-700' : 'text-emerald-700' }}">
                        {{ $siswa->status === 'alumni' ? 'Alumni' : 'Aktif' }}
                        @if ($siswa->tanggal_lulus)
                            <span class="block text-xs font-normal text-slate-500">Lulus {{ $siswa->tanggal_lulus->format('d/m/Y') }}</span>
                        @endif
                    </p>
                </div>
            </div>
        </section>

        @if ($siswa->status === 'alumni')
            <section class="mt-8 rounded-3xl border border-indigo-100 bg-white p-5 shadow-sm sm:p-6">
                <div class="mb-4">
                    <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Arsip Akademik</p>
                    <h2 class="mt-1 text-xl font-bold text-slate-900">Riwayat Nilai per Materi</h2>
                    <p class="mt-1 text-sm text-slate-500">Nilai dan periode tetap tersimpan meskipun siswa sudah menjadi alumni.</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full min-w-[720px] divide-y divide-slate-200 text-left text-sm">
                        <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                            <tr>
                                <th class="px-3 py-3">Periode</th>
                                <th class="px-3 py-3">Kelas</th>
                                <th class="px-3 py-3">Materi</th>
                                <th class="px-3 py-3">Tanggal Uji</th>
                                <th class="print-hide-value px-3 py-3">Nilai</th>
                                <th class="px-3 py-3">Penguji</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @if ($riwayatNilai->isNotEmpty())
                                @foreach ($riwayatNilai as $penilaian)
                                <tr>
                                    <td class="px-3 py-3">{{ $penilaian->periodeAkademik?->label ?? 'Periode lama' }}</td>
                                    <td class="px-3 py-3">{{ $penilaian->kelas_nama_snapshot ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $penilaian->materi?->nama ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $penilaian->tanggal_uji?->format('d/m/Y') ?? $penilaian->tanggal_uji }}</td>
                                    <td class="print-hide-value px-3 py-3 font-semibold">{{ $penilaian->nilai ?? '-' }}</td>
                                    <td class="px-3 py-3">{{ $penilaian->user?->name ?? '-' }}</td>
                                </tr>
                                @endforeach
                            @else
                                <tr><td colspan="6" class="px-3 py-5 text-center text-slate-500">Belum ada nilai yang tercatat.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </section>
        @endif

        {{-- Ringkasan --}}
        <section class="mt-8">

            <div class="mb-4">
                <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">
                    Ringkasan Akademik
                </p>

                <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                    Perkembangan Kompetensi
                </h2>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-blue-50 text-lg ring-1 ring-blue-100">
                        📚
                    </div>

                    <p class="mt-4 text-2xl font-bold text-slate-900">
                        {{ $totalMateri }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Total Materi
                    </p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-emerald-50 text-lg ring-1 ring-emerald-100">
                        ✅
                    </div>

                    <p class="mt-4 text-2xl font-bold text-green-700">
                        {{ $lulus }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Sudah Lulus
                    </p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-amber-50 text-lg ring-1 ring-amber-100">
                        📝
                    </div>

                    <p class="mt-4 text-2xl font-bold text-amber-700">
                        {{ $sudahDinilai ?? 0 }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Sudah Diuji
                    </p>
                </div>

                <div class="rounded-3xl border border-slate-200 bg-white p-4 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-2xl bg-slate-100 text-lg ring-1 ring-slate-200">
                        ⏳
                    </div>

                    <p class="mt-4 text-2xl font-bold text-slate-700">
                        {{ max($totalMateri - ($sudahDinilai ?? 0), 0) }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Belum Diuji
                    </p>
                </div>
            </div>

            <div class="mt-4 rounded-3xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-slate-900">
                            Progres Kelulusan Kompetensi
                        </p>

                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ $lulus }} dari {{ $totalMateri }} materi telah dinyatakan lulus.
                        </p>
                    </div>

                    <p class="text-2xl font-extrabold tracking-tight text-blue-700">
                        {{ $persentase }}%
                    </p>
                </div>

                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200">
                    <div
                        class="h-full rounded-full bg-gradient-to-r from-blue-600 to-green-500"
                        style="width: {{ min(max($persentase, 0), 100) }}%"
                    ></div>
                </div>
            </div>
        </section>

        {{-- Materi kompetensi --}}
        <section class="mt-10">

            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-[0.16em] text-blue-700">
                        Daftar Pembelajaran
                    </p>

                    <h2 class="mt-1 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                        Materi Kompetensi
                    </h2>
                </div>

                <p class="text-sm text-slate-500">
                    Tingkat {{ $siswa->rombel?->tingkat ?? '-' }}
                </p>
            </div>

            <div class="space-y-4">

                @if ($materisUrut->isEmpty())
                    <div class="rounded-3xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm sm:p-12">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl">
                            📚
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-slate-900">Belum Ada Materi</h3>
                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            Belum ada materi kompetensi untuk tingkat {{ $siswa->rombel?->tingkat ?? '-' }}.
                        </p>
                    </div>
                @else
                    @foreach ($materisUrut as $materi)

                    @php
                        $kelulusan = isset($penilaianByMateri)
                            ? $penilaianByMateri->get($materi->id)
                            : $siswa->kelulusans
                                ->where('materi_id', $materi->id)
                                ->first();

                        $sudahDiuji = $kelulusan !== null;

                        $sudahLulus = $sudahDiuji
                            && ! is_null($kelulusan->nilai)
                            && $kelulusan->nilai >= 75;
                    @endphp

                    <article
                        class="
                            print-break-inside-avoid
                            overflow-hidden rounded-3xl border bg-white shadow-sm transition-shadow hover:shadow-md

                            @if ($sudahLulus)
                                border-green-200
                            @elseif ($sudahDiuji)
                                border-amber-200
                            @else
                                border-slate-200
                            @endif
                        "
                    >
                        <div class="flex flex-col sm:flex-row">

                            <div
                                class="
                                    h-2 w-full sm:h-auto sm:w-2

                                    @if ($sudahLulus)
                                        bg-green-500
                                    @elseif ($sudahDiuji)
                                        bg-amber-500
                                    @else
                                        bg-slate-300
                                    @endif
                                "
                            ></div>

                            <div class="flex-1 p-5 sm:p-6">

                                <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

                                    <div class="min-w-0 flex-1">

                                        <div class="flex flex-wrap items-center gap-2">

                                            <span
                                                class="
                                                    inline-flex rounded-full px-3 py-1 text-xs font-semibold

                                                    @if ($sudahLulus)
                                                        bg-green-100 text-green-700
                                                    @elseif ($sudahDiuji)
                                                        bg-amber-100 text-amber-700
                                                    @else
                                                        bg-slate-100 text-slate-600
                                                    @endif
                                                "
                                            >
                                                @if ($sudahLulus)
                                                    ✓ Lulus
                                                @elseif ($sudahDiuji)
                                                    Belum Lulus
                                                @else
                                                    Belum Diuji
                                                @endif
                                            </span>

                                            @if ($materi->kode)
                                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-bold text-blue-700 ring-1 ring-blue-100">
                                                    {{ $materi->kode }}
                                                </span>
                                            @endif
                                        </div>

                                        <h3 class="mt-3 break-words text-lg font-bold text-slate-900 sm:text-xl">
                                            {{ $materi->nama }}
                                        </h3>

                                        @if ($materi->deskripsi)
                                            <p class="mt-2 whitespace-pre-line text-sm leading-6 text-slate-500">
                                                {{ $materi->deskripsi }}
                                            </p>
                                        @endif

                                        @if ($sudahDiuji)
                                            <div class="mt-4 grid gap-3 rounded-2xl bg-slate-50 p-4 text-sm ring-1 ring-slate-100 sm:grid-cols-2">

                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                        Penguji
                                                    </p>

                                                    <p class="mt-1 font-semibold text-slate-700">
                                                        {{ $kelulusan->user?->name ?? '-' }}
                                                    </p>
                                                </div>

                                                <div>
                                                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                                                        Tanggal Pengujian
                                                    </p>

                                                    <p class="mt-1 font-semibold text-slate-700">
                                                        @if ($kelulusan->tanggal_uji)
                                                            {{ \Illuminate\Support\Carbon::parse($kelulusan->tanggal_uji)->translatedFormat('d F Y') }}
                                                        @else
                                                            -
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="mt-4 rounded-2xl bg-slate-50 p-4 text-sm text-slate-500 ring-1 ring-slate-100">
                                                Kompetensi ini belum dilakukan pengujian.
                                                Siswa tetap dapat mempelajari materi yang tersedia.
                                            </div>
                                        @endif
                                    </div>

                                    <div class="no-print w-full shrink-0 md:w-auto">
                                        @if ($materi->pdf_file)
                                            <a
                                                href="{{ asset('storage/' . $materi->pdf_file) }}"
                                                target="_blank"
                                                rel="noopener noreferrer"
                                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-700 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 md:w-auto"
                                            >
                                                📄 Buka Materi PDF
                                            </a>
                                        @else
                                            <div class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-slate-100 px-5 py-3 text-sm font-semibold text-slate-400 md:w-auto">
                                                📄 PDF Belum Tersedia
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </article>

                    @endforeach
                @endif
            </div>
        </section>

        <section class="print-only print-signature">
            <p>{{ now()->translatedFormat('d F Y') }}</p>
            <div class="print-signature-line">Penguji</div>
        </section>

        <section class="no-print mt-8 rounded-2xl border border-blue-100 bg-blue-50 p-5">
            <div class="flex items-start gap-3">
                <div class="text-xl">
                    ℹ️
                </div>

                <div>
                    <p class="font-bold text-blue-900">
                        Informasi
                    </p>

                    <p class="mt-1 text-sm leading-6 text-blue-700">
                        Status kompetensi diperbarui oleh guru penguji.
                        @if ($siswa->status === 'alumni')
                            Riwayat nilai alumni tetap tersedia untuk ditinjau.
                        @else
                            Nilai siswa tidak ditampilkan pada halaman ini.
                        @endif
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
            <p>
                © {{ date('Y') }} {{ $appTitle }} · Sistem Monitoring Kompetensi Siswa
            </p>

            <p class="mt-1">
                Designed &amp; Developed by

                <a
                    href="https://instagram.com/alynoval10"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="font-semibold text-blue-600 hover:underline"
                >
                    Noval Aly, S.T.
                </a>
            </p>
        </div>
    </footer>

</body>

</html>
