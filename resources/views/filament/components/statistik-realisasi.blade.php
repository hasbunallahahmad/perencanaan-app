<div class="space-y-6">
    <!-- Statistik Utama -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Rencana -->
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-medium">Total Rencana Aksi</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_rencana']) }}</p>
                </div>
                <div class="bg-blue-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Realisasi -->
        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm font-medium">Total Realisasi</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_realisasi']) }}</p>
                </div>
                <div class="bg-green-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Peserta -->
        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-medium">Total Peserta</p>
                    <p class="text-3xl font-bold">{{ number_format($stats['total_peserta']) }}</p>
                </div>
                <div class="bg-purple-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Anggaran -->
        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm font-medium">Total Anggaran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($stats['total_anggaran'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-orange-400 bg-opacity-30 rounded-full p-3">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Keseluruhan -->
    @php
        $progressOverall =
            $stats['total_rencana'] > 0 ? round(($stats['total_realisasi'] / $stats['total_rencana']) * 100, 1) : 0;
    @endphp
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Progress Realisasi Keseluruhan</h3>
            <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                         @if ($progressOverall >= 80) bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                         @elseif($progressOverall >= 50) bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                         @else bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200 @endif">
                {{ $progressOverall }}%
            </span>
        </div>

        <div class="w-full bg-gray-200 rounded-full h-3 dark:bg-gray-700 mb-2">
            <div class="h-3 rounded-full transition-all duration-500 ease-out
                        @if ($progressOverall >= 80) bg-green-600
                        @elseif($progressOverall >= 50) bg-yellow-500
                        @else bg-red-500 @endif"
                style="width: {{ $progressOverall }}%"></div>
        </div>

        <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400">
            <span>{{ number_format($stats['total_realisasi']) }} Realisasi</span>
            <span>{{ number_format($stats['total_rencana']) }} Rencana</span>
        </div>
    </div>

    <!-- Statistik per Bidang -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Realisasi per Bidang -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Realisasi per Bidang</h3>

            @if ($stats['realisasi_per_bidang']->count() > 0)
                @php
                    $maxRealisasi = $stats['realisasi_per_bidang']->max('total') ?: 1;
                    $colors = [
                        'bg-blue-500',
                        'bg-green-500',
                        'bg-purple-500',
                        'bg-orange-500',
                        'bg-red-500',
                        'bg-indigo-500',
                        'bg-pink-500',
                        'bg-teal-500',
                    ];
                @endphp

                <div class="space-y-4">
                    @foreach ($stats['realisasi_per_bidang'] as $index => $bidang)
                        @php
                            $percentage = ($bidang->total / $maxRealisasi) * 100;
                            $colorClass = $colors[$index % count($colors)];
                        @endphp

                        <div class="group">
                            <div class="flex items-center justify-between mb-2">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate pr-2">
                                    {{ $bidang->nama }}
                                </h4>
                                <div class="flex items-center space-x-2 text-sm">
                                    <span
                                        class="text-gray-600 dark:text-gray-400">{{ number_format($bidang->total) }}</span>
                                    <span class="text-gray-500 dark:text-gray-500">realisasi</span>
                                </div>
                            </div>

                            <div class="w-full bg-gray-200 rounded-full h-2.5 dark:bg-gray-700">
                                <div class="{{ $colorClass }} h-2.5 rounded-full transition-all duration-700 ease-out group-hover:opacity-80"
                                    style="width: {{ $percentage }}%"></div>
                            </div>

                            <!-- Detail Info -->
                            <div class="mt-2 text-xs text-gray-500 dark:text-gray-400 flex justify-between">
                                <span>{{ number_format($bidang->peserta) }} peserta</span>
                                <span>Rp {{ number_format($bidang->anggaran, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 00-2-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada data realisasi</p>
                </div>
            @endif
        </div>

        <!-- Top Performing Bidang -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Top Performing Bidang</h3>

            @if ($stats['realisasi_per_bidang']->count() > 0)
                <div class="space-y-4">
                    @foreach ($stats['realisasi_per_bidang']->take(5) as $index => $bidang)
                        <div class="flex items-center space-x-4 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-shrink-0">
                                <div
                                    class="w-8 h-8 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                    <span class="text-white text-sm font-bold">{{ $index + 1 }}</span>
                                </div>
                            </div>

                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $bidang->nama }}
                                </h4>
                                <div class="flex items-center space-x-4 mt-1 text-xs text-gray-500 dark:text-gray-400">
                                    <span>{{ number_format($bidang->total) }} realisasi</span>
                                    <span>{{ number_format($bidang->peserta) }} peserta</span>
                                </div>
                            </div>

                            <div class="flex-shrink-0 text-right">
                                <div class="text-sm font-medium text-gray-900 dark:text-white">
                                    Rp {{ number_format($bidang->anggaran / 1000000, 1) }}M
                                </div>
                                <div class="text-xs text-gray-500 dark:text-gray-400">anggaran</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Belum ada data untuk ditampilkan</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Status Summary -->
    @php
        // Hitung status berdasarkan data yang ada
        $statusCounts = [
            'selesai' => 0,
            'sebagian' => 0,
            'belum' => 0,
        ];

        // Ambil data rencana aksi dengan realisasi
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

    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Status Pelaksanaan Kegiatan</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Selesai -->
            <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-800 dark:text-green-200">Selesai</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                            {{ number_format($statusCounts['selesai']) }}</p>
                        @if ($totalStatus > 0)
                            <p class="text-xs text-green-600 dark:text-green-400">
                                {{ number_format(($statusCounts['selesai'] / $totalStatus) * 100, 1) }}% dari total
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Sebagian -->
            <div
                class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-800 dark:text-yellow-200">Dalam Progress</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                            {{ number_format($statusCounts['sebagian']) }}</p>
                        @if ($totalStatus > 0)
                            <p class="text-xs text-yellow-600 dark:text-yellow-400">
                                {{ number_format(($statusCounts['sebagian'] / $totalStatus) * 100, 1) }}% dari total
                            </p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Belum Terlaksana -->
            <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-red-500 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </div>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800 dark:text-red-200">Belum Terlaksana</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                            {{ number_format($statusCounts['belum']) }}</p>
                        @if ($totalStatus > 0)
                            <p class="text-xs text-red-600 dark:text-red-400">
                                {{ number_format(($statusCounts['belum'] / $totalStatus) * 100, 1) }}% dari total
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Insight dan Rekomendasi -->
    <div class="bg-gradient-to-r from-indigo-500 to-purple-600 rounded-lg shadow-lg p-6 text-white">
        <h3 class="text-lg font-semibold mb-4">💡 Insights & Rekomendasi</h3>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <h4 class="font-medium mb-2">📊 Tingkat Realisasi</h4>
                @if ($progressOverall >= 80)
                    <p class="text-sm">Excellent! Tingkat realisasi sangat baik ({{ $progressOverall }}%). Pertahankan
                        momentum ini.</p>
                @elseif($progressOverall >= 50)
                    <p class="text-sm">Good progress! Masih ada ruang untuk peningkatan. Target 80% realisasi sangat
                        achievable.</p>
                @else
                    <p class="text-sm">Perlu peningkatan focus. Identifikasi hambatan dan buat action plan untuk
                        mempercepat realisasi.</p>
                @endif
            </div>

            <div class="bg-white bg-opacity-20 rounded-lg p-4">
                <h4 class="font-medium mb-2">🎯 Rekomendasi</h4>
                @if ($statusCounts['belum'] > $statusCounts['selesai'])
                    <p class="text-sm">Fokus pada kegiatan yang belum terlaksana. Pertimbangkan realokasi resources.
                    </p>
                @elseif($statusCounts['sebagian'] > 0)
                    <p class="text-sm">Prioritaskan penyelesaian kegiatan yang sudah berjalan sebagian.</p>
                @else
                    <p class="text-sm">Performance excellent! Siapkan planning untuk periode berikutnya.</p>
                @endif
            </div>
        </div>
    </div>
</div>
