{{-- resources/views/filament/widgets/year-selector.blade.php --}}
<x-filament-widgets::widget>
    <x-filament::section>
        <x-slot name="heading">
            Pilih Tahun Anggaran
        </x-slot>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Year Selector --}}
            <div class="space-y-2">
                <x-filament::input.wrapper>
                    <x-filament::input.select wire:model.live="selectedYear">
                        @foreach ($this->getAvailableYears() as $year)
                            <option value="{{ $year }}">
                                {{ $year }}
                                @if ($this->hasDataForYear($year))
                                    ✓
                                @else
                                    (kosong)
                                @endif
                            </option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <p class="text-sm text-gray-500">
                    Tahun aktif: <strong>{{ $selectedYear }}</strong>
                    @if ($this->hasDataForYear($selectedYear))
                        <span class="text-green-600">(memiliki data)</span>
                    @else
                        <span class="text-orange-600">(belum ada data)</span>
                    @endif
                </p>
            </div>

            {{-- Info Panel --}}
            <div class="md:col-span-2 space-y-2">
                <div class="bg-blue-50 dark:bg-blue-950/50 p-3 rounded-lg">
                    <h4 class="font-medium text-blue-900 dark:text-blue-100">
                        Informasi Tahun
                    </h4>
                    <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                        @if ($this->hasDataForYear($selectedYear))
                            Tahun {{ $selectedYear }} sudah memiliki data.
                            Anda dapat melihat dan mengedit data yang ada.
                        @else
                            Tahun {{ $selectedYear }} belum memiliki data.
                            Mulai dengan menambahkan data baru menggunakan menu navigasi.
                        @endif
                    </p>

                    @if (!empty($this->getYearsWithData()))
                        <p class="text-xs text-blue-600 dark:text-blue-400 mt-2">
                            Tahun dengan data:
                            @foreach ($this->getYearsWithData() as $year)
                                <span class="inline-block bg-blue-100 dark:bg-blue-900 px-2 py-1 rounded text-xs mr-1">
                                    {{ $year }}
                                </span>
                            @endforeach
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
