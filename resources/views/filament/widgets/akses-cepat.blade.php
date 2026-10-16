<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">Akses Cepat</x-slot>
        <x-slot name="description">Langsung ke pekerjaan yang sering digunakan.</x-slot>

        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ($links as $link)
                <a href="{{ $link['url'] }}" class="fi-quick-link group flex items-center gap-3 rounded-xl border border-gray-200 bg-white p-4 transition duration-200 hover:-translate-y-0.5 hover:border-primary-300 hover:shadow-md dark:border-white/10 dark:bg-white/5">
                    <span class="flex size-11 shrink-0 items-center justify-center rounded-xl bg-primary-50 text-primary-600 dark:bg-primary-400/10 dark:text-primary-300">
                        <x-filament::icon :icon="$link['icon']" class="size-5" />
                    </span>
                    <span class="min-w-0">
                        <span class="block font-semibold text-gray-950 dark:text-white">{{ $link['label'] }}</span>
                        <span class="mt-0.5 block text-sm text-gray-500 dark:text-gray-400">{{ $link['description'] }}</span>
                    </span>
                    <x-filament::icon icon="heroicon-m-arrow-up-right" class="ml-auto size-4 shrink-0 text-gray-400 transition group-hover:text-primary-600" />
                </a>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
