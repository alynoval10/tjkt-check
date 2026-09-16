<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Kompetensi - {{ $siswa->nama }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; font: 14px Arial, sans-serif; color: #18211b; background: #eef0f2; }
        main { max-width: 210mm; margin: 24px auto; padding: 16mm; background: white; }
        .toolbar { max-width: 210mm; margin: 16px auto; padding: 0 16px; }
        button { padding: 10px 16px; border: 0; border-radius: 4px; background: #166534; color: white; cursor: pointer; }
        h1 { font-size: 23px; margin: 6px 0 20px; }
        header { border-bottom: 2px solid #166534; margin-bottom: 20px; }
        dl { display: grid; grid-template-columns: 85px minmax(0, 1fr); gap: 8px; }
        dt, dd { margin: 0; overflow-wrap: anywhere; }
        table { width: 100%; border-collapse: collapse; margin-top: 24px; table-layout: fixed; }
        th, td { border: 1px solid #b7c0ba; padding: 8px; text-align: left; overflow-wrap: anywhere; }
        th { background: #edf4ef; }
        .signature { margin-top: 30px; margin-left: auto; width: 240px; max-width: 100%; break-inside: avoid; }
        .signature p:last-child { margin-top: 65px; border-bottom: 1px solid #555; }
        footer { margin-top: 24px; font-size: 11px; color: #555; }
        @page { size: A4; margin: 14mm; }
        @media print {
            body { background: white; }
            main { max-width: none; margin: 0; padding: 0; }
            .toolbar { display: none; }
            thead { display: table-header-group; }
            tr { break-inside: avoid; }
        }
        @media screen and (max-width: 600px) { main { margin: 0; padding: 16px; } th, td { padding: 5px; font-size: 12px; } }
    </style>
</head>
<body>
    <div class="toolbar"><button type="button" onclick="window.print()">Cetak / Simpan PDF</button></div>
    <main>
        <header><strong>TJKT CHECK</strong><h1>Kartu Kompetensi Siswa</h1></header>
        <dl>
            <dt>Nama</dt><dd>{{ $siswa->nama }}</dd>
            <dt>NIS</dt><dd>{{ $siswa->nis }}</dd>
            <dt>Kelas</dt><dd>{{ $siswa->rombel?->nama ?? '-' }}</dd>
        </dl>
        {{-- Nilai null berbeda dari nilai nol: nol berarti sudah diuji. --}}
        <table>
            <thead><tr><th style="width:30%">Materi</th><th style="width:10%">Nilai</th><th style="width:20%">Status</th><th style="width:18%">Tanggal Uji</th><th>Penguji</th></tr></thead>
            <tbody>
                @forelse ($materis as $materi)
                    @php($hasil = $penilaian->get($materi->id))
                    <tr>
                        <td>{{ $materi->nama }}</td>
                        <td>{{ $hasil?->nilai ?? '-' }}</td>
                        <td>{{ is_null($hasil?->nilai) ? 'Belum Diuji' : ($hasil->nilai >= 75 ? 'Lulus' : 'Remedial') }}</td>
                        <td>{{ $hasil?->tanggal_uji ? \Illuminate\Support\Carbon::parse($hasil->tanggal_uji)->format('d/m/Y') : '-' }}</td>
                        <td>{{ $hasil?->user?->name ?? '-' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="5">Belum ada materi untuk kelas siswa ini.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="signature"><p>Tanggal: __________________</p><p>Penguji / Penanggung Jawab</p><p>&nbsp;</p></div>
        <footer>Batas kelulusan: 75. Dicetak {{ now()->format('d/m/Y H:i') }}.</footer>
    </main>
</body>
</html>
