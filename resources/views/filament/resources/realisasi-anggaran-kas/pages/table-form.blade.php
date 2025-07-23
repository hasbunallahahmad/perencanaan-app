{{-- resources/views/filament/resources/realisasi-anggaran-kas/pages/table-form.blade.php --}}

<x-filament-panels::page>
    <div class="space-y-4">
        {{-- Filter Section --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-4">
            <div class="flex items-center space-x-4">
                <label class="text-sm font-medium text-gray-700 dark:text-gray-300">Tahun:</label>
                <div class="w-32">
                    <x-filament::input.wrapper>
                        <x-filament::input.select wire:model.live="selectedYear">
                            @php
                                $currentYear = date('Y');
                                $years = [];
                                for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                                    $years[$i] = $i;
                                }
                            @endphp
                            @foreach ($years as $year)
                                <option value="{{ $year }}" {{ $year == $selectedYear ? 'selected' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </x-filament::input.select>
                    </x-filament::input.wrapper>
                </div>
            </div>
        </div>

        {{-- Compact Table Form Section --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-xs divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th rowspan="2"
                                class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r min-w-[60px]">
                                Tahun
                            </th>
                            <th rowspan="2"
                                class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r min-w-[100px]">
                                Jenis Anggaran
                            </th>
                            <th rowspan="2"
                                class="px-2 py-2 text-left font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r min-w-[80px]">
                                Pagu
                            </th>
                            <th class="px-2 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r"
                                colspan="3">
                                TW I
                            </th>
                            <th class="px-2 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r"
                                colspan="3">
                                TW II
                            </th>
                            <th class="px-2 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r"
                                colspan="3">
                                TW III
                            </th>
                            <th class="px-2 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r"
                                colspan="3">
                                TW IV
                            </th>
                            <th rowspan="2"
                                class="px-2 py-2 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r min-w-[80px]">
                                Total Realisasi
                            </th>
                            <th rowspan="2"
                                class="px-2 py-2 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider min-w-[70px]">
                                %
                            </th>
                        </tr>
                        <tr class="bg-gray-100 dark:bg-gray-600">
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Rencana
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Realisasi
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-16">
                                Edit
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Rencana
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Realisasi
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-16">
                                Edit
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Rencana
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Realisasi
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-16">
                                Edit
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Rencana
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-20">
                                Realisasi
                            </th>
                            <th
                                class="px-1 py-1 text-center font-medium text-gray-500 dark:text-gray-300 uppercase tracking-wider border-r w-16">
                                Edit
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @foreach ($rencanaAnggaranKas as $index => $rencana)
                            @php
                                $existingRealisasi = \App\Models\RealisasiAnggaranKas::where(
                                    'rencana_anggaran_kas_id',
                                    $rencana->id,
                                )
                                    ->get()
                                    ->keyBy('triwulan');

                                $quarterlyAmount = $rencana->jumlah_rencana / 4;

                                $tw1_realisasi = $existingRealisasi->get(1)?->jumlah_realisasi ?? 0;
                                $tw2_realisasi = $existingRealisasi->get(2)?->jumlah_realisasi ?? 0;
                                $tw3_realisasi = $existingRealisasi->get(3)?->jumlah_realisasi ?? 0;
                                $tw4_realisasi = $existingRealisasi->get(4)?->jumlah_realisasi ?? 0;

                                $totalRealisasi = $tw1_realisasi + $tw2_realisasi + $tw3_realisasi + $tw4_realisasi;
                                $persentase =
                                    $rencana->jumlah_rencana > 0
                                        ? round(($totalRealisasi / $rencana->jumlah_rencana) * 100, 2)
                                        : 0;
                            @endphp
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-2 py-2 text-center font-medium text-gray-900 dark:text-gray-100 border-r">
                                    {{ $rencana->tahun }}
                                </td>
                                <td class="px-2 py-2 text-gray-900 dark:text-gray-100 border-r">
                                    <div class="truncate" title="{{ $rencana->jenis_anggaran_text }}">
                                        {{ $rencana->jenis_anggaran_text }}
                                    </div>
                                </td>
                                <td class="px-2 py-2 text-right text-gray-900 dark:text-gray-100 border-r">
                                    <div class="whitespace-nowrap text-xs"
                                        title="Rp {{ number_format($rencana->jumlah_rencana, 0, ',', '.') }}">
                                        Rp {{ number_format($rencana->jumlah_rencana, 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- TW 1 --}}
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw1'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="rencanaData.{{ $rencana->id }}.tw1"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw1')"
                                                placeholder="0"
                                                value="{{ number_format($quarterlyAmount, 0, '', '') }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($this->getRencanaValue($rencana->id, 'tw1') ?: $quarterlyAmount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw1'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="realisasiData.{{ $rencana->id }}.tw1"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw1')"
                                                placeholder="0" value="{{ $tw1_realisasi }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($tw1_realisasi, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r text-center">
                                    @if ($this->isEditable($rencana->id, 'tw1'))
                                        <x-filament::icon-button icon="heroicon-o-lock-closed" color="success"
                                            size="xs" wire:click="lockQuarter({{ $rencana->id }}, 'tw1')"
                                            tooltip="Kunci TW 1" />
                                    @else
                                        <x-filament::icon-button icon="heroicon-o-pencil" color="warning" size="xs"
                                            wire:click="unlockQuarter({{ $rencana->id }}, 'tw1')"
                                            tooltip="Edit TW 1" />
                                    @endif
                                </td>

                                {{-- TW 2 --}}
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw2'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="rencanaData.{{ $rencana->id }}.tw2"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw2')"
                                                placeholder="0"
                                                value="{{ number_format($quarterlyAmount, 0, '', '') }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($this->getRencanaValue($rencana->id, 'tw2') ?: $quarterlyAmount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw2'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="realisasiData.{{ $rencana->id }}.tw2"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw2')"
                                                placeholder="0" value="{{ $tw2_realisasi }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($tw2_realisasi, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r text-center">
                                    @if ($this->isEditable($rencana->id, 'tw2'))
                                        <x-filament::icon-button icon="heroicon-o-lock-closed" color="success"
                                            size="xs" wire:click="lockQuarter({{ $rencana->id }}, 'tw2')"
                                            tooltip="Kunci TW 2" />
                                    @else
                                        <x-filament::icon-button icon="heroicon-o-pencil" color="warning"
                                            size="xs" wire:click="unlockQuarter({{ $rencana->id }}, 'tw2')"
                                            tooltip="Edit TW 2" />
                                    @endif
                                </td>

                                {{-- TW 3 --}}
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw3'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="rencanaData.{{ $rencana->id }}.tw3"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw3')"
                                                placeholder="0"
                                                value="{{ number_format($quarterlyAmount, 0, '', '') }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($this->getRencanaValue($rencana->id, 'tw3') ?: $quarterlyAmount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw3'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="realisasiData.{{ $rencana->id }}.tw3"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw3')"
                                                placeholder="0" value="{{ $tw3_realisasi }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($tw3_realisasi, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r text-center">
                                    @if ($this->isEditable($rencana->id, 'tw3'))
                                        <x-filament::icon-button icon="heroicon-o-lock-closed" color="success"
                                            size="xs" wire:click="lockQuarter({{ $rencana->id }}, 'tw3')"
                                            tooltip="Kunci TW 3" />
                                    @else
                                        <x-filament::icon-button icon="heroicon-o-pencil" color="warning"
                                            size="xs" wire:click="unlockQuarter({{ $rencana->id }}, 'tw3')"
                                            tooltip="Edit TW 3" />
                                    @endif
                                </td>

                                {{-- TW 4 --}}
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw4'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="rencanaData.{{ $rencana->id }}.tw4"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw4')"
                                                placeholder="0"
                                                value="{{ number_format($quarterlyAmount, 0, '', '') }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($this->getRencanaValue($rencana->id, 'tw4') ?: $quarterlyAmount, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r">
                                    @if ($this->isEditable($rencana->id, 'tw4'))
                                        <x-filament::input.wrapper class="w-full">
                                            <x-filament::input type="number"
                                                wire:model.live="realisasiData.{{ $rencana->id }}.tw4"
                                                wire:change="saveQuarterlyData({{ $rencana->id }}, 'tw4')"
                                                placeholder="0" value="{{ $tw4_realisasi }}"
                                                class="text-xs text-right p-1 h-8" min="0" step="1000" />
                                        </x-filament::input.wrapper>
                                    @else
                                        <div
                                            class="text-xs text-right p-1 h-8 flex items-center justify-end bg-gray-100 dark:bg-gray-600 rounded border">
                                            {{ number_format($tw4_realisasi, 0, ',', '.') }}
                                        </div>
                                    @endif
                                </td>
                                <td class="px-1 py-2 border-r text-center">
                                    @if ($this->isEditable($rencana->id, 'tw4'))
                                        <x-filament::icon-button icon="heroicon-o-lock-closed" color="success"
                                            size="xs" wire:click="lockQuarter({{ $rencana->id }}, 'tw4')"
                                            tooltip="Kunci TW 4" />
                                    @else
                                        <x-filament::icon-button icon="heroicon-o-pencil" color="warning"
                                            size="xs" wire:click="unlockQuarter({{ $rencana->id }}, 'tw4')"
                                            tooltip="Edit TW 4" />
                                    @endif
                                </td>

                                {{-- Total Realisasi --}}
                                <td class="px-2 py-2 text-right font-medium text-gray-900 dark:text-gray-100 border-r">
                                    <div class="whitespace-nowrap text-xs">
                                        Rp {{ number_format($this->getTotalRealisasi($rencana->id), 0, ',', '.') }}
                                    </div>
                                </td>

                                {{-- Persentase --}}
                                <td class="px-2 py-2 text-center">
                                    @php
                                        $persentase = $this->getPersentase($rencana->id);
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium
                                        {{ $persentase >= 100 ? 'bg-green-100 text-green-800' : ($persentase >= 75 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                                        {{ $persentase }}%
                                    </span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Compact Action Buttons --}}
        <div class="flex justify-end space-x-2">
            <x-filament::button wire:click="unlockAll" color="warning" icon="heroicon-o-lock-open" size="sm">
                Unlock Semua
            </x-filament::button>

            <x-filament::button wire:click="resetForm" color="gray" icon="heroicon-o-arrow-path" size="sm">
                Reset
            </x-filament::button>
        </div>
    </div>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <div class="fixed top-4 right-4 bg-green-500 text-white px-4 py-2 rounded-lg shadow-lg z-50">
            {{ session('message') }}
        </div>
    @endif

    {{-- Custom CSS untuk optimasi tampilan --}}
    <style>
        /* Kompak table styling */
        .compact-table th,
        .compact-table td {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }

        /* Input field styling untuk angka penuh */
        .compact-table input[type="number"] {
            font-size: 0.75rem;
            padding: 0.125rem 0.25rem;
            height: 1.75rem;
            min-width: 90px;
        }

        /* Locked field styling */
        .locked-field {
            background-color: #f3f4f6;
            border: 1px solid #d1d5db;
            color: #6b7280;
        }

        /* Format angka dengan separator ribuan */
        .currency-format {
            font-family: 'Courier New', monospace;
            letter-spacing: 0.05em;
        }

        /* Responsive adjustments */
        @media (max-width: 1024px) {
            .compact-table {
                font-size: 0.625rem;
            }

            .compact-table input[type="number"] {
                min-width: 80px;
                font-size: 0.625rem;
            }
        }

        @media (max-width: 768px) {
            .compact-table input[type="number"] {
                min-width: 70px;
                font-size: 0.625rem;
            }
        }

        /* Scroll indicator */
        .overflow-x-auto {
            scrollbar-width: thin;
            scrollbar-color: #cbd5e0 #f7fafc;
        }

        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f7fafc;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background-color: #cbd5e0;
            border-radius: 4px;
        }

        /* Hover effect untuk input */
        .compact-table input[type="number"]:hover {
            border-color: #3b82f6;
        }

        .compact-table input[type="number"]:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 1px #3b82f6;
        }

        /* Animation untuk success message */
        .fixed.top-4.right-4 {
            animation: slideIn 0.3s ease-out;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
    </style>

    {{-- JavaScript untuk auto-hide success message --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide success message after 3 seconds
            setTimeout(function() {
                const message = document.querySelector('.fixed.top-4.right-4');
                if (message) {
                    message.style.transform = 'translateX(100%)';
                    message.style.opacity = '0';
                    setTimeout(() => message.remove(), 300);
                }
            }, 3000);
        });
    </script>
</x-filament-panels::page>
