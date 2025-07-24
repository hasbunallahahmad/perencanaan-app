{{-- Enhanced version dengan perbaikan --}}
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form untuk pilih tahun --}}
        <div class="bg-white rounded-lg shadow p-6">
            {{ $this->form }}
        </div>

        {{-- Statistics Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-document-text class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Total Items</p>
                        <p class="text-lg font-semibold text-blue-600">{{ $this->tujuanSasaranData->count() }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-check class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Tercapai</p>
                        <p class="text-lg font-semibold text-green-600">
                            {{ $this->tujuanSasaranData->where('status_pencapaian', 'Tercapai')->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-clock class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Dalam Proses</p>
                        <p class="text-lg font-semibold text-yellow-600">
                            {{ $this->tujuanSasaranData->whereIn('status_pencapaian', ['Baik', 'Cukup'])->count() }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <x-heroicon-o-exclamation-triangle class="w-4 h-4 text-white" />
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">Kurang</p>
                        <p class="text-lg font-semibold text-red-600">
                            {{ $this->tujuanSasaranData->where('status_pencapaian', 'Kurang')->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="bg-gray-700 text-white p-4">
                <h2 class="text-lg font-semibold text-center">
                    INPUT REALISASI TUJUAN DAN SASARAN RENSTRA HASIL REVIEW
                </h2>
                <p class="text-center text-sm text-gray-300 mt-1">
                    Tahun {{ $this->selectedTahun ?? date('Y') }}
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full table-fixed">
                    <thead class="bg-gray-600 text-white text-sm">
                        <tr>
                            <th class="p-3 border border-gray-400 w-48">TUJUAN</th>
                            <th class="p-3 border border-gray-400 w-48">SASARAN</th>
                            <th class="p-3 border border-gray-400 w-40">INDIKATOR</th>
                            <th class="p-3 border border-gray-400 w-20">TIPE</th>
                            <th class="p-3 border border-gray-400 w-32">TARGET<br>TAHUN</th>
                            <th class="p-3 border border-gray-400 w-32">REALISASI<br>TAHUN</th>
                            <th class="p-3 border border-gray-400 w-24">PENETAPAN<br>TARGET</th>
                            <th class="p-3 border border-gray-400 w-24">TW. I</th>
                            <th class="p-3 border border-gray-400 w-24">TW. II</th>
                            <th class="p-3 border border-gray-400 w-24">TW. III</th>
                            <th class="p-3 border border-gray-400 w-24">TW. IV</th>
                            <th class="p-3 border border-gray-400 w-24">STATUS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($this->tujuanSasaranData as $index => $item)
                            @php
                                $statusColor = match ($item['status_pencapaian']) {
                                    'Tercapai' => 'bg-green-50 border-l-4 border-l-green-500',
                                    'Baik' => 'bg-blue-50 border-l-4 border-l-blue-500',
                                    'Cukup' => 'bg-yellow-50 border-l-4 border-l-yellow-500',
                                    'Kurang' => 'bg-red-50 border-l-4 border-l-red-500',
                                    default => 'bg-gray-50',
                                };
                            @endphp
                            <tr class="{{ $statusColor }}">
                                {{-- Tujuan --}}
                                <td class="p-3 border border-gray-300 text-sm">
                                    <div class="w-full">
                                        <p class="font-medium text-gray-900 break-words">{{ $item['tujuan'] }}</p>
                                    </div>
                                </td>

                                {{-- Sasaran --}}
                                <td class="p-3 border border-gray-300 text-sm">
                                    <div class="w-full">
                                        <p class="text-gray-700 break-words">{{ $item['sasaran'] }}</p>
                                    </div>
                                </td>

                                {{-- Indikator --}}
                                <td class="p-3 border border-gray-300 text-sm">
                                    <textarea wire:blur="updateIndikator({{ $index }}, $event.target.value)"
                                        class="w-full p-2 border border-gray-300 rounded text-sm resize-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        rows="3" placeholder="Masukkan indikator...">{{ $item['indikator'] }}</textarea>
                                </td>

                                {{-- Tipe --}}
                                <td class="p-3 border border-gray-300 text-center">
                                    <span
                                        class="inline-block bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs font-medium whitespace-nowrap">
                                        UMUM+
                                    </span>
                                </td>

                                {{-- Target Tahun --}}
                                <td class="p-3 border border-gray-300 text-center">
                                    <div class="space-y-2">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ number_format($item['target'] ?? 0, 2) }} {{ $item['satuan'] ?? '%' }}
                                        </div>
                                        <div class="flex flex-col space-y-1">
                                            <input type="number" step="0.01" value="{{ $item['target'] ?? '' }}"
                                                wire:blur="updateTarget({{ $index }}, $event.target.value)"
                                                class="w-full p-1 border border-gray-300 rounded text-sm text-center focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                                min="0" placeholder="0" />
                                            <select
                                                wire:change="updateSatuan({{ $index }}, $event.target.value)"
                                                class="w-full p-1 border border-gray-300 rounded text-xs focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                                <option value="%"
                                                    {{ ($item['satuan'] ?? '%') == '%' ? 'selected' : '' }}>%</option>
                                                <option value="unit"
                                                    {{ ($item['satuan'] ?? '%') == 'unit' ? 'selected' : '' }}>unit
                                                </option>
                                                <option value="orang"
                                                    {{ ($item['satuan'] ?? '%') == 'orang' ? 'selected' : '' }}>orang
                                                </option>
                                                <option value="dokumen"
                                                    {{ ($item['satuan'] ?? '%') == 'dokumen' ? 'selected' : '' }}>
                                                    dokumen</option>
                                                <option value="kegiatan"
                                                    {{ ($item['satuan'] ?? '%') == 'kegiatan' ? 'selected' : '' }}>
                                                    kegiatan</option>
                                            </select>
                                        </div>
                                    </div>
                                </td>

                                {{-- Realisasi Tahun --}}
                                <td class="p-3 border border-gray-300 text-center">
                                    <div class="space-y-1">
                                        <div class="text-sm font-semibold text-gray-900">
                                            {{ number_format($item['total_realisasi'] ?? 0, 2) }}
                                            {{ $item['satuan'] ?? '%' }}
                                        </div>
                                        <div
                                            class="text-xs px-2 py-1 rounded font-medium {{ ($item['persentase_calculated'] ?? 0) >= 100
                                                ? 'bg-green-100 text-green-800'
                                                : (($item['persentase_calculated'] ?? 0) >= 75
                                                    ? 'bg-blue-100 text-blue-800'
                                                    : (($item['persentase_calculated'] ?? 0) >= 50
                                                        ? 'bg-yellow-100 text-yellow-800'
                                                        : 'bg-red-100 text-red-800')) }}">
                                            {{ number_format($item['persentase_calculated'] ?? 0, 1) }}%
                                        </div>
                                    </div>
                                </td>

                                {{-- Penetapan Target --}}
                                <td class="p-3 border border-gray-300 text-center">
                                    <div class="bg-gray-100 p-2 rounded text-sm font-medium">
                                        {{ number_format($item['target'] ?? 0, 2) }}
                                    </div>
                                </td>

                                {{-- TW I --}}
                                <td class="p-3 border border-gray-300">
                                    <div class="space-y-2">
                                        <div class="bg-gray-100 p-2 rounded text-center text-sm">
                                            {{ number_format($item['realisasi_tw_1'] ?? 0, 2) }}
                                        </div>
                                        <div class="bg-teal-500 text-white p-1 rounded text-center relative">
                                            <input type="number" step="0.01"
                                                value="{{ $item['realisasi_tw_1'] ?? '' }}"
                                                wire:blur="updateRealisasi({{ $index }}, 'realisasi_tw_1', $event.target.value)"
                                                class="w-full bg-transparent text-white text-center text-sm border-none outline-none placeholder-teal-300"
                                                min="0" placeholder="0" />
                                            @if ($item['realisasi_tw_1'] !== null && $item['realisasi_tw_1'] > 0)
                                                <div class="absolute -top-1 -right-1">
                                                    <span
                                                        class="bg-green-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">✓</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- TW II --}}
                                <td class="p-3 border border-gray-300">
                                    <div class="space-y-2">
                                        <div class="bg-gray-100 p-2 rounded text-center text-sm">
                                            {{ number_format($item['realisasi_tw_2'] ?? 0, 2) }}
                                        </div>
                                        <div class="bg-teal-500 text-white p-1 rounded text-center relative">
                                            <input type="number" step="0.01"
                                                value="{{ $item['realisasi_tw_2'] ?? '' }}"
                                                wire:blur="updateRealisasi({{ $index }}, 'realisasi_tw_2', $event.target.value)"
                                                class="w-full bg-transparent text-white text-center text-sm border-none outline-none placeholder-teal-300"
                                                min="0" placeholder="0" />
                                            @if ($item['realisasi_tw_2'] !== null && $item['realisasi_tw_2'] > 0)
                                                <div class="absolute -top-1 -right-1">
                                                    <span
                                                        class="bg-green-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">✓</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- TW III --}}
                                <td class="p-3 border border-gray-300">
                                    <div class="space-y-2">
                                        <div class="bg-gray-100 p-2 rounded text-center text-sm">
                                            {{ number_format($item['realisasi_tw_3'] ?? 0, 2) }}
                                        </div>
                                        <div class="bg-teal-500 text-white p-1 rounded text-center relative">
                                            <input type="number" step="0.01"
                                                value="{{ $item['realisasi_tw_3'] ?? '' }}"
                                                wire:blur="updateRealisasi({{ $index }}, 'realisasi_tw_3', $event.target.value)"
                                                class="w-full bg-transparent text-white text-center text-sm border-none outline-none placeholder-teal-300"
                                                min="0" placeholder="0" />
                                            @if ($item['realisasi_tw_3'] !== null && $item['realisasi_tw_3'] > 0)
                                                <div class="absolute -top-1 -right-1">
                                                    <span
                                                        class="bg-green-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">✓</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- TW IV --}}
                                <td class="p-3 border border-gray-300">
                                    <div class="space-y-2">
                                        <div class="bg-gray-100 p-2 rounded text-center text-sm">
                                            {{ number_format($item['realisasi_tw_4'] ?? 0, 2) }}
                                        </div>
                                        <div class="bg-teal-500 text-white p-1 rounded text-center relative">
                                            <input type="number" step="0.01"
                                                value="{{ $item['realisasi_tw_4'] ?? '' }}"
                                                wire:blur="updateRealisasi({{ $index }}, 'realisasi_tw_4', $event.target.value)"
                                                class="w-full bg-transparent text-white text-center text-sm border-none outline-none placeholder-teal-300"
                                                min="0" placeholder="0" />
                                            @if ($item['realisasi_tw_4'] !== null && $item['realisasi_tw_4'] > 0)
                                                <div class="absolute -top-1 -right-1">
                                                    <span
                                                        class="bg-green-500 text-white text-xs rounded-full w-4 h-4 flex items-center justify-center">✓</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="p-3 border border-gray-300 text-center">
                                    @php
                                        $badgeColor = match ($item['status_pencapaian']) {
                                            'Tercapai' => 'bg-green-100 text-green-800 border-green-200',
                                            'Baik' => 'bg-blue-100 text-blue-800 border-blue-200',
                                            'Cukup' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                                            'Kurang' => 'bg-red-100 text-red-800 border-red-200',
                                            default => 'bg-gray-100 text-gray-800 border-gray-200',
                                        };
                                    @endphp
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $badgeColor }}">
                                        {{ $item['status_pencapaian'] }}
                                    </span>

                                    {{-- Data completion indicator --}}
                                    <div class="mt-1">
                                        @if ($item['is_complete'] ?? false)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-green-100 text-green-700">
                                                <x-heroicon-s-check class="w-3 h-3 mr-1" />
                                                Lengkap
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded text-xs bg-yellow-100 text-yellow-700">
                                                <x-heroicon-s-exclamation-triangle class="w-3 h-3 mr-1" />
                                                Belum Lengkap
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="p-8 text-center text-gray-500">
                                    <div class="flex flex-col items-center space-y-2">
                                        <x-heroicon-o-inbox class="w-12 h-12 text-gray-400" />
                                        <p class="font-medium">Tidak ada data master tujuan dan sasaran yang aktif</p>
                                        <p class="text-sm">Silakan tambahkan data master terlebih dahulu</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer info --}}
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <div
                    class="flex flex-col md:flex-row justify-between items-start md:items-center space-y-2 md:space-y-0">
                    <div class="flex flex-wrap items-center space-x-4 text-sm text-gray-600">
                        <span>Total: <strong>{{ $this->tujuanSasaranData->count() }}</strong> item</span>
                        <span class="hidden md:inline">•</span>
                        <span>Sudah ada:
                            <strong>{{ $this->tujuanSasaranData->where('is_existing', true)->count() }}</strong>
                            item</span>
                        <span class="hidden md:inline">•</span>
                        <span>Lengkap:
                            <strong>{{ $this->tujuanSasaranData->where('is_complete', true)->count() }}</strong>
                            item</span>
                    </div>
                    <div class="flex items-center space-x-3">
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Tercapai</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Baik</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Cukup</span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <div class="w-3 h-3 bg-red-500 rounded-full"></div>
                            <span class="text-xs text-gray-600">Kurang</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Auto-save indicator --}}
        <div class="fixed bottom-4 right-4 z-50" wire:loading
            wire:target="updateRealisasi,updateTarget,updateIndikator,updateSatuan">
            <div class="bg-blue-500 text-white px-4 py-2 rounded-lg shadow-lg flex items-center space-x-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <span class="text-sm">Menyimpan...</span>
            </div>
        </div>

        {{-- Progress indicator for data completion --}}
        @if ($this->tujuanSasaranData->count() > 0)
            <div class="bg-white rounded-lg shadow p-4">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-sm font-medium text-gray-900">Progress Kelengkapan Data</h3>
                    <span class="text-sm text-gray-500">
                        {{ $this->tujuanSasaranData->where('is_complete', true)->count() }} /
                        {{ $this->tujuanSasaranData->count() }}
                    </span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    @php
                        $completionPercentage =
                            $this->tujuanSasaranData->count() > 0
                                ? ($this->tujuanSasaranData->where('is_complete', true)->count() /
                                        $this->tujuanSasaranData->count()) *
                                    100
                                : 0;
                    @endphp
                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                        style="width: {{ $completionPercentage }}%"></div>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    {{ number_format($completionPercentage, 1) }}% data telah dilengkapi
                </p>
            </div>
        @endif
    </div>

    <style>
        /* Custom scrollbar untuk tabel */
        .overflow-x-auto::-webkit-scrollbar {
            height: 8px;
        }

        .overflow-x-auto::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        .overflow-x-auto::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Improve table responsiveness */
        .table-fixed {
            min-width: 1200px;
        }

        /* Better input focus states */
        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
        }

        /* Loading state untuk inputs */
        .loading-input {
            opacity: 0.7;
            pointer-events: none;
        }
    </style>
</x-filament-panels::page>
