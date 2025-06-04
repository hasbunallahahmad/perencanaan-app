<x-filament-widgets::widget>
    <x-filament::section class="bg-primary-50 dark:bg-primary-950 border-primary-200 dark:border-primary-800">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary-100 dark:bg-primary-900 rounded-lg">
                    <x-heroicon-o-calendar class="w-6 h-6 text-primary-600 dark:text-primary-400" />
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Filter Tahun
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-gray-400">
                        Tahun aktif: <span
                            class="font-medium text-primary-600 dark:text-primary-400">{{ session('selected_year', now()->year) }}</span>
                    </p>
                </div>
            </div>

            <div>
                {{ $this->yearSelectorAction }}
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
