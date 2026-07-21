<!DOCTYPE html>

<html>

<head>

<script src="https://cdn.tailwindcss.com"></script>

</head>


<body class="bg-gray-100">


<div class="max-w-3xl mx-auto mt-10">


<div class="bg-white p-8 rounded-xl shadow">


<h1 class="text-3xl font-bold">

{{$siswa->nama}}

</h1>


<p>
NIS : {{$siswa->nis}}
</p>


<p>
Kelas : {{$siswa->kelas}}
</p>



<hr class="my-5">


<h2 class="text-xl font-bold">

Materi Lulus

</h2>



@foreach($siswa->kelulusans as $lulus)


<div class="bg-green-100 p-4 rounded-lg mt-3">


<b>
✅ {{$lulus->materi->nama}}
</b>


<br>


Penguji :
{{$lulus->user->name}}


<br>


Tanggal :
{{$lulus->tanggal_uji}}


</div>


@endforeach



</div>


</div>


</body>

</html>