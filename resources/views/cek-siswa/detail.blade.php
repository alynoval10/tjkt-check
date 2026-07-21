<!DOCTYPE html>

<html>

<head>

<title>
{{$siswa->nama}}
</title>

</head>


<body>


<h2>
{{$siswa->nama}}
</h2>


<p>
Kelas:
{{$siswa->kelas}}
</p>


<h3>
Materi Lulus
</h3>


<table border="1" cellpadding="10">

<tr>
<th>
Materi
</th>

<th>
Penguji
</th>

<th>
Tanggal
</th>

</tr>


@foreach($siswa->kelulusans as $lulus)

<tr>

<td>
{{$lulus->materi->nama}}
</td>


<td>
{{$lulus->user->name}}
</td>


<td>
{{$lulus->tanggal_uji}}
</td>


</tr>


@endforeach


</table>


</body>

</html>