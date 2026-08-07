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

    <title>Cek Kompetensi Siswa - TJKT CHECK</title>

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

<body class="bg-slate-100 text-slate-800">

    <div class="min-h-screen">

        {{-- Navigasi --}}
        <header class="border-b border-blue-800 bg-blue-700 text-white shadow-sm">
            <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-4 sm:px-6 lg:px-8">

                <div class="flex items-center gap-3">
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
                </div>

                <div class="flex items-center gap-4">

    <div class="hidden text-right sm:block">
            <p class="text-sm font-semibold">
                Program Keahlian TJKT
            </p>

            <p class="text-xs text-blue-100">
                SMKN 1 Krangkeng
            </p>
        </div>

        <a href="{{ url('/admin/login') }}"
        class="rounded-lg bg-white px-4 py-2 text-sm font-semibold text-blue-700 shadow transition hover:bg-blue-50">
            Login Guru / Admin
        </a>

    </div>
            </div>
        </header>

        {{-- Hero --}}
        <section class="bg-gradient-to-br from-blue-700 via-blue-700 to-indigo-800 text-white">
            <div class="mx-auto max-w-6xl px-4 py-12 sm:px-6 sm:py-16 lg:px-8 lg:py-20">
                <div class="grid items-center gap-10 lg:grid-cols-2">

                    <div>
                        <span class="inline-flex rounded-full border border-white/20 bg-white/10 px-3 py-1 text-xs font-semibold text-blue-50">
                            Sistem Monitoring Pembelajaran Kejuruan
                        </span>

                        <h2 class="mt-5 text-3xl font-bold leading-tight sm:text-4xl lg:text-5xl">
                            Pantau perkembangan kompetensi dengan mudah
                        </h2>

                        <p class="mt-4 max-w-xl text-sm leading-7 text-blue-100 sm:text-base">
                            Cari data siswa menggunakan nama atau NIS untuk melihat
                            progres kompetensi dan materi pembelajaran sesuai tingkat kelas.
                        </p>

                        <div class="mt-7 grid max-w-lg grid-cols-2 gap-3 sm:grid-cols-3">
                            <div class="rounded-xl border border-white/15 bg-white/10 p-4">
                                <p class="text-2xl font-bold">X–XII</p>
                                <p class="mt-1 text-xs text-blue-100">
                                    Semua tingkat
                                </p>
                            </div>

                            <div class="rounded-xl border border-white/15 bg-white/10 p-4">
                                <p class="text-2xl font-bold">24/7</p>
                                <p class="mt-1 text-xs text-blue-100">
                                    Akses informasi
                                </p>
                            </div>

                            <div class="col-span-2 rounded-xl border border-white/15 bg-white/10 p-4 sm:col-span-1">
                                <p class="text-2xl font-bold">PDF</p>
                                <p class="mt-1 text-xs text-blue-100">
                                    Materi belajar
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Form pencarian --}}
                    <div class="rounded-2xl bg-white p-5 text-slate-800 shadow-2xl sm:p-7">
                        <div class="mb-6">
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">
                                🔍
                            </div>

                            <h3 class="mt-4 text-xl font-bold sm:text-2xl">
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

                        <div class="mt-6 border-t border-slate-200 pt-5">
                            <div class="flex items-start gap-3 rounded-xl bg-slate-50 p-4">
                                <div class="mt-0.5 text-lg">ℹ️</div>

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
        <main class="mx-auto max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
            <div class="mb-6">
                <p class="text-sm font-semibold uppercase tracking-wider text-blue-600">
                    Layanan 
                </p>

                <h2 class="mt-2 text-2xl font-bold text-slate-900">
                    Informasi yang dapat diakses siswa
                </h2>
            </div>

            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-100 text-2xl">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-100 text-2xl">
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

                <div class="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm sm:col-span-2 lg:col-span-1">
                    <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-amber-100 text-2xl">
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
                    © {{ date('Y') }} TJKT CHECK · Portal Kompetensi Siswa
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

                return `
                    <a
                        href="{{ url('/cek-siswa') }}/${siswa.id}"
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
                    "{{ url('/cek-siswa/search') }}",
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