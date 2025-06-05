<x-filament-widgets::widget>
    <x-filament::section>
        <div
            class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg border border-blue-200">
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-gray-900">Tahun Perencanaan Aktif</h3>
            </div>
            <div class="flex items-center space-x-3">
                <label class="text-sm font-medium text-gray-700">Pilih Tahun:</label>
                <select wire:model.live="selectedYear"
                    class="px-4 py-2 text-sm font-medium border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 bg-white transition-colors duration-200">
                    @foreach ($this->getAvailableYears() as $year => $label)
                        <option value="{{ $year }}" class="py-2">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
