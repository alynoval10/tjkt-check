<!DOCTYPE html>
<html>

<head>

    <title>TJKT CHECK</title>

    <script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-100">

<div class="max-w-xl mx-auto mt-20">

    <div class="bg-white rounded-xl shadow-lg p-8">

        <h1 class="text-4xl font-bold text-center text-blue-700">

            TJKT CHECK

        </h1>

        <p class="text-center mt-2 text-gray-500">

            Sistem Monitoring Kompetensi Siswa

        </p>

       <form action="{{ route('cek-siswa.hasil') }}" method="GET" class="mt-10">

            <input

                type="text"

                name="keyword"

                placeholder="Masukkan Nama atau NIS"

                class="w-full border rounded-lg p-4"

            >

            <button

                class="w-full mt-4 bg-blue-600 text-white p-4 rounded-lg"

            >

                Cari

            </button>

        </form>

    </div>

</div>

</body>

</html>