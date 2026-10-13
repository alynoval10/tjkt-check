<x-filament-panels::page>
    <x-filament::section>
        <div class="flex items-center gap-4">
            @if ($logo = \App\Models\SiteSetting::value('site_logo'))
                <div class="flex shrink-0 items-center justify-center overflow-hidden rounded-lg border border-gray-200 bg-white dark:border-white/10 dark:bg-gray-900" style="width: 3rem; height: 3rem;">
                    <img
                        src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($logo) }}"
                        alt="Logo aplikasi"
                        style="display: block; width: 100%; height: 100%; max-width: 100%; max-height: 100%; object-fit: contain;"
                    >
                </div>
            @else
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-primary-100 text-xl font-bold text-primary-700">
                    {{ mb_strtoupper(mb_substr(\App\Models\SiteSetting::value('site_title', 'TJKT CHECK'), 0, 1)) }}
                </div>
            @endif

            <div>
                <p class="text-sm text-gray-500">Judul aplikasi</p>
                <h2 class="text-xl font-bold text-gray-950 dark:text-white">
                    {{ \App\Models\SiteSetting::value('site_title', 'TJKT CHECK') }}
                </h2>
            </div>
        </div>

        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">
            Judul dan logo ini akan tampil pada panel guru/admin serta halaman portal siswa.
        </p>
    </x-filament::section>
</x-filament-panels::page>
