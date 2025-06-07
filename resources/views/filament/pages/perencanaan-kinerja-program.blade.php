<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Header Info -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                        Perencanaan Kinerja Program
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                        Kelola target dan perencanaan kinerja kegiatan untuk setiap program
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

        <!-- Form Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-md font-medium text-gray-900 dark:text-white">
                    Tambah Target Program Baru
                </h3>
            </div>
            <div class="p-6">
                <form wire:submit.prevent="save">
                    {{ $this->form }}

                    <div class="mt-6 flex justify-end">
                        <x-filament::button type="submit" color="success">
                            Simpan
                        </x-filament::button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Table Section -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="p-6 border-b border-gray-200 dark:border-gray-700">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-md font-medium text-gray-900 dark:text-white">
                            Daftar Perencanaan Kinerja Program
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Kelola dan pantau semua target Program yang telah direncanakan
                        </p>
                    </div>
                </div>
            </div>
            <div class="p-6">
                {{ $this->table }}
            </div>
        </div>
    </div>
</x-filament-panels::page>
