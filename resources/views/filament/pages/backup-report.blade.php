<div>
    @if (! $report)
        <p>Belum ada hasil pengujian yang berlaku untuk file ini.</p>
    @elseif ($report['status'] === 'failed')
        <p>{{ $report['message'] }}</p>
    @else
        <p style="margin-bottom:16px">{{ $report['files'] }} file terverifikasi. Database aktif tidak diganti.</p>
        <table style="width:100%;border-collapse:collapse">
            <thead><tr><th style="text-align:left;padding:8px">Tabel</th><th style="text-align:right;padding:8px">Jumlah data</th></tr></thead>
            <tbody>
                @foreach ($report['tables'] as $table => $count)
                    <tr style="border-top:1px solid rgba(128,128,128,.2)"><td style="padding:8px;overflow-wrap:anywhere">{{ $table }}</td><td style="padding:8px;text-align:right">{{ $count }}</td></tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
