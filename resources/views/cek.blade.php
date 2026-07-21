<!DOCTYPE html>
<html>

<head>
    <title>TJKT CHECK</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-xl mx-auto mt-20">

    <div class="bg-white p-8 rounded-xl shadow">

        <h1 class="text-3xl font-bold text-center text-blue-700">
            TJKT CHECK
        </h1>

        <p class="text-center text-gray-500 mt-2">
            Sistem Monitoring Kompetensi Siswa
        </p>


        <input
            id="search"
            type="text"
            placeholder="Cari Nama atau NIS..."
            class="w-full border rounded-lg p-4 mt-8"
        >


        <div id="hasil" class="mt-3"></div>


    </div>

</div>


<script>

let search = document.getElementById('search');


search.addEventListener('keyup', function(){

    let keyword = this.value;


    if(keyword.length < 2){
        document.getElementById('hasil').innerHTML = '';
        return;
    }


    fetch('/cek-siswa/search?keyword=' + keyword)

    .then(response => response.json())

    .then(data => {

        let html = '';


        data.forEach(siswa => {

            html += `

            <a href="/cek-siswa/${siswa.id}"
            class="block bg-gray-100 p-3 mt-2 rounded-lg">

            <b>${siswa.nama}</b><br>

            NIS : ${siswa.nis}<br>

            Kelas : ${siswa.kelas}

            </a>

            `;

        });


        document.getElementById('hasil').innerHTML = html;

    });


});

</script>


</body>
</html>