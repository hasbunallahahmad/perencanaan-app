<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Realisasi Kinerja Kegiatan
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Monitor dan input realisasi kinerja per triwulan untuk setiap Kegiatan
                    </p>
                </div>
                <div class="bg-blue-100 dark:bg-blue-900 p-3 rounded-lg">
                    <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-md font-medium text-gray-900 dark:text-white">
                            Data Realisasi Kinerja Kegiatan
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Klik tombol "Input Realisasi" untuk mengisi data realisasi per triwulan
                        </p>
                    </div>
                    <div class="flex space-x-2">
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">
                                < 50%</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">50-74%</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">75-99%</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-600 dark:text-gray-400">≥ 100%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
