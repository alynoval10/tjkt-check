{{-- Ringkasan berlabel agar detail dan cakupan penyimpanan mudah dipindai. --}}
<div style="text-align:left;font-size:14px;line-height:1.5">
    <dl style="display:grid;grid-template-columns:100px minmax(0,1fr);gap:10px 16px;margin:0">
        <dt class="text-gray-500 dark:text-gray-400">Kelas</dt>
        <dd style="margin:0;font-weight:600;overflow-wrap:anywhere">{{ $kelas }}</dd>
        <dt class="text-gray-500 dark:text-gray-400">Materi</dt>
        <dd style="margin:0;font-weight:600;overflow-wrap:anywhere">{{ $materi }}</dd>
        <dt class="text-gray-500 dark:text-gray-400">Tanggal uji</dt>
        <dd style="margin:0">{{ preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal) ? implode('/', array_reverse(explode('-', $tanggal))) : $tanggal }}</dd>
    </dl>

    <div style="border-top:1px solid rgba(128,128,128,.2);border-bottom:1px solid rgba(128,128,128,.2);padding:16px 0;margin:20px 0">
        <div style="display:flex;justify-content:space-between;gap:16px;align-items:baseline">
            <strong>Nilai akan diproses</strong>
            <strong style="white-space:nowrap">{{ $jumlah }} siswa</strong>
        </div>
        <div class="text-gray-500 dark:text-gray-400" style="display:flex;justify-content:space-between;gap:16px;margin-top:10px">
            <span>Tersembunyi oleh filter</span><span style="white-space:nowrap">{{ $tersembunyi }} siswa</span>
        </div>
        <div class="text-gray-500 dark:text-gray-400" style="display:flex;justify-content:space-between;gap:16px;margin-top:10px">
            <span>Nilai kosong, dilewati</span><span style="white-space:nowrap">{{ $kosong }} siswa</span>
        </div>
    </div>
    <p class="text-gray-500 dark:text-gray-400" style="margin:0;font-size:13px">
        Nilai yang sudah ada akan diperbarui.
        @if ($tersembunyi > 0)
            Nilai siswa yang tersembunyi juga ikut disimpan.
        @endif
    </p>
</div>
