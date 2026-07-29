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
                background: white;
            }

            .no-print {
                display: none !important;
            }

            .shadow {
                box-shadow: none !important;
            }

            .print-break-inside-avoid {
                break-inside: avoid;
            }
        }
    </style>
</head>

<body class="bg-slate-100 text-slate-800">

    <div class="mx-auto max-w-5xl px-4 py-10 sm:px-6 lg:px-8">

        {{-- Header --}}
        <div class="rounded-2xl bg-blue-700 p-8 text-white shadow">
            <h1 class="text-3xl font-bold">
                TJKT CHECK
            </h1>

            <p class="mt-1 text-blue-100">
                Monitoring Kompetensi Siswa
            </p>
        </div>

        {{-- Tombol navigasi --}}
        <div class="no-print mt-6 flex flex-wrap gap-3">
            <a
                href="{{ url('/cek-siswa') }}"
                class="rounded-lg bg-gray-600 px-5 py-2 text-white transition hover:bg-gray-700"
            >
                ← Kembali ke Pencarian
            </a>

            <button
                type="button"
                onclick="window.print()"
                class="rounded-lg bg-blue-600 px-5 py-2 text-white transition hover:bg-blue-700"
            >
                🖨 Cetak Hasil
            </button>
        </div>

        {{-- Data siswa --}}
        <div class="mt-6 rounded-2xl bg-white p-8 shadow">
            <div class="flex flex-col gap-6 sm:flex-row sm:justify-between">

                <div>
                    <h2 class="text-3xl font-bold">
                        {{ $siswa->nama }}
                    </h2>

                    <p class="mt-2">
                        NIS:
                        <span class="font-semibold">
                            {{ $siswa->nis }}
                        </span>
                    </p>

                    <p class="mt-1">
                        Kelas:
                        <span class="font-semibold">
                            {{ $siswa->rombel?->nama ?? '-' }}
                        </span>
                    </p>

                    <p class="mt-1">
                        Tingkat:
                        <span class="font-semibold">
                            {{ $siswa->rombel?->tingkat ?? '-' }}
                        </span>
                    </p>
                </div>

                <div class="sm:text-right">
                    <p class="text-gray-500">
                        Progress Kelulusan
                    </p>

                    <h2 class="text-4xl font-bold text-blue-600">
                        {{ $persentase }}%
                    </h2>
                </div>
            </div>

            <div class="mt-6">
                <div class="h-4 w-full overflow-hidden rounded-full bg-gray-200">
                    <div
                        class="h-4 rounded-full bg-green-500 transition-all"
                        style="width: {{ min($persentase, 100) }}%"
                    ></div>
                </div>

                <div class="mt-3 flex flex-wrap gap-x-5 gap-y-2 text-sm text-gray-600">
                    <p>
                        <span class="font-semibold text-green-700">
                            {{ $lulus }}
                        </span>
                        dari
                        <span class="font-semibold">
                            {{ $totalMateri }}
                        </span>
                        materi sudah lulus
                    </p>

                    <p>
                        Sudah diuji:
                        <span class="font-semibold">
                            {{ $sudahDinilai ?? 0 }}
                        </span>
                    </p>

                    <p>
                        Belum diuji:
                        <span class="font-semibold">
                            {{ max($totalMateri - ($sudahDinilai ?? 0), 0) }}
                        </span>
                    </p>
                </div>
            </div>
        </div>

        {{-- Daftar materi --}}
        <div class="mt-6 rounded-2xl bg-white p-8 shadow">
            <div class="mb-6">
                <h2 class="text-2xl font-bold">
                    📚 Materi Kompetensi
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Semua materi untuk tingkat
                    {{ $siswa->rombel?->tingkat ?? '-' }}
                    ditampilkan, baik yang sudah maupun belum diuji.
                </p>
            </div>

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

                <div
                    class="
                        print-break-inside-avoid
                        mb-4 rounded-xl border p-5

                        @if ($sudahLulus)
                            border-green-200 bg-green-50
                        @elseif ($sudahDiuji)
                            border-red-200 bg-red-50
                        @else
                            border-gray-200 bg-gray-50
                        @endif
                    "
                >
                    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">

                        <div class="min-w-0 flex-1">

                            <div class="flex flex-wrap items-center gap-3">
                                <h3
                                    class="
                                        text-lg font-bold

                                        @if ($sudahLulus)
                                            text-green-700
                                        @elseif ($sudahDiuji)
                                            text-red-700
                                        @else
                                            text-gray-700
                                        @endif
                                    "
                                >
                                    @if ($sudahLulus)
                                        ✅
                                    @elseif ($sudahDiuji)
                                        ❌
                                    @else
                                        ○
                                    @endif

                                    {{ $materi->nama }}
                                </h3>

                                @if ($sudahLulus)
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-semibold text-green-700">
                                        Lulus
                                    </span>
                                @elseif ($sudahDiuji)
                                    <span class="rounded-full bg-red-100 px-3 py-1 text-xs font-semibold text-red-700">
                                        Belum Lulus
                                    </span>
                                @else
                                    <span class="rounded-full bg-gray-200 px-3 py-1 text-xs font-semibold text-gray-600">
                                        Belum Diuji
                                    </span>
                                @endif
                            </div>

                            @if ($materi->kode)
                                <p class="mt-2 text-sm text-gray-500">
                                    Kode materi:
                                    <span class="font-medium">
                                        {{ $materi->kode }}
                                    </span>
                                </p>
                            @endif

                            @if ($materi->deskripsi)
                                <p class="mt-3 whitespace-pre-line text-sm leading-relaxed text-gray-600">
                                    {{ $materi->deskripsi }}
                                </p>
                            @endif

                            @if ($sudahDiuji)
                                <div class="mt-4 grid gap-2 text-sm text-gray-600 sm:grid-cols-2">

                                    

                                    <p>
                                        👨‍🏫 Penguji:
                                        <span class="font-medium">
                                            {{ $kelulusan->user?->name ?? '-' }}
                                        </span>
                                    </p>

                                    <p>
                                        📅 Tanggal:
                                        <span class="font-medium">
                                            @if ($kelulusan->tanggal_uji)
                                                {{ \Illuminate\Support\Carbon::parse($kelulusan->tanggal_uji)->format('d-m-Y') }}
                                            @else
                                                -
                                            @endif
                                        </span>
                                    </p>

                                    @if ($kelulusan->catatan)
                                        <p class="sm:col-span-2">
                                            Catatan:
                                            <span class="font-medium">
                                                {{ $kelulusan->catatan }}
                                            </span>
                                        </p>
                                    @endif
                                </div>
                            @else
                                <div class="mt-4 text-sm text-gray-500">
                                    Materi ini belum dilakukan pengujian.
                                </div>
                            @endif
                        </div>

                        {{-- Tombol PDF --}}
                        <div class="no-print shrink-0">
                            @if ($materi->pdf_file)
                                <a
                                    href="{{ asset('storage/' . $materi->pdf_file) }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-blue-700"
                                >
                                    📄 Lihat Materi PDF
                                </a>
                            @else
                                <span class="inline-flex items-center rounded-lg bg-gray-200 px-4 py-2 text-sm text-gray-500">
                                    PDF belum tersedia
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-gray-300 bg-gray-50 p-8 text-center">
                    <p class="font-semibold text-gray-700">
                        Belum ada materi
                    </p>

                    <p class="mt-1 text-sm text-gray-500">
                        Belum ada materi untuk tingkat
                        {{ $siswa->rombel?->tingkat ?? '-' }}.
                    </p>
                </div>
            @endforelse
        </div>
    </div>

    <footer class="mt-10 border-t py-6 text-center text-sm text-gray-500">
        Designed &amp; Developed by

        <a
            href="https://instagram.com/alynoval10"
            target="_blank"
            rel="noopener noreferrer"
            class="no-print text-blue-600 hover:underline"
        >
            Noval Aly, S.T.
        </a>

        <span class="hidden print:inline">
            Noval Aly, S.T.
        </span>
    </footer>

</body>

</html>