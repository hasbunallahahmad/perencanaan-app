{{-- resources/views/filament/components/realisasi-table.blade.php --}}
<div class="space-y-8">
    {{-- Action Bar dengan Tombol Simpan --}}
    <div class="bg-white dark:bg-gray-800 p-4 rounded-lg border border-gray-300 dark:border-gray-600 shadow-sm">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center space-x-4">
                <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    📊 Data Realisasi Tahun {{ $currentYear }}
                </h1>
                @if ($this->hasChanges)
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd"></path>
                        </svg>
                        Ada Perubahan
                    </span>
                @endif
            </div>

            <div class="flex items-center space-x-3">
                {{-- Reset Button --}}
                @if ($this->hasChanges)
                    <button wire:click="resetForm"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15">
                            </path>
                        </svg>
                        Reset
                    </button>
                @endif

                {{-- Save Button --}}
                <button wire:click="saveAllData" wire:loading.attr="disabled"
                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200">
                    <div wire:loading wire:target="saveAllData" class="inline-flex items-center">
                        <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Menyimpan...
                    </div>
                    <div wire:loading.remove wire:target="saveAllData" class="inline-flex items-center">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                            </path>
                        </svg>
                        Simpan Realisasi
                    </div>
                </button>
            </div>
        </div>

        {{-- Progress Info --}}
        @if ($this->hasChanges || $this->getTotalItemsWithData()['tujuan'] > 0 || $this->getTotalItemsWithData()['sasaran'] > 0)
            @php $itemCounts = $this->getTotalItemsWithData(); @endphp
            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-600">
                <div class="flex flex-wrap items-center gap-4 text-sm text-gray-600 dark:text-gray-400">
                    <span>📋 {{ $itemCounts['tujuan'] }} Tujuan dengan data</span>
                    <span>🎯 {{ $itemCounts['sasaran'] }} Sasaran dengan data</span>
                    @if ($this->hasChanges)
                        <span class="text-yellow-600 dark:text-yellow-400 font-medium">⚠️ Jangan lupa simpan
                            perubahan</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div class="bg-green-50 dark:bg-green-900 border border-green-200 dark:border-green-700 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ session('message') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('error'))
        <div class="bg-red-50 dark:bg-red-900 border border-red-200 dark:border-red-700 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800 dark:text-red-200">
                        {{ session('error') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('warning'))
        <div class="bg-yellow-50 dark:bg-yellow-900 border border-yellow-200 dark:border-yellow-700 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">
                        {{ session('warning') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    @if (session()->has('info'))
        <div class="bg-blue-50 dark:bg-blue-900 border border-blue-200 dark:border-blue-700 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-800 dark:text-blue-200">
                        {{ session('info') }}
                    </p>
                </div>
            </div>
        </div>
    @endif

    {{-- TABEL TUJUAN --}}
    <div>
        {{-- Header untuk Tabel Tujuan --}}
        <div class="mb-4">
            <h2
                class="text-xl font-bold text-gray-900 dark:text-gray-100 bg-green-100 dark:bg-green-800 px-4 py-3 rounded-t-lg border border-gray-300 dark:border-gray-600">
                📋 DATA REALISASI TUJUAN - TAHUN {{ $currentYear }}
            </h2>
        </div>

        {{-- Tabel Tujuan --}}
        <div class="overflow-x-auto">
            <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
                <thead class="bg-green-50 dark:bg-green-800">
                    <tr>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                            TUJUAN
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                            INDIKATOR TUJUAN
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-green-100 dark:bg-green-900">
                            TARGET TAHUN
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                            TW. I
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                            TW. II
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                            TW. III
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                            TW. IV
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-blue-900">
                            PENCAPAIAN
                        </th>
                        <th
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                            STATUS
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tujuans as $tujuan)
                        <tr
                            class="hover:bg-gray-50 dark:hover:bg-gray-800 {{ $this->hasUnsavedChanges('tujuan', $tujuan->id) ? 'bg-yellow-50 dark:bg-yellow-900' : '' }}">
                            {{-- Tujuan Column --}}
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm bg-green-50 dark:bg-green-900">
                                <div class="font-medium text-gray-900 dark:text-gray-100">
                                    {{ $tujuan->tujuan ?? 'No Title' }}
                                </div>
                                @if ($this->hasUnsavedChanges('tujuan', $tujuan->id))
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 mt-1">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                            <path fill-rule="evenodd"
                                                d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Diubah
                                    </span>
                                @endif
                            </td>

                            {{-- Indikator Tujuan Column --}}
                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm">
                                {{ $tujuan->indikator_tujuan ?? '-' }}
                            </td>

                            {{-- Target Tahun (Input) --}}
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm text-center bg-green-25 dark:bg-green-950">
                                <input type="number"
                                    wire:model.live="realisasiData.sasaran.{{ $sasaran->id }}.target_tahun"
                                    class="w-24 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                    placeholder="{{ $sasaran->target ?? '0' }}" step="0.01">
                                <div class="text-xs text-gray-500 mt-1">{{ $sasaran->satuan ?? '' }}</div>
                            </td>

                            {{-- TW I-IV --}}
                            @for ($tw = 1; $tw <= 4; $tw++)
                                <td
                                    class="border border-gray-300 dark:border-gray-600 px-2 py-3 bg-orange-25 dark:bg-orange-950">
                                    <div class="text-center">
                                        <input type="number"
                                            wire:model.live="realisasiData.sasaran.{{ $sasaran->id }}.tw{{ $tw }}"
                                            class="w-24 px-1 py-1 text-center border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                                            placeholder="0" step="0.01">
                                    </div>
                                </td>
                            @endfor

                            {{-- Pencapaian Column --}}
                            <td
                                class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center bg-blue-25 dark:bg-blue-950">
                                @php
                                    $achievement = $this->getAchievementPercentage('sasaran', $sasaran->id);
                                @endphp
                                <div
                                    class="text-sm font-semibold {{ $achievement >= 100 ? 'text-green-600' : ($achievement >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ $achievement }}%
                                </div>
                                <div class="text-xs text-gray-500 mt-1">
                                    {{ ($this->realisasiData['sasaran'][$sasaran->id]['tw1'] ?? 0) +
                                        ($this->realisasiData['sasaran'][$sasaran->id]['tw2'] ?? 0) +
                                        ($this->realisasiData['sasaran'][$sasaran->id]['tw3'] ?? 0) +
                                        ($this->realisasiData['sasaran'][$sasaran->id]['tw4'] ?? 0) }}
                                    /
                                    {{ $this->realisasiData['sasaran'][$sasaran->id]['target_tahun'] ?? 0 }}
                                </div>
                            </td>

                            {{-- Status Column --}}
                            <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center">
                                @if ($this->hasAnySavedData('sasaran', $sasaran->id))
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        Tersimpan
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Belum Ada Data
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9"
                                class="border border-gray-300 dark:border-gray-600 px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data sasaran ditemukan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Summary Section --}}
    <div class="mt-8">
        <div class="bg-gray-50 dark:bg-gray-800 px-6 py-4 rounded-lg border border-gray-300 dark:border-gray-600">
            <div class="flex justify-between items-center">
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="font-semibold">{{ count($tujuans ?? []) }}</span> Tujuan |
                            <span class="font-semibold">{{ count($sasarans ?? []) }}</span> Sasaran
                        </div>
                        <div>
                            Tahun: <span class="font-semibold">{{ $currentYear }}</span>
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-gray-500">
                        Terakhir update: {{ now()->format('d/m/Y H:i') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div wire:loading wire:target="saveAllData"
        class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-sm w-full mx-4">
            <div class="flex items-center space-x-4">
                <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
                <span class="text-gray-700 dark:text-gray-300">Menyimpan data realisasi...</span>
            </div>
        </div>
    </div>

    {{-- JavaScript untuk UI Enhancement --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input[type="number"]');

            inputs.forEach(input => {
                // Visual feedback saat input berubah
                input.addEventListener('input', function() {
                    this.classList.add('ring-2', 'ring-yellow-300', 'border-yellow-400');
                    setTimeout(() => {
                        this.classList.remove('ring-2', 'ring-yellow-300',
                            'border-yellow-400');
                    }, 1000);
                });

                // Validasi real-time
                input.addEventListener('blur', function() {
                    const value = parseFloat(this.value);
                    if (value < 0) {
                        this.value = 0;
                        this.classList.add('ring-2', 'ring-red-300', 'border-red-400');
                        setTimeout(() => {
                            this.classList.remove('ring-2', 'ring-red-300',
                                'border-red-400');
                        }, 2000);
                    }
                });
            });

            // Keyboard shortcuts
            document.addEventListener('keydown', function(e) {
                // Ctrl+S untuk save
                if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                    e.preventDefault();
                    @this.call('saveAllData');
                }

                // Ctrl+R untuk reset (jika ada perubahan)
                if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
                    e.preventDefault();
                    if (confirm('Reset form ke data terakhir yang tersimpan?')) {
                        @this.call('resetForm');
                    }
                }
            });

            // Listen untuk Livewire events
            window.addEventListener('data-saved', event => {
                console.log('Data saved successfully:', event.detail.count);

                // Show temporary success indicator
                const saveBtn = document.querySelector('[wire\\:click="saveAllData"]');
                if (saveBtn) {
                    const originalText = saveBtn.innerHTML;
                    saveBtn.innerHTML =
                        '<svg class="w-4 h-4 mr-2 text-green-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>Tersimpan!';
                    saveBtn.classList.add('bg-green-600', 'hover:bg-green-700');
                    saveBtn.classList.remove('bg-blue-600', 'hover:bg-blue-700');

                    setTimeout(() => {
                        saveBtn.innerHTML = originalText;
                        saveBtn.classList.remove('bg-green-600', 'hover:bg-green-700');
                        saveBtn.classList.add('bg-blue-600', 'hover:bg-blue-700');
                    }, 2000);
                }
            });

            window.addEventListener('save-error', event => {
                console.error('Save error:', event.detail.message);
            });

            // Auto-scroll ke error jika ada
            const errorAlert = document.querySelector('.bg-red-50');
            if (errorAlert) {
                errorAlert.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });
            }
        });

        // Confirmation before leaving page if there are unsaved changes
        window.addEventListener('beforeunload', function(e) {
            // Check if there are changes through Livewire component
            if (document.querySelector('.bg-yellow-50')) {
                e.preventDefault();
                e.returnValue = 'Ada perubahan yang belum disimpan. Yakin ingin meninggalkan halaman?';
                return e.returnValue;
            }
        });
    </script>

    {{-- Custom CSS untuk styling tambahan --}}
    <style>
        /* Enhanced focus states */
        input[type="number"]:focus {
            transform: scale(1.02);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }

        /* Hover effects untuk rows */
        tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }

        /* Header styling */
        h2 {
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        /* Button hover animation */
        button {
            transition: all 0.2s ease;
        }

        button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        /* Loading animation enhancement */
        @keyframes pulse-blue {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .animate-pulse-blue {
            animation: pulse-blue 2s ease-in-out infinite;
        }

        /* Change indicator animation */
        .bg-yellow-50 {
            animation: highlight 0.5s ease-in-out;
        }

        @keyframes highlight {
            0% {
                background-color: transparent;
            }

            50% {
                background-color: rgb(254 240 138);
            }

            100% {
                background-color: rgb(254 249 195);
            }
        }
    </style>
</div>
wire:model.live="realisasiData.tujuan.{{ $tujuan->id }}.target_tahun"
class="w-24 px-2 py-1 text-center border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
placeholder="{{ $tujuan->target ?? '0' }}" step="0.01">
<div class="text-xs text-gray-500 mt-1">{{ $tujuan->satuan ?? '' }}</div>
</td>

{{-- TW I-IV --}}
@for ($tw = 1; $tw <= 4; $tw++)
    <td class="border border-gray-300 dark:border-gray-600 px-2 py-3 bg-orange-25 dark:bg-orange-950">
        <div class="text-center">
            <input type="number" wire:model.live="realisasiData.tujuan.{{ $tujuan->id }}.tw{{ $tw }}"
                class="w-24 px-1 py-1 text-center border border-gray-300 dark:border-gray-600 rounded text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
                placeholder="0" step="0.01">
        </div>
    </td>
@endfor

{{-- Pencapaian Column --}}
<td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center bg-blue-25 dark:bg-blue-950">
    @php
        $achievement = $this->getAchievementPercentage('tujuan', $tujuan->id);
    @endphp
    <div
        class="text-sm font-semibold {{ $achievement >= 100 ? 'text-green-600' : ($achievement >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
        {{ $achievement }}%
    </div>
    <div class="text-xs text-gray-500 mt-1">
        {{ ($this->realisasiData['tujuan'][$tujuan->id]['tw1'] ?? 0) +
            ($this->realisasiData['tujuan'][$tujuan->id]['tw2'] ?? 0) +
            ($this->realisasiData['tujuan'][$tujuan->id]['tw3'] ?? 0) +
            ($this->realisasiData['tujuan'][$tujuan->id]['tw4'] ?? 0) }}
        /
        {{ $this->realisasiData['tujuan'][$tujuan->id]['target_tahun'] ?? 0 }}
    </div>
</td>

{{-- Status Column --}}
<td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center">
    @if ($this->hasAnySavedData('tujuan', $tujuan->id))
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd"
                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                    clip-rule="evenodd" />
            </svg>
            Tersimpan
        </span>
    @else
        <span
            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Belum Ada Data
        </span>
    @endif
</td>
</tr>
@empty
<tr>
    <td colspan="9"
        class="border border-gray-300 dark:border-gray-600 px-4 py-8 text-center text-gray-500 dark:text-gray-400">
        Tidak ada data tujuan ditemukan
    </td>
</tr>
@endforelse
</tbody>
</table>
</div>
</div>

{{-- TABEL SASARAN --}}
<div>
    {{-- Header untuk Tabel Sasaran --}}
    <div class="mb-4">
        <h2
            class="text-xl font-bold text-gray-900 dark:text-gray-100 bg-blue-100 dark:bg-blue-800 px-4 py-3 rounded-t-lg border border-gray-300 dark:border-gray-600">
            🎯 DATA REALISASI SASARAN - TAHUN {{ $currentYear }}
        </h2>
    </div>

    {{-- Tabel Sasaran --}}
    <div class="overflow-x-auto">
        <table class="w-full border-collapse border border-gray-300 dark:border-gray-600">
            <thead class="bg-blue-50 dark:bg-blue-800">
                <tr>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                        SASARAN
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                        INDIKATOR SASARAN
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-green-100 dark:bg-green-900">
                        TARGET TAHUN
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                        TW. I
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                        TW. II
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibolf text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                        TW. III
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-orange-50 dark:bg-orange-900">
                        TW. IV
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100 bg-blue-50 dark:bg-blue-900">
                        PENCAPAIAN
                    </th>
                    <th
                        class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-center text-sm font-semibold text-gray-900 dark:text-gray-100">
                        STATUS
                    </th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sasarans as $sasaran)
                    <tr
                        class="hover:bg-gray-50 dark:hover:bg-gray-800 {{ $this->hasUnsavedChanges('sasaran', $sasaran->id) ? 'bg-yellow-50 dark:bg-yellow-900' : '' }}">
                        {{-- Sasaran Column --}}
                        <td
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm bg-blue-50 dark:bg-blue-900">
                            <div class="font-medium text-gray-900 dark:text-gray-100">
                                {{ $sasaran->sasaran ?? 'No Title' }}
                            </div>
                            @if ($this->hasUnsavedChanges('sasaran', $sasaran->id))
                                <span
                                    class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200 mt-1">
                                    <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z" />
                                        <path fill-rule="evenodd"
                                            d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                    Diubah
                                </span>
                            @endif
                        </td>

                        {{-- Indikator Sasaran Column --}}
                        <td class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm">
                            {{ $sasaran->indikator_sasaran ?? '-' }}
                        </td>

                        {{-- Target Tahun (Input) --}}
                        <td
                            class="border border-gray-300 dark:border-gray-600 px-4 py-3 text-sm text-center bg-green-25 dark:bg-green-950">
                            <input type="number"
