<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Hasil Kompetensi {{ $siswa->nama }} - TJKT CHECK
    </title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        @media print {
            body {
                background: white !important;
            }

            .no-print {
                display: none !important;
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
            }
        }
    </style>
</head>

<body class="min-h-screen bg-slate-100 text-slate-800">

    {{-- Navbar --}}
    <header class="no-print border-b border-blue-800 bg-blue-700 text-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">

            <a
                href="{{ route('cek-siswa.index') }}"
                class="flex items-center gap-3"
            >
                <div class="flex h-11 w-11 items-center justify-center rounded-xl bg-white/15 text-xl font-bold ring-1 ring-white/20">
                    T
                </div>

                <div>
                    <h1 class="text-lg font-bold leading-tight sm:text-xl">
                        TJKT CHECK
                    </h1>

                    <p class="text-xs text-blue-100 sm:text-sm">
                        Portal Kompetensi Siswa
                    </p>
                </div>
            </a>

            <a
                href="{{ route('cek-siswa.index') }}"
                class="hidden rounded-lg border border-white/20 bg-white/10 px-4 py-2 text-sm font-semibold transition hover:bg-white/20 sm:inline-flex"
            >
                Cari Siswa Lain
            </a>
        </div>
    </header>

    <main class="print-container mx-auto max-w-6xl px-4 py-6 sm:px-6 sm:py-10 lg:px-8">

        {{-- Tombol navigasi --}}
        <div class="no-print mb-5 flex gap-3">
            <a
                href="{{ route('cek-siswa.index') }}"
                class="inline-flex flex-1 items-center justify-center rounded-xl bg-slate-700 px-4 py-3 text-sm font-semibold text-white transition hover:bg-slate-800 sm:flex-none"
            >
                ← Kembali
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="inline-flex flex-1 items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 sm:flex-none"
            >
                🖨 Cetak
            </button>
        </div>

        {{-- Profil siswa --}}
        <section class="print-shadow-none overflow-hidden rounded-2xl bg-white shadow-sm">

            <div class="bg-gradient-to-r from-blue-700 to-indigo-700 px-5 py-6 text-white sm:px-8 sm:py-8">

                <div class="flex flex-col gap-6 sm:flex-row sm:items-center sm:justify-between">

                    <div class="flex items-center gap-4">

                        <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-2xl bg-white/15 text-2xl font-bold ring-1 ring-white/20 sm:h-20 sm:w-20 sm:text-3xl">
                            {{ mb_strtoupper(mb_substr($siswa->nama, 0, 1)) }}
                        </div>

                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">
                                Data Peserta Didik
                            </p>

                            <h2 class="mt-1 break-words text-2xl font-bold sm:text-3xl">
                                {{ $siswa->nama }}
                            </h2>

                            <p class="mt-1 text-sm text-blue-100">
                                Teknik Jaringan Komputer dan Telekomunikasi
                            </p>
                        </div>
                    </div>

                    <div class="rounded-xl border border-white/15 bg-white/10 px-5 py-4 sm:text-right">
                        <p class="text-xs font-semibold uppercase tracking-wider text-blue-100">
                            Progres Kelulusan
                        </p>

                        <p class="mt-1 text-4xl font-bold">
                            {{ $persentase }}%
                        </p>
                    </div>
                </div>
            </div>

            <div class="grid gap-px bg-slate-200 sm:grid-cols-3">

                <div class="bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Nomor Induk Siswa
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $siswa->nis }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Kelas
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $siswa->rombel?->nama ?? '-' }}
                    </p>
                </div>

                <div class="bg-white p-5">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-400">
                        Tingkat
                    </p>

                    <p class="mt-2 font-bold text-slate-900">
                        {{ $siswa->rombel?->tingkat ?? '-' }}
                    </p>
                </div>
            </div>
        </section>

        {{-- Ringkasan --}}
        <section class="mt-6">

            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                    Ringkasan Akademik
                </p>

                <h2 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">
                    Perkembangan Kompetensi
                </h2>
            </div>

            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100 text-lg">
                        📚
                    </div>

                    <p class="mt-4 text-2xl font-bold text-slate-900">
                        {{ $totalMateri }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Total Materi
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-green-100 text-lg">
                        ✅
                    </div>

                    <p class="mt-4 text-2xl font-bold text-green-700">
                        {{ $lulus }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Sudah Lulus
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-amber-100 text-lg">
                        📝
                    </div>

                    <p class="mt-4 text-2xl font-bold text-amber-700">
                        {{ $sudahDinilai ?? 0 }}
                    </p>

                    <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                        Sudah Diuji
                    </p>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm sm:p-5">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-lg">
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

            <div class="mt-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <p class="font-bold text-slate-900">
                            Progres Kelulusan Kompetensi
                        </p>

                        <p class="mt-1 text-xs text-slate-500 sm:text-sm">
                            {{ $lulus }} dari {{ $totalMateri }} materi telah dinyatakan lulus.
                        </p>
                    </div>

                    <p class="text-xl font-bold text-blue-700">
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
        <section class="mt-8">

            <div class="mb-5 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">
                        Daftar Pembelajaran
                    </p>

                    <h2 class="mt-1 text-xl font-bold text-slate-900 sm:text-2xl">
                        Materi Kompetensi
                    </h2>
                </div>

                <p class="text-sm text-slate-500">
                    Tingkat {{ $siswa->rombel?->tingkat ?? '-' }}
                </p>
            </div>

            <div class="space-y-4">

                @forelse ($materis ?? [] as $materi)

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
                            overflow-hidden rounded-2xl border bg-white shadow-sm

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
                                                <span class="rounded-full bg-blue-50 px-3 py-1 text-xs font-semibold text-blue-700">
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
                                            <div class="mt-4 grid gap-3 rounded-xl bg-slate-50 p-4 text-sm sm:grid-cols-2">

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
                                            <div class="mt-4 rounded-xl bg-slate-50 p-4 text-sm text-slate-500">
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
                                                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700 md:w-auto"
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

                @empty

                    <div class="rounded-2xl border border-dashed border-slate-300 bg-white p-8 text-center shadow-sm sm:p-12">
                        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-slate-100 text-3xl">
                            📚
                        </div>

                        <h3 class="mt-4 text-lg font-bold text-slate-900">
                            Belum Ada Materi
                        </h3>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-slate-500">
                            Belum ada materi kompetensi untuk tingkat
                            {{ $siswa->rombel?->tingkat ?? '-' }}.
                        </p>
                    </div>

                @endforelse
            </div>
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
                        Nilai siswa tidak ditampilkan pada halaman ini.
                    </p>
                </div>
            </div>
        </section>
    </main>

    <footer class="mt-10 border-t border-slate-200 bg-white">
        <div class="mx-auto max-w-6xl px-4 py-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
            <p>
                © {{ date('Y') }} TJKT CHECK · Sistem Monitoring Kompetensi Siswa
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