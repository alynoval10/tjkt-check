<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Rapor Kompetensi - {{ $siswa->nama }}</title>
    <style>
        :root { color-scheme: light; --ink: #172b3a; --muted: #637381; --line: #d9e2e8; --brand: #075b54; --brand-soft: #e9f4f1; --paper: #fff; }
        * { box-sizing: border-box; }
        body { margin: 0; padding: 28px 16px; background: #edf2f4; color: var(--ink); font: 14px/1.5 Inter, "Segoe UI", Arial, sans-serif; }
        .toolbar { display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; margin: 0 auto 18px; }
        .period-picker { display: flex; align-items: center; gap: 8px; color: var(--muted); }
        select, button { min-height: 40px; border: 1px solid var(--line); border-radius: 8px; background: white; padding: 0 12px; font: inherit; }
        button { border: 0; background: var(--brand); color: white; font-weight: 700; cursor: pointer; }
        button:hover { background: #064b45; }
        .paper { width: min(100%, 210mm); min-height: 297mm; margin: 0 auto; padding: 17mm 16mm 14mm; background: var(--paper); box-shadow: 0 12px 36px rgba(23, 43, 58, .10); }
        .masthead { display: flex; align-items: center; gap: 14px; padding-bottom: 16px; border-bottom: 2px solid var(--brand); }
        .logo-box { display: flex; flex: 0 0 58px; width: 58px; height: 58px; align-items: center; justify-content: center; overflow: hidden; border: 1px solid var(--line); border-radius: 12px; background: white; }
        .logo-box img { display: block; width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain; }
        .logo-fallback { color: var(--brand); font-size: 17px; font-weight: 800; }
        .school-name { margin: 0; color: var(--brand); font-size: 12px; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
        .document-name { margin: 2px 0 0; font-size: 25px; line-height: 1.2; letter-spacing: -.025em; }
        .document-subtitle { margin: 4px 0 0; color: var(--muted); font-size: 12px; }
        .period-row { display: flex; justify-content: space-between; align-items: center; gap: 12px; margin: 18px 0 14px; }
        .period-label { color: var(--muted); font-size: 11px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .period-value { margin-top: 3px; font-size: 15px; font-weight: 700; }
        .period-pill { padding: 6px 10px; border-radius: 999px; background: var(--brand-soft); color: var(--brand); font-size: 11px; font-weight: 700; white-space: nowrap; }
        .student-card { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 12px 28px; padding: 16px 18px; border: 1px solid var(--line); border-radius: 10px; background: #fbfcfd; }
        .student-field { min-width: 0; }
        .field-label { display: block; margin-bottom: 2px; color: var(--muted); font-size: 10px; font-weight: 700; letter-spacing: .08em; text-transform: uppercase; }
        .field-value { display: block; overflow-wrap: anywhere; font-size: 13px; font-weight: 650; }
        .summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 10px; margin: 16px 0 22px; }
        .summary-card { padding: 11px 13px; border: 1px solid var(--line); border-radius: 9px; background: white; }
        .summary-number { display: block; font-size: 20px; font-weight: 800; line-height: 1.15; }
        .summary-label { display: block; margin-top: 4px; color: var(--muted); font-size: 10px; }
        .section-heading { display: flex; justify-content: space-between; align-items: baseline; gap: 10px; margin-bottom: 8px; }
        .section-heading h2 { margin: 0; font-size: 15px; }
        .section-heading span { color: var(--muted); font-size: 11px; }
        .table-wrap { overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        th, td { padding: 9px 10px; border-bottom: 1px solid var(--line); text-align: left; vertical-align: top; overflow-wrap: anywhere; }
        th { background: #f1f6f5; color: #3d5555; font-size: 10px; font-weight: 800; letter-spacing: .055em; text-transform: uppercase; }
        td { font-size: 11px; }
        tbody tr:nth-child(even) { background: #fbfcfd; }
        .score { font-size: 13px; font-weight: 800; }
        .status { display: inline-block; padding: 3px 7px; border-radius: 999px; font-size: 9px; font-weight: 800; white-space: nowrap; }
        .status-pass { background: #e8f5ec; color: #176a38; }
        .status-remedial { background: #fff1e8; color: #a34b11; }
        .status-pending { background: #f0f2f4; color: #596773; }
        .empty { padding: 24px; color: var(--muted); text-align: center; }
        .signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 32px; margin-top: 30px; break-inside: avoid; }
        .signature { min-height: 115px; text-align: center; }
        .signature-role { color: var(--muted); font-size: 11px; }
        .signature-line { width: 78%; margin: 78px auto 0; padding-top: 5px; border-top: 1px solid #6b7780; font-size: 11px; }
        .report-footer { display: flex; justify-content: space-between; gap: 12px; margin-top: 16px; padding-top: 10px; border-top: 1px solid var(--line); color: var(--muted); font-size: 9px; }
        @page { size: A4; margin: 10mm; }
        @media print {
            body { padding: 0; background: white; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .toolbar { display: none !important; }
            .paper { width: auto; min-height: 0; margin: 0; padding: 0; box-shadow: none; }
            thead { display: table-header-group; }
            tr, .student-card, .summary-card { break-inside: avoid; }
            .table-wrap { overflow: visible; }
        }
        @media screen and (max-width: 600px) {
            body { padding: 14px 8px; }
            .paper { min-height: 0; padding: 22px 16px; }
            .document-name { font-size: 20px; }
            .student-card { gap: 10px 14px; padding: 13px; }
            .summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
            th, td { padding: 7px 6px; font-size: 10px; }
            .signatures { gap: 8px; }
            .signature-line { width: 95%; }
        }
    </style>
</head>
<body>
    <div class="toolbar">
        <form method="GET" action="{{ route('siswa.kartu-kompetensi', $siswa) }}" class="period-picker">
            <label for="periode_id">Periode:</label>
            <select id="periode_id" name="periode_id" onchange="this.form.submit()">
                <option value="" @selected(is_null($periodeId))>Semua periode</option>
                @foreach ($periodes as $pilihanPeriode)
                    <option value="{{ $pilihanPeriode->id }}" @selected((int) $periodeId === $pilihanPeriode->id)>
                        {{ $pilihanPeriode->tahun_ajaran }} — Semester {{ $pilihanPeriode->semester }}
                    </option>
                @endforeach
            </select>
        </form>
        <button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
    </div>

    @php
        $jumlahMateri = $materis->count();
        $jumlahDiuji = $penilaian->filter(fn ($hasil) => ! is_null($hasil->nilai))->count();
        $jumlahTuntas = $penilaian->filter(fn ($hasil) => ! is_null($hasil->nilai) && $hasil->nilai >= 75)->count();
        $jumlahRemedial = $penilaian->filter(fn ($hasil) => ! is_null($hasil->nilai) && $hasil->nilai < 75)->count();
    @endphp

    <main class="paper">
        <header class="masthead">
            <div class="logo-box">
                @if ($logo = \App\Models\SiteSetting::value('site_logo'))
                    <img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}" alt="Logo {{ \App\Models\SiteSetting::value('site_title', 'TJKT CHECK') }}">
                @else
                    <span class="logo-fallback">{{ mb_strtoupper(mb_substr(\App\Models\SiteSetting::value('site_title', 'TJKT CHECK'), 0, 2)) }}</span>
                @endif
            </div>
            <div>
                <p class="school-name">{{ \App\Models\SiteSetting::value('site_title', 'TJKT CHECK') }}</p>
                <h1 class="document-name">Rapor Kompetensi Siswa</h1>
                <p class="document-subtitle">Rekap capaian kompetensi praktik program keahlian TJKT</p>
            </div>
        </header>

        <section class="period-row">
            <div>
                <div class="period-label">Periode Penilaian</div>
                <div class="period-value">
                    {{ $periode ? "Tahun Ajaran {$periode->tahun_ajaran} — Semester {$periode->semester}" : 'Semua Periode' }}
                </div>
            </div>
            <span class="period-pill">{{ $siswa->status === 'alumni' ? 'ALUMNI' : 'SISWA AKTIF' }}</span>
        </section>

        <section class="student-card" aria-label="Identitas siswa">
            <div class="student-field"><span class="field-label">Nama Siswa</span><span class="field-value">{{ $siswa->nama }}</span></div>
            <div class="student-field"><span class="field-label">NIS</span><span class="field-value">{{ $siswa->nis }}</span></div>
            <div class="student-field"><span class="field-label">Rombel</span><span class="field-value">{{ $kelasLaporan }}</span></div>
            <div class="student-field"><span class="field-label">Tingkat</span><span class="field-value">{{ $siswa->status === 'alumni' ? 'Alumni' : ($tingkatLaporan ?? '-') }}</span></div>
        </section>

        <section class="summary" aria-label="Ringkasan kompetensi">
            <div class="summary-card"><span class="summary-number">{{ $jumlahMateri }}</span><span class="summary-label">Total materi</span></div>
            <div class="summary-card"><span class="summary-number">{{ $jumlahDiuji }}</span><span class="summary-label">Sudah diuji</span></div>
            <div class="summary-card"><span class="summary-number">{{ $jumlahTuntas }}</span><span class="summary-label">Kompeten (≥ 75)</span></div>
            <div class="summary-card"><span class="summary-number">{{ $jumlahRemedial }}</span><span class="summary-label">Perlu penguatan</span></div>
        </section>

        <section>
            <div class="section-heading">
                <h2>Rincian Kompetensi</h2>
                <span>{{ $jumlahMateri }} materi</span>
            </div>
            <div class="table-wrap">
                <table>
                    <colgroup>
                        <col style="width: 22%"><col style="width: 8%"><col style="width: 13%">
                        <col style="width: 14%"><col style="width: 16%"><col style="width: 27%">
                    </colgroup>
                    <thead>
                        <tr><th>Materi</th><th>Nilai</th><th>Status</th><th>Tanggal Uji</th><th>Penguji</th><th>Catatan</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($materis as $materi)
                            @php($hasil = $penilaian->get($materi->id))
                            <tr>
                                <td>{{ $materi->nama }}</td>
                                <td class="score">{{ is_null($hasil?->nilai) ? '-' : $hasil->nilai }}</td>
                                <td>
                                    @if (is_null($hasil?->nilai))
                                        <span class="status status-pending">Belum diuji</span>
                                    @elseif ($hasil->nilai >= 75)
                                        <span class="status status-pass">Kompeten</span>
                                    @else
                                        <span class="status status-remedial">Penguatan</span>
                                    @endif
                                </td>
                                <td>{{ $hasil?->tanggal_uji?->format('d/m/Y') ?? '-' }}</td>
                                <td>{{ $hasil?->user?->name ?? '-' }}</td>
                                <td>{{ $hasil?->catatan ?: '-' }}</td>
                            </tr>
                        @empty
                            <tr><td class="empty" colspan="6">Belum ada materi untuk tingkat siswa ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="signatures">
            <div class="signature">
                <div class="signature-role">Mengetahui,</div>
                <div class="signature-line">Kepala Program Keahlian</div>
            </div>
            <div class="signature">
                <div class="signature-role">{{ now()->translatedFormat('d F Y') }}</div>
                <div class="signature-line">Penguji / Pembimbing</div>
            </div>
        </section>

        <footer class="report-footer">
            <span>Batas kompeten: nilai 75.</span>
            <span>Dicetak {{ now()->format('d/m/Y H:i') }}</span>
        </footer>
    </main>
</body>
</html>
