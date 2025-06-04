<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Form Input --}}
        <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Input Data Capaian Kinerja</h3>

            <form wire:submit="save">
                {{ $this->form }}

                <div class="mt-6 flex justify-end space-x-3">
                    <x-filament::button type="button" color="gray" wire:click="$set('data', [])">
                        Reset
                    </x-filament::button>

                    {{-- <x-filament::button type="submit">
                        <x-heroicon-o-plus class="w-4 h-4 mr-2" />
                        Simpan Data
                    </x-filament::button> --}}
                </div>
            </form>
        </div>

        {{-- Tabel Data --}}
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Data Capaian Kinerja</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Daftar data capaian kinerja yang telah diinput. Anda dapat mengedit atau menghapus data yang sudah
                    ada.
                </p>
            </div>

            <div class="overflow-x-auto">
                {{ $this->table }}
            </div>
        </div>
    </div>

    <style>
        /* Custom styling untuk tabel agar lebih mirip dengan Google Sheets */
        .fi-ta-table {
            border-collapse: collapse;
        }

        .fi-ta-header-cell,
        .fi-ta-cell {
            border: 1px solid #e5e7eb;
            padding: 8px 12px;
        }

        .fi-ta-header-cell {
            background-color: #f9fafb;
            font-weight: 600;
            text-align: center;
        }

        .fi-ta-cell {
            text-align: center;
        }

        /* Highlight untuk kolom total dan persentase */
        .fi-ta-cell:nth-last-child(3),
        .fi-ta-cell:nth-last-child(4) {
            background-color: #fef3c7;
            font-weight: 600;
        }

        /* Warna untuk persentase */
        .text-success-600 {
            color: #16a34a !important;
        }

        .text-warning-600 {
            color: #ca8a04 !important;
        }

        .text-danger-600 {
            color: #dc2626 !important;
        }

        /* Responsive table */
        @media (max-width: 768px) {
            .fi-ta-table {
                font-size: 0.875rem;
            }

            .fi-ta-cell,
            .fi-ta-header-cell {
                padding: 6px 8px;
            }
        }
    </style>

    {{-- JavaScript untuk perhitungan otomatis --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Auto-calculate saat form input berubah
            const twInputs = ['tw1', 'tw2', 'tw3', 'tw4'];

            twInputs.forEach(function(inputName) {
                const input = document.querySelector(`[name="data.${inputName}"]`);
                if (input) {
                    input.addEventListener('input', function() {
                        calculateTotals();
                    });
                }
            });

            const targetInput = document.querySelector('[name="data.target_nilai"]');
            if (targetInput) {
                targetInput.addEventListener('input', function() {
                    calculateTotals();
                });
            }

            function calculateTotals() {
                const tw1 = parseFloat(document.querySelector('[name="data.tw1"]')?.value || 0);
                const tw2 = parseFloat(document.querySelector('[name="data.tw2"]')?.value || 0);
                const tw3 = parseFloat(document.querySelector('[name="data.tw3"]')?.value || 0);
                const tw4 = parseFloat(document.querySelector('[name="data.tw4"]')?.value || 0);
                const target = parseFloat(document.querySelector('[name="data.target_nilai"]')?.value || 0);

                const total = tw1 + tw2 + tw3 + tw4;
                const percentage = target > 0 ? (total / target) * 100 : 0;

                const totalInput = document.querySelector('[name="data.total"]');
                const percentageInput = document.querySelector('[name="data.persentase"]');

                if (totalInput) totalInput.value = total.toFixed(2);
                if (percentageInput) percentageInput.value = percentage.toFixed(2);
            }
        });
    </script>
</x-filament-panels::page>
