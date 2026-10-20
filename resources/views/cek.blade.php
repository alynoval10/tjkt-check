@php($appTitle = \App\Models\SiteSetting::value('site_title', 'TJKT CHECK'))
@php($appLogo = \App\Models\SiteSetting::value('site_logo'))
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Portal monitoring kompetensi siswa TJKT"
    >

    <title>Cek Kompetensi Siswa - {{ $appTitle }}</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <style>
        [x-cloak] {
            display: none !important;
        }

        body {
            min-height: 100vh;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-800 antialiased">

    <div class="min-h-screen">

        {{-- Navigasi --}}
        <header class="sticky top-0 z-30 border-b border-slate-200/80 bg-white/90 text-slate-900 shadow-sm backdrop-blur-xl">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-4 py-3 sm:px-6 lg:px-8">

                <div class="flex items-center gap-3">
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
                </div>

                <div class="flex items-center gap-3 sm:gap-5">
                    <div class="hidden text-right sm:block">
                        <p class="text-sm font-semibold text-slate-800">
                Program Keahlian TJKT
                        </p>

                        <p class="text-xs text-slate-500">
                SMKN 1 Krangkeng
                        </p>
                    </div>

                    <a href="{{ url('/admin/login') }}"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-700 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-800 focus:outline-none focus:ring-4 focus:ring-blue-100">
                        <span aria-hidden="true">↗</span>
                        Login Guru / Admin
                    </a>

                </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="relative isolate overflow-hidden border-b border-blue-100 bg-gradient-to-br from-white via-blue-50/60 to-indigo-50/70">
            <div class="pointer-events-none absolute -right-28 -top-32 h-96 w-96 rounded-full bg-blue-200/30 blur-3xl"></div>
            <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 sm:py-12 lg:px-8 lg:py-14">
                <div class="grid items-center gap-7 xl:grid-cols-[0.92fr_1.08fr_1.08fr] xl:gap-8">

                    <div class="relative order-2 mx-auto flex w-full max-w-sm items-end justify-center self-stretch xl:order-1 xl:max-w-none">
                        <div class="absolute bottom-5 left-1/2 h-56 w-56 -translate-x-1/2 rounded-full bg-blue-100/80 sm:h-72 sm:w-72"></div>
                        <div class="absolute bottom-5 left-1/2 h-44 w-72 -translate-x-1/2 rounded-[50%] bg-gradient-to-r from-blue-100/70 via-white/80 to-indigo-100/70 blur-sm sm:w-80"></div>
                        <img src="{{ asset('images/siswa-mural.png') }}?v={{ filemtime(public_path('images/siswa-mural.png')) }}" alt="Ilustrasi siswa dan siswi TJKT memakai seragam prakerin" class="relative z-10 h-auto max-h-[23rem] w-full object-contain object-bottom drop-shadow-[0_18px_20px_rgba(30,64,175,0.12)] sm:max-h-[28rem] xl:max-h-[30rem]">
                    </div>

                    <div class="relative order-1 xl:order-2">
                        <span class="inline-flex items-center gap-2 rounded-full border border-blue-100 bg-white/80 px-3.5 py-1.5 text-xs font-semibold text-blue-700 shadow-sm">
                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                            Sistem Monitoring Pembelajaran Kejuruan
                        </span>

                        <h2 class="mt-5 text-3xl font-extrabold leading-[1.12] tracking-tight text-slate-950 sm:text-4xl">
                            Pantau perkembangan <span class="text-blue-700">kompetensi dengan mudah</span>
                        </h2>

                        <p class="mt-4 max-w-xl text-sm leading-7 text-slate-600 sm:text-base">
                            Cari data siswa menggunakan nama atau NIS untuk melihat
                            progres kompetensi dan materi pembelajaran sesuai tingkat kelas.
                        </p>

                        <div class="mt-7 grid max-w-lg grid-cols-3 gap-2 sm:gap-3">
                            <div class="rounded-2xl border border-blue-100 bg-white/90 p-3 shadow-sm sm:p-4">
                                <p class="text-xl font-extrabold text-slate-900 sm:text-2xl">X–XII</p>
                                <p class="mt-1 text-[11px] leading-4 text-slate-500 sm:text-xs">
                                    Semua tingkat
                                </p>
                            </div>

                            <div class="rounded-2xl border border-emerald-100 bg-emerald-50/80 p-3 shadow-sm sm:p-4">
                                <p class="text-xl font-extrabold text-slate-900 sm:text-2xl">24/7</p>
                                <p class="mt-1 text-[11px] leading-4 text-slate-500 sm:text-xs">
                                    Akses informasi
                                </p>
                            </div>

                            <div class="rounded-2xl border border-amber-100 bg-amber-50/80 p-3 shadow-sm sm:p-4">
                                <p class="text-xl font-extrabold text-slate-900 sm:text-2xl">PDF</p>
                                <p class="mt-1 text-[11px] leading-4 text-slate-500 sm:text-xs">
                                    Materi belajar
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Form pencarian --}}
                    <div class="relative order-3 rounded-3xl border border-slate-200/80 bg-white p-5 text-slate-800 shadow-[0_24px_70px_-35px_rgba(30,64,175,0.35)] sm:p-7 lg:p-8">
                        <div class="mb-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-2xl text-blue-700 ring-1 ring-blue-100">
                                <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.8" stroke="currentColor" stroke-width="2"/><path d="m16 16 4.5 4.5" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                            </div>

                            <h3 class="mt-4 text-xl font-extrabold tracking-tight text-slate-900 sm:text-2xl">
                                Cari Data Siswa
                            </h3>

                            <p class="mt-2 text-sm leading-6 text-slate-500">
                                Masukkan minimal dua karakter nama siswa atau nomor induk siswa.
                            </p>
                        </div>

                        <div class="relative">
                            <label
                                for="keyword"
                                class="mb-2 block text-sm font-semibold text-slate-700"
                            >
                                Nama atau NIS
                            </label>

                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4 text-slate-400">
                                    🔎
                                </span>

                                <input
                                    type="search"
                                    id="keyword"
                                    autocomplete="off"
                                    placeholder="Contoh: Noval atau 12345"
                                    class="w-full rounded-xl border border-slate-300 bg-white py-3.5 pl-11 pr-4 text-sm outline-none transition placeholder:text-slate-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                                >
                            </div>

                            {{-- Status loading --}}
                            <div
                                id="loading"
                                class="mt-4 hidden items-center gap-3 rounded-xl bg-blue-50 px-4 py-3 text-sm text-blue-700"
                            >
                                <svg
                                    class="h-5 w-5 animate-spin"
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <circle
                                        class="opacity-25"
                                        cx="12"
                                        cy="12"
                                        r="10"
                                        stroke="currentColor"
                                        stroke-width="4"
                                    ></circle>

                                    <path
                                        class="opacity-75"
                                        fill="currentColor"
                                        d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"
                                    ></path>
                                </svg>

                                Sedang mencari data siswa...
                            </div>

                            {{-- Hasil pencarian --}}
                            <div
                                id="results"
                                class="mt-4 space-y-3"
                            ></div>
                        </div>

                        <div class="mt-6 border-t border-slate-100 pt-5">
                            <div class="flex items-start gap-3 rounded-2xl bg-slate-50 p-4 ring-1 ring-slate-100">
                                <div class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-blue-100 text-sm font-bold text-blue-700">i</div>

                                <p class="text-xs leading-5 text-slate-500">
                                    Pastikan nama atau NIS dimasukkan dengan benar.
                                    Informasi yang ditampilkan hanya berupa progres kompetensi
                                    dan materi pembelajaran.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Informasi layanan --}}
        <main class="mx-auto max-w-7xl px-4 py-10 sm:px-6 sm:py-14 lg:px-8">
            <div class="mb-7 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-blue-700">
                    Layanan
                </p>

                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-slate-950 sm:text-3xl">
                    Informasi yang dapat diakses siswa
                </h2>
                </div>
                <p class="text-sm text-slate-500">Informasi kompetensi siswa tersedia secara daring.</p>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-emerald-200 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50 text-2xl ring-1 ring-emerald-100">
                        ✅
                    </div>

                    <h3 class="mt-4 font-bold text-slate-900">
                        Status Kompetensi
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Menampilkan materi yang sudah lulus, belum lulus,
                        dan belum dilakukan pengujian.
                    </p>
                </div>

                <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-blue-200 hover:shadow-lg">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-blue-50 text-2xl ring-1 ring-blue-100">
                        📚
                    </div>

                    <h3 class="mt-4 font-bold text-slate-900">
                        Materi Pembelajaran
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Siswa dapat membuka materi PDF sesuai tingkat kelas,
                        baik yang sudah maupun belum diuji.
                    </p>
                </div>

                <div class="group rounded-3xl border border-slate-200 bg-white p-6 shadow-sm transition duration-200 hover:-translate-y-1 hover:border-amber-200 hover:shadow-lg sm:col-span-2 lg:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-amber-50 text-2xl ring-1 ring-amber-100">
                        📈
                    </div>

                    <h3 class="mt-4 font-bold text-slate-900">
                        Progres Belajar
                    </h3>

                    <p class="mt-2 text-sm leading-6 text-slate-500">
                        Progres dihitung berdasarkan jumlah materi kompetensi
                        yang telah dinyatakan lulus.
                    </p>
                </div>
            </div>
        </main>

        <footer class="border-t border-slate-200 bg-white">
            <div class="mx-auto flex max-w-6xl flex-col gap-2 px-4 py-6 text-center text-sm text-slate-500 sm:px-6 lg:px-8">
                <p>
                    © {{ date('Y') }} {{ $appTitle }} · Portal Kompetensi Siswa
                </p>

                <p>
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
    </div>

    <script>
        const keywordInput = document.getElementById('keyword');
        const resultsContainer = document.getElementById('results');
        const loadingIndicator = document.getElementById('loading');

        let searchTimer = null;
        let activeRequest = null;

        function escapeHtml(value) {
            const div = document.createElement('div');
            div.textContent = value ?? '';
            return div.innerHTML;
        }

        function showMessage(message, type = 'info') {
            const styles = {
                info: 'border-blue-200 bg-blue-50 text-blue-700',
                warning: 'border-amber-200 bg-amber-50 text-amber-700',
                error: 'border-red-200 bg-red-50 text-red-700',
            };

            resultsContainer.innerHTML = `
                <div class="rounded-xl border px-4 py-4 text-sm ${styles[type]}">
                    ${escapeHtml(message)}
                </div>
            `;
        }

        function renderResults(siswas) {
            if (!Array.isArray(siswas) || siswas.length === 0) {
                showMessage('Data siswa tidak ditemukan. Periksa kembali nama atau NIS.', 'warning');
                return;
            }

            resultsContainer.innerHTML = siswas.map((siswa) => {
                const nama = escapeHtml(siswa.nama);
                const nis = escapeHtml(siswa.nis);
                const kelas = escapeHtml(siswa.rombel?.nama ?? '-');
                const tingkat = escapeHtml(siswa.rombel?.tingkat ?? '-');
                const status = siswa.status === 'alumni' ? 'Alumni' : 'Aktif';

                return `
                    <a
                        href="{{ url('/cek-siswa') }}/${siswa.public_id}"
                        class="group block rounded-xl border border-slate-200 bg-white p-4 transition hover:border-blue-400 hover:bg-blue-50 hover:shadow-md"
                    >
                        <div class="flex items-center gap-4">
                            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-blue-100 font-bold text-blue-700 group-hover:bg-blue-600 group-hover:text-white">
                                ${nama.substring(0, 1).toUpperCase()}
                            </div>

                            <div class="min-w-0 flex-1">
                                <p class="truncate font-bold text-slate-900">
                                    ${nama}
                                </p>

                                <div class="mt-1 flex flex-wrap gap-x-3 gap-y-1 text-xs text-slate-500">
                                    <span>NIS: ${nis}</span>
                                    <span>Kelas: ${kelas}</span>
                                    <span>Tingkat: ${tingkat}</span>
                                    <span>Status: ${status}</span>
                                </div>
                            </div>

                            <div class="shrink-0 text-xl text-slate-400 transition group-hover:translate-x-1 group-hover:text-blue-600">
                                →
                            </div>
                        </div>
                    </a>
                `;
            }).join('');
        }

        async function searchStudents(keyword) {
            if (activeRequest) {
                activeRequest.abort();
            }

            activeRequest = new AbortController();

            loadingIndicator.classList.remove('hidden');
            loadingIndicator.classList.add('flex');
            resultsContainer.innerHTML = '';

            try {
                const url = new URL(
                      '/cek-siswa/search',
                window.location.origin
                );

                url.searchParams.set('keyword', keyword);

                const response = await fetch(url.toString(), {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: activeRequest.signal,
                });

                if (!response.ok) {
                    throw new Error('Gagal mengambil data siswa.');
                }

                const data = await response.json();

                renderResults(data);
            } catch (error) {
                if (error.name !== 'AbortError') {
                    showMessage(
                        'Terjadi kesalahan saat mencari data. Silakan coba kembali.',
                        'error'
                    );
                }
            } finally {
                loadingIndicator.classList.add('hidden');
                loadingIndicator.classList.remove('flex');
            }
        }

        keywordInput.addEventListener('input', function () {
            const keyword = this.value.trim();

            clearTimeout(searchTimer);

            if (keyword.length === 0) {
                resultsContainer.innerHTML = '';
                return;
            }

            if (keyword.length < 2) {
                showMessage('Masukkan minimal dua karakter untuk melakukan pencarian.');
                return;
            }

            searchTimer = setTimeout(() => {
                searchStudents(keyword);
            }, 350);
        });
    </script>

</body>

</html>
