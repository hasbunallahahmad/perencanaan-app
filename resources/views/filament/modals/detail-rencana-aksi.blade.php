<div class="space-y-6">
    <!-- Header Information -->
    <div class="bg-gray-50 dark:bg-gray-900 rounded-lg p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Informasi Umum</h3>
                <div class="space-y-3">
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Bidang</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $rencanaAksi->bidang?->nama ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Program</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $rencanaAksi->program?->nama_program ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Kegiatan</label>
                        <p class="text-sm text-gray-900 dark:text-white">
                            {{ $rencanaAksi->kegiatan?->nama_kegiatan ?? 'N/A' }}</p>
                    </div>
                    @if ($rencanaAksi->subKegiatan)
                        <div>
                            <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Sub Kegiatan</label>
                            <p class="text-sm text-gray-900 dark:text-white">
                                {{ $rencanaAksi->subKegiatan->nama_sub_kegiatan }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Status & Progress</h3>
                <div class="space-y-3">
                    @php
                        $totalRencanaAksi = count($rencanaAksi->rencana_aksi_list ?? []);
                        $totalRealisasi = $realisasiList->count();
                        $progressPercentage =
                            $totalRencanaAksi > 0 ? round(($totalRealisasi / $totalRencanaAksi) * 100, 1) : 0;

                        if ($totalRealisasi == 0) {
                            $status = 'Belum Terlaksana';
                            $statusColor = 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200';
                        } elseif ($totalRealisasi >= $totalRencanaAksi) {
                            $status = 'Selesai';
                            $statusColor = 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200';
                        } else {
                            $status = 'Sebagian';
                            $statusColor = 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200';
                        }
                    @endphp

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Status Pelaksanaan</label>
                        <div class="mt-1">
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColor }}">
                                {{ $status }}
                            </span>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Progress</label>
                        <div class="mt-1">
                            <div class="flex items-center">
                                <div class="w-full bg-gray-200 rounded-full h-2 dark:bg-gray-700 mr-3">
                                    <div class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                                        style="width: {{ min($progressPercentage, 100) }}%"></div>
                                </div>
                                <span
                                    class="text-sm font-medium text-gray-900 dark:text-white">{{ $progressPercentage }}%</span>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                {{ $totalRealisasi }} dari {{ $totalRencanaAksi }} rencana aksi terealisasi
                            </p>
                        </div>
                    </div>

                    <div>
                        <label class="text-sm font-medium text-gray-600 dark:text-gray-400">Tahun</label>
                        <p class="text-sm text-gray-900 dark:text-white">{{ $rencanaAksi->tahun ?? 'N/A' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rencana Aksi List -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Rencana Aksi</h3>
        </div>
        <div class="p-6">
            @if (!empty($rencanaAksi->rencana_aksi_list))
                <div class="space-y-4">
                    @foreach ($rencanaAksi->rencana_aksi_list as $index => $aksi)
                        @php
                            $aksiRealisasi = $realisasiList->where('rencana_aksi_item', $index + 1)->first();
                            $isRealized = $aksiRealisasi !== null;
                        @endphp

                        <div
                            class="flex items-start space-x-4 p-4 rounded-lg border {{ $isRealized ? 'bg-green-50 border-green-200 dark:bg-green-900/20 dark:border-green-800' : 'bg-gray-50 border-gray-200 dark:bg-gray-900 dark:border-gray-700' }}">
                            <div class="flex-shrink-0 mt-1">
                                @if ($isRealized)
                                    <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center">
                                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7"></path>
                                        </svg>
                                    </div>
                                @else
                                    <div
                                        class="w-6 h-6 bg-gray-300 dark:bg-gray-600 rounded-full flex items-center justify-center">
                                        <span
                                            class="text-xs font-medium text-gray-600 dark:text-gray-400">{{ $index + 1 }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            {{ $aksi['aksi'] ?? 'N/A' }}
                                        </p>

                                        <div
                                            class="mt-2 grid grid-cols-1 md:grid-cols-3 gap-4 text-xs text-gray-600 dark:text-gray-400">
                                            @if (isset($aksi['target_waktu']))
                                                <div>
                                                    <span class="font-medium">Target Waktu:</span>
                                                    <span>{{ $aksi['target_waktu'] }}</span>
                                                </div>
                                            @endif

                                            @if (isset($aksi['penanggung_jawab']))
                                                <div>
                                                    <span class="font-medium">Penanggung Jawab:</span>
                                                    <span>{{ $aksi['penanggung_jawab'] }}</span>
                                                </div>
                                            @endif

                                            @if (isset($aksi['indikator']))
                                                <div>
                                                    <span class="font-medium">Indikator:</span>
                                                    <span>{{ $aksi['indikator'] }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="ml-4 flex-shrink-0">
                                        @if ($isRealized)
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                Terealisasi
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200">
                                                Belum Terealisasi
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400">Tidak ada rencana aksi yang tersedia</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Realisasi Details -->
    <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Detail Realisasi</h3>
                <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                    {{ $realisasiList->count() }} Realisasi
                </span>
            </div>
        </div>

        <div class="p-6">
            @if ($realisasiList->count() > 0)
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 dark:bg-blue-900 rounded-lg">
                                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Peserta</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    {{ number_format($realisasiList->sum('jumlah_peserta')) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 dark:bg-green-900 rounded-lg">
                                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Total Anggaran</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">Rp
                                    {{ number_format($realisasiList->sum('realisasi_anggaran'), 0, ',', '.') }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-purple-50 dark:bg-purple-900/20 rounded-lg p-4">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 dark:bg-purple-900 rounded-lg">
                                <svg class="w-5 h-5 text-purple-600 dark:text-purple-400" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                    </path>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-600 dark:text-gray-400">Periode</p>
                                <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                    @if ($realisasiList->count() > 0)
                                        {{ $realisasiList->min('tanggal') ? \Carbon\Carbon::parse($realisasiList->min('tanggal'))->format('M Y') : 'N/A' }}
                                        -
                                        {{ $realisasiList->max('tanggal') ? \Carbon\Carbon::parse($realisasiList->max('tanggal'))->format('M Y') : 'N/A' }}
                                    @else
                                        N/A
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Realisasi List -->
                <div class="space-y-4">
                    @foreach ($realisasiList as $realisasi)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <div class="flex items-center space-x-2 mb-2">
                                        <h4 class="font-medium text-gray-900 dark:text-white">
                                            {{ $realisasi->nama_kegiatan ?? 'Kegiatan Realisasi' }}</h4>
                                        <span
                                            class="inline-flex items-center px-2 py-1 rounded-md text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                            {{ $realisasi->tanggal ? \Carbon\Carbon::parse($realisasi->tanggal)->format('d M Y') : 'N/A' }}
                                        </span>
                                    </div>

                                    @if ($realisasi->deskripsi)
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">
                                            {{ $realisasi->deskripsi }}</p>
                                    @endif

                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Lokasi:</span>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $realisasi->lokasi ?? 'N/A' }}</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Peserta:</span>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ number_format($realisasi->jumlah_peserta ?? 0) }} orang</p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">Anggaran:</span>
                                            <p class="text-gray-600 dark:text-gray-400">Rp
                                                {{ number_format($realisasi->realisasi_anggaran ?? 0, 0, ',', '.') }}
                                            </p>
                                        </div>
                                        <div>
                                            <span class="font-medium text-gray-700 dark:text-gray-300">PIC:</span>
                                            <p class="text-gray-600 dark:text-gray-400">
                                                {{ $realisasi->penanggung_jawab ?? 'N/A' }}</p>
                                        </div>
                                    </div>

                                    @if ($realisasi->dokumentasi || $realisasi->catatan)
                                        <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
                                            @if ($realisasi->catatan)
                                                <div class="mb-2">
                                                    <span
                                                        class="font-medium text-gray-700 dark:text-gray-300 text-sm">Catatan:</span>
                                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                                        {{ $realisasi->catatan }}</p>
                                                </div>
                                            @endif

                                            @if ($realisasi->dokumentasi)
                                                <div>
                                                    <span
                                                        class="font-medium text-gray-700 dark:text-gray-300 text-sm">Dokumentasi:</span>
                                                    <a href="{{ $realisasi->dokumentasi }}" target="_blank"
                                                        class="text-blue-600 dark:text-blue-400 hover:underline text-sm">
                                                        Lihat Dokumentasi
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-400 dark:text-gray-600 mx-auto mb-4" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-gray-400 mb-2">Belum ada realisasi yang tercatat</p>
                    <p class="text-sm text-gray-400 dark:text-gray-500">Tambahkan realisasi untuk rencana aksi ini</p>
                </div>
            @endif
        </div>
    </div>
</div>
