<!DOCTYPE html>

<html>

<head>

<title>
TJKT CHECK - {{$siswa->nama}}
</title>


<script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-slate-100">


<div class="max-w-5xl mx-auto py-10">


<!-- HEADER -->

<div class="bg-blue-700 text-white rounded-2xl p-8 shadow">


<h1 class="text-3xl font-bold">

TJKT CHECK

</h1>


<p>
Monitoring Kompetensi Siswa
</p>


</div>



<!-- DATA SISWA -->

<div class="bg-white rounded-2xl shadow p-8 mt-6">


<div class="flex justify-between">


<div>


<h2 class="text-3xl font-bold">

{{$siswa->nama}}

</h2>


<p class="mt-2">

NIS : {{$siswa->nis}}

</p>


<p>

Kelas : {{$siswa->kelas}}

</p>


</div>



<div class="text-right">


<p class="text-gray-500">

Progress

</p>


<h2 class="text-4xl font-bold text-blue-600">

{{$persentase}}%

</h2>


</div>


</div>



<div class="mt-6">


<div class="w-full bg-gray-200 rounded-full h-4">


<div

class="bg-green-500 h-4 rounded-full"

style="width: {{$persentase}}%"

>

</div>


</div>


<p class="mt-2">

{{$lulus}} dari {{$totalMateri}} materi sudah lulus

</p>


</div>


</div>





<!-- MATERI -->


<div class="bg-white rounded-2xl shadow p-8 mt-6">


<h2 class="text-2xl font-bold mb-5">

📚 Materi Kompetensi

</h2>



@foreach($materis as $materi)

@php
    $kelulusan = $siswa->kelulusans
        ->where('materi_id', $materi->id)
        ->first();
@endphp


@if($kelulusan)

<div class="border rounded-xl p-5 mb-4 bg-green-50">

<div class="flex justify-between">

<h3 class="font-bold text-lg text-green-700">

✅ {{$materi->nama}}

</h3>

<span class="text-green-600">

Lulus

</span>

</div>


<div class="mt-3 text-gray-600">

<p>
👨‍🏫 Penguji :
{{$kelulusan->user->name ?? '-'}}
</p>


<p>
📅 Tanggal :
{{$kelulusan->tanggal_uji}}
</p>

</div>

</div>


@else


<div class="border rounded-xl p-5 mb-4 bg-gray-100">

<div class="flex justify-between">

<h3 class="font-bold text-lg text-gray-500">

○ {{$materi->nama}}

</h3>


<span class="text-gray-500">

Belum Lulus

</span>

</div>


<div class="mt-3 text-gray-400">

<p>
Belum dilakukan pengujian
</p>

</div>


</div>


@endif


@endforeach



</div>


</div>


</body>

</html>