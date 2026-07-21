<!DOCTYPE html>
<html>
<head>
<title>Cek Kompetensi Siswa</title>

<style>
body{
font-family:Arial;
background:#f3f4f6;
}

.container{
max-width:700px;
margin:40px auto;
background:white;
padding:30px;
border-radius:10px;
}

input{
width:80%;
padding:12px;
}

button{
padding:12px;
background:#2563eb;
color:white;
border:0;
}

a{
text-decoration:none;
color:#2563eb;
}
</style>

</head>

<body>


<div class="container">

<h2>
Cek Kompetensi Siswa TJKT
</h2>


<form>

<input 
name="search"
placeholder="Cari nama atau NIS..."
value="{{request('search')}}"
>

<button>
Cari
</button>

</form>


<hr>


@foreach($siswas as $siswa)

<p>
<a href="{{route('cek-siswa.detail',$siswa->id)}}">

<b>
{{$siswa->nama}}
</b>

<br>

{{$siswa->kelas}}

</a>

</p>

@endforeach


</div>


</body>
</html>