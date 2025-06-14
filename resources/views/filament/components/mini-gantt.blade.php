@php
    $record = $getRecord();
    $rencana = $record->rencana_pelaksanaan ?? [];
    $bulanAktif = collect($rencana)->filter(fn($value) => $value === true)->keys();
    $totalBulan = $bulanAktif->count();

    $bulanIndonesia = [
        'januari' => 'Jan',
        'februari' => 'Feb',
        'maret' => 'Mar',
        'april' => 'Apr',
        'mei' => 'Mei',
        'juni' => 'Jun',
        'juli' => 'Jul',
        'agustus' => 'Agu',
        'september' => 'Sep',
        'oktober' => 'Okt',
        'november' => 'Nov',
        'desember' => 'Des',
    ];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
    <div class="flex items-center justify-between mb-3">
        <h4 class="text-sm font-medium text-gray-900 dark:text-white flex items-center">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                </path>
            </svg>
            Timeline Pelaksanaan
        </h4>
        <span
            class="text-xs bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 px-2 py-1 rounded-full">
            {{ $totalBulan }}/12 bulan
        </span>
    </div>

    {{-- Mini Timeline Bar --}}
    <div class="space-y-3">
        <div class="flex items-center justify-center space-x-1">
            @foreach (['januari', 'februari', 'maret', 'april', 'mei', 'juni', 'juli', 'agustus', 'september', 'oktober', 'november', 'desember'] as $index => $bulan)
                <div class="flex flex-col items-center">
                    <div class="w-6 h-6 rounded-sm {{ isset($rencana[$bulan]) && $rencana[$bulan]
                        ? 'bg-primary-500 dark:bg-primary-400 shadow-sm'
                        : 'bg-gray-200 dark:bg-gray-600' }}"
                        title="{{ ucfirst($bulan) }} {{ isset($rencana[$bulan]) && $rencana[$bulan] ? '(Terjadwal)' : '(Tidak Terjadwal)' }}">
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ $bulanIndonesia[$bulan] }}
                    </span>
                </div>
            @endforeach
        </div>

        {{-- Progress Bar --}}
        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
            <div class="bg-primary-500 dark:bg-primary-400 h-2 rounded-full transition-all duration-300"
                style="width: {{ ($totalBulan / 12) * 100 }}%"></div>
        </div>

        {{-- Summary Info --}}
        <div class="flex justify-between items-center text-xs">
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 rounded-sm bg-primary-500 dark:bg-primary-400"></div>
                    <span class="text-gray-600 dark:text-gray-400">Terjadwal</span>
                </div>
                <div class="flex items-center space-x-1">
                    <div class="w-3 h-3 rounded-sm bg-gray-200 dark:bg-gray-600"></div>
                    <span class="text-gray-600 dark:text-gray-400">Tidak Terjadwal</span>
                </div>
            </div>
            <span class="text-gray-500 dark:text-gray-400 font-medium">
                {{ round(($totalBulan / 12) * 100) }}% dari tahun
            </span>
        </div>

        {{-- Active Months List --}}
        @if ($totalBulan > 0)
            <div class="mt-3 p-3 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    Bulan Pelaksanaan:
                </p>
                <div class="flex flex-wrap gap-1">
                    @foreach ($bulanAktif as $bulan)
                        <span
                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">
                            {{ ucfirst($bulan) }}
                        </span>
                    @endforeach
                </div>
            </div>
        @else
            <div
                class="mt-3 p-3 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                <p class="text-xs text-yellow-700 dark:text-yellow-300 text-center">
                    ⚠️ Belum ada bulan yang dijadwalkan untuk pelaksanaan
                </p>
            </div>
        @endif
    </div>
</div>
