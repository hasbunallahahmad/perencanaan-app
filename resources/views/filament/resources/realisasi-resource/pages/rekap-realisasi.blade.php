<x-filament-panels::page>
    <div class="space-y-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            @php
                $totalRencana = App\Models\RencanaAksi::count();
                $totalRealisasi = App\Models\Realisasi::count();
                $totalPeserta = App\Models\Realisasi::sum('jumlah_peserta');
                $totalAnggaran = App\Models\Realisasi::sum('realisasi_anggaran');
                $progressOverall = $totalRencana > 0 ? round(($totalRealisasi / $totalRencana) * 100, 1) : 0;
            @endphp

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                        <svg class="w-6 h-6 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Rencana</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalRencana) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                        <svg class="w-6 h-6 text-green-600 dark:text-green-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Realisasi</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalRealisasi) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                        <svg class="w-6 h-6 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Peserta</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ number_format($totalPeserta) }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 dark:bg-yellow-900 rounded-lg">
                        <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                            </path>
                        </svg>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Anggaran</p>
                        <p class="text-2xl font-semibold text-gray-900 dark:text-white">Rp
                            {{ number_format($totalAnggaran, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Progress Bar -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 mb-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Progress Keseluruhan</h3>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $progressOverall }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                <div class="bg-blue-600 h-2.5 rounded-full transition-all duration-300"
                    style="width: {{ $progressOverall }}%"></div>
            </div>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                {{ $totalRealisasi }} dari {{ $totalRencana }} rencana aksi telah terealisasi
            </p>
        </div>

        <!-- Chart Section (Optional) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Realisasi per Bidang</h3>
                <div class="space-y-3">
                    @php
                        $realisasiPerBidang = App\Models\Realisasi::join(
                            'rencana_aksi',
                            'realisasi.rencana_aksi_id',
                            '=',
                            'rencana_aksi.id',
                        )
                            ->join('bidang', 'rencana_aksi.bidang_id', '=', 'bidang.id')
                            ->selectRaw('bidang.nama, COUNT(*) as total')
                            ->groupBy('bidang.id', 'bidang.nama')
                            ->orderBy('total', 'desc')
                            ->get();
                        $maxRealisasi = $realisasiPerBidang->max('total') ?: 1;
                    @endphp

                    @forelse($realisasiPerBidang as $bidang)
                        <div class="flex items-center">
                            <div class="w-1/3">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $bidang->nama }}</p>
                            </div>
                            <div class="w-2/3 ml-4">
                                <div class="flex items-center">
                                    <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 mr-2">
                                        <div class="bg-green-600 h-2 rounded-full transition-all duration-300"
                                            style="width: {{ ($bidang->total / $maxRealisasi) * 100 }}%"></div>
                                    </div>
                                    <span
                                        class="text-sm font-medium text-gray-600 dark:text-gray-400 min-w-0">{{ $bidang->total }}</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 dark:text-gray-400 text-center py-4">Belum ada data realisasi
                        </p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status Pelaksanaan</h3>
                @php
                    $statusCounts = [
                        'selesai' => 0,
                        'sebagian' => 0,
                        'belum' => 0,
                    ];

                    $rencanaAksiList = App\Models\RencanaAksi::withCount('realisasi')->get();

                    foreach ($rencanaAksiList as $rencana) {
                        $totalRencanaAksi = count($rencana->rencana_aksi_list ?? []);
                        $totalRealisasi = $rencana->realisasi_count;

                        if ($totalRealisasi == 0) {
                            $statusCounts['belum']++;
                        } elseif ($totalRealisasi >= $totalRencanaAksi) {
                            $statusCounts['selesai']++;
                        } else {
                            $statusCounts['sebagian']++;
                        }
                    }

                    $totalStatus = array_sum($statusCounts);
                @endphp

                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Selesai</span>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $statusCounts['selesai'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-yellow-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Sebagian</span>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $statusCounts['sebagian'] }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-3 h-3 bg-red-500 rounded-full mr-2"></div>
                            <span class="text-sm text-gray-600 dark:text-gray-400">Belum Terlaksana</span>
                        </div>
                        <span
                            class="text-sm font-medium text-gray-900 dark:text-white">{{ $statusCounts['belum'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
