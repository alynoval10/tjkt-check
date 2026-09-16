<div style="display:grid;gap:16px">
    @forelse ($riwayat as $item)
        <article style="border-bottom:1px solid rgba(128,128,128,.25);padding-bottom:16px;overflow-wrap:anywhere">
            <div style="display:flex;flex-wrap:wrap;align-items:center;gap:12px;margin-bottom:8px">
                <strong>{{ $item->tanggal_uji->format('d/m/Y') }}</strong>
                <x-filament::badge :color="is_null($item->nilai) ? 'gray' : ($item->nilai >= 75 ? 'success' : 'danger')">
                    {{ is_null($item->nilai) ? 'Belum Diuji' : $item->nilai.' - '.($item->nilai >= 75 ? 'Lulus' : 'Remedial') }}
                </x-filament::badge>
                <span>{{ match ($item->jenis) { 'data_awal' => 'Data awal saat fitur diaktifkan', 'penilaian_awal' => 'Penilaian awal', default => 'Perubahan penilaian' } }}</span>
            </div>
            <div>{{ $item->siswa }} / {{ $item->materi }}</div>
            <div>Penguji: {{ $item->penguji }}</div>
            <p style="white-space:pre-wrap;margin:8px 0">{{ $item->catatan ?: '-' }}</p>
            <div style="font-size:12px">
                Dicatat {{ $item->dicatat_pada->format('d/m/Y H:i:s') }}
                @if ($item->diubah_oleh)
                    oleh {{ $item->diubah_oleh }}
                @endif
            </div>
        </article>
    @empty
        <p>Belum ada riwayat penilaian.</p>
    @endforelse
</div>
