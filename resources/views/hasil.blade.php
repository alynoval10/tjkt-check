<!DOCTYPE html>
<html>

<head>

<script src="https://cdn.tailwindcss.com"></script>

</head>

<body class="bg-slate-100">

<div class="max-w-5xl mx-auto mt-10">

@if($siswa)

<div class="bg-white rounded-xl shadow p-8">

<h2 class="text-3xl font-bold">

{{ $siswa->nama }}

</h2>

<p>NIS : {{ $siswa->nis }}</p>

<p>Kelas : {{ $siswa->kelas }}</p>

</div>

@endif

</div>

</body>

</html>