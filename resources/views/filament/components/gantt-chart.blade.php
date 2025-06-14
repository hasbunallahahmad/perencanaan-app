@php
    $bulanNames = [
        '01' => 'Jan',
        '1' => 'Jan',
        '02' => 'Feb',
        '2' => 'Feb',
        '03' => 'Mar',
        '3' => 'Mar',
        '04' => 'Apr',
        '4' => 'Apr',
        '05' => 'Mei',
        '5' => 'Mei',
        '06' => 'Jun',
        '6' => 'Jun',
        '07' => 'Jul',
        '7' => 'Jul',
        '08' => 'Ags',
        '8' => 'Ags',
        '09' => 'Sep',
        '9' => 'Sep',
        '10' => 'Okt',
        '11' => 'Nov',
        '12' => 'Des',
    ];

    // Ambil data rencana pelaksanaan dari record
    $rencanaPelaksanaan = $rencana_pelaksanaan ?? [];
    $rencanaAksiList = $rencana_aksi_list ?? [];

    // Gabungkan data bulan dari berbagai sumber
    $activeBulan = [];

    // Dari rencana_pelaksanaan (prioritas utama)
    if (!empty($rencanaPelaksanaan) && is_array($rencanaPelaksanaan)) {
        foreach ($rencanaPelaksanaan as $item) {
            if (is_array($item) && isset($item['bulan'])) {
                $activeBulan[] = str_pad($item['bulan'], 2, '0', STR_PAD_LEFT);
            } elseif (is_string($item) || is_numeric($item)) {
                $activeBulan[] = str_pad($item, 2, '0', STR_PAD_LEFT);
            }
        }
    }

    // Jika tidak ada data dari rencana_pelaksanaan, ambil dari rencana_aksi_list
    if (empty($activeBulan) && !empty($rencanaAksiList) && is_array($rencanaAksiList)) {
        foreach ($rencanaAksiList as $aksi) {
            if (isset($aksi['bulan']) && is_array($aksi['bulan'])) {
                foreach ($aksi['bulan'] as $bulan) {
                    $activeBulan[] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
                }
            }
        }
    }

    // Hapus duplikat dan sort
    $activeBulan = array_unique($activeBulan);
    sort($activeBulan);

    // Hitung total bulan aktif
    $totalActiveBulan = count($activeBulan);

    // Buat array untuk semua rencana aksi
    $allRencanaAksi = [];
    if (!empty($rencanaAksiList) && is_array($rencanaAksiList)) {
        foreach ($rencanaAksiList as $index => $aksi) {
            $aksiName = $aksi['aksi'] ?? 'Rencana Aksi ' . ($index + 1);

            // Bulan untuk aksi ini
            $aksiBulan = [];
            if (isset($aksi['bulan']) && is_array($aksi['bulan'])) {
                foreach ($aksi['bulan'] as $bulan) {
                    $aksiBulan[] = str_pad($bulan, 2, '0', STR_PAD_LEFT);
                }
            } elseif (!empty($activeBulan)) {
                // Jika tidak ada bulan spesifik, gunakan semua bulan aktif
                $aksiBulan = $activeBulan;
            }

            $allRencanaAksi[] = [
                'nama' => $aksiName,
                'bulan' => $aksiBulan,
            ];
        }
    } else {
        // Jika tidak ada rencana aksi spesifik, buat default
        $allRencanaAksi[] = [
            'nama' => 'Rencana Aksi 1',
            'bulan' => $activeBulan,
        ];
    }
@endphp

<div class="bg-white border border-gray-200 rounded-lg shadow-sm">
    <!-- Header -->
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">
                Rencana Pelaksanaan (12 Bulan)
            </h3>
            <div class="text-sm text-gray-500">
                {{ $totalActiveBulan }}/12 bulan
            </div>
        </div>
    </div>

    <!-- Gantt Chart -->
    <div class="p-6">
        <div class="overflow-x-auto">
            <table class="w-full border-collapse">
                <!-- Header Bulan -->
                <thead>
                    <tr>
                        <th
                            class="text-left py-3 px-4 font-medium text-gray-700 bg-gray-50 border border-gray-200 min-w-[200px]">
                            Rencana Aksi
                        </th>
                        @foreach (['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'] as $bulan)
                            <th
                                class="text-center py-3 px-2 font-medium text-gray-700 bg-gray-50 border border-gray-200 min-w-[60px]">
                                {{ $bulanNames[$bulan] }}
                            </th>
                        @endforeach
                    </tr>
                </thead>

                <!-- Body -->
                <tbody>
                    @forelse($allRencanaAksi as $aksi)
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4 border border-gray-200 font-medium text-gray-900">
                                {{ $aksi['nama'] }}
                            </td>
                            @foreach (['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'] as $bulan)
                                <td class="py-3 px-2 border border-gray-200 text-center">
                                    @if (in_array($bulan, $aksi['bulan']))
                                        <div
                                            class="w-8 h-8 mx-auto bg-white rounded border-2 border-gray-300 flex items-center justify-center">
                                            <svg class="w-4 h-4 text-gray-800" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-8 h-8 mx-auto bg-gray-200 rounded border-2 border-gray-300"></div>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr>
                            <td class="py-8 px-4 border border-gray-200 text-center text-gray-500" colspan="13">
                                Belum ada rencana aksi yang dijadwalkan
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Legend -->
        {{-- <div class="mt-6 flex items-center space-x-6">
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-orange-500 rounded border border-orange-600"></div>
                <span class="text-sm text-gray-600"> &nbsp; Bulan Terjadwal</span>
            </div>
            &nbsp;&nbsp;&nbsp;
            <div class="flex items-center space-x-2">
                <div class="w-4 h-4 bg-gray-200 rounded border border-gray-300"></div>
                <span class="text-sm text-gray-600">&nbsp; Bulan Tidak Terjadwal</span>
            </div>
        </div> --}}
        &nbsp;
        @if ($totalActiveBulan > 0)
            <!-- Status Info -->
            <div class="mt-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
                <div class="flex items-start space-x-3">
                    <svg class="w-5 h-5 text-blue-500 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Bulan aktif:</p>
                        <p class="text-sm text-blue-700 mt-1">
                            @foreach ($activeBulan as $bulan)
                                <span
                                    class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1 mb-1">
                                    {{ $bulanNames[$bulan] }}
                                </span>
                            @endforeach
                        </p>
                    </div>
                </div>
            </div>
        @else
            <!-- No Schedule Warning -->
            <div class="mt-4 p-4 bg-amber-50 rounded-lg border border-amber-200">
                <div class="flex items-center space-x-3">
                    <svg class="w-5 h-5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                    <p class="text-sm text-amber-800">
                        Belum ada bulan yang dijadwalkan untuk pelaksanaan
                    </p>
                </div>
            </div>
        @endif
    </div>
</div>
