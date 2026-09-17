<x-filament-panels::page>
    <div style="display:flex;justify-content:space-between;align-items:center;gap:16px;flex-wrap:wrap">
        <span>{{ count($backups) }} cadangan tersedia</span>
        <x-filament::button wire:click="$refresh" color="gray" icon="heroicon-o-arrow-path">Muat Ulang</x-filament::button>
    </div>
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse;text-align:left;font-size:14px">
            <thead><tr>
                <th style="padding:12px">Cadangan</th><th style="padding:12px">Ukuran</th>
                <th style="padding:12px">Pemeriksaan</th><th style="padding:12px">Tindakan</th>
            </tr></thead>
            <tbody>
                @forelse ($backups as $backup)
                    <tr wire:key="backup-{{ $backup['name'] }}" style="border-top:1px solid rgba(128,128,128,.2)">
                        <td style="padding:12px;min-width:180px;max-width:360px;overflow-wrap:anywhere">
                            <strong>{{ $backup['date'] }}</strong><div style="font-size:12px">{{ $backup['name'] }}</div>
                        </td>
                        <td style="padding:12px;white-space:nowrap">{{ number_format($backup['bytes'] / 1048576, 2) }} MB</td>
                        <td style="padding:12px;min-width:140px">
                            <x-filament::badge :color="! $backup['report'] ? 'gray' : ($backup['report']['status'] === 'passed' ? 'success' : 'danger')">
                                {{ ! $backup['report'] ? 'Belum diuji' : ($backup['report']['status'] === 'passed' ? 'Lulus uji restore' : 'Uji gagal') }}
                            </x-filament::badge>
                            @if ($backup['report'])
                                <div style="font-size:12px;margin-top:6px">{{ \Illuminate\Support\Carbon::parse($backup['report']['checked_at'])->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>
                        <td style="padding:12px;min-width:240px">
                            <div style="display:flex;gap:10px;flex-wrap:wrap">
                                <x-filament::button tag="a" :href="route('backup.download', $backup['name'])" color="gray" icon="heroicon-o-arrow-down-tray" size="sm">Unduh</x-filament::button>
                                {{ ($this->ujiRestoreAction)(['name' => $backup['name']]) }}
                                {{ ($this->hapusBackupAction)(['name' => $backup['name']]) }}
                                @if ($backup['report'])
                                    {{ ($this->hasilAction)(['name' => $backup['name']]) }}
                                    @if ($backup['report']['status'] === 'passed')
                                        {{ ($this->pulihkanAction)(['name' => $backup['name']]) }}
                                    @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" style="padding:32px;text-align:center">Belum ada cadangan.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-filament-panels::page>
