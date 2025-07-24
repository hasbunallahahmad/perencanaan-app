<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\MasterTujuanSasaran;
use App\Models\MasterSasaran;
use App\Models\RealisasiTujuanSasaran;
use Illuminate\Support\Facades\Auth;

class RealisasiTable extends Component
{
    // Properties untuk data
    public $realisasiData = [
        'tujuan' => [],
        'sasaran' => []
    ];

    // Data yang sudah tersimpan di database
    public $savedRealisasiData = [
        'tujuan' => [],
        'sasaran' => []
    ];

    // Property untuk tahun aktif
    public $currentYear;

    // Track changes untuk UI feedback
    public bool $hasChanges = false;
    public array $changedItems = [];

    protected $listeners = [
        'refreshData' => 'loadSavedData',
        'yearChanged' => 'handleYearChange',
        'saveTriggered' => 'saveAllData'
    ];

    public function mount($currentYear = null)
    {
        $this->currentYear = $currentYear ?? date('Y');
        $this->loadSavedData();
        $this->initializeRealisasiData();
    }

    /**
     * Handle year change dari parent page
     */
    public function handleYearChange($data)
    {
        $this->changeYear($data['year']);
    }

    /**
     * Load data yang sudah tersimpan dari database
     */
    public function loadSavedData()
    {
        // Reset data arrays
        $this->savedRealisasiData = ['tujuan' => [], 'sasaran' => []];

        // Load data realisasi tujuan yang sudah tersimpan
        $savedTujuanData = RealisasiTujuanSasaran::whereNotNull('master_tujuan_sasaran_id')
            ->whereNull('master_sasaran_id')
            ->where('tahun', $this->currentYear)
            ->get();

        foreach ($savedTujuanData as $data) {
            $this->savedRealisasiData['tujuan'][$data->master_tujuan_sasaran_id] = [
                'target_tahun' => $data->target_tahun,
                'tw1' => $data->realisasi_tw1,
                'tw2' => $data->realisasi_tw2,
                'tw3' => $data->realisasi_tw3,
                'tw4' => $data->realisasi_tw4,
            ];
        }

        // Load data realisasi sasaran yang sudah tersimpan
        $savedSasaranData = RealisasiTujuanSasaran::whereNotNull('master_sasaran_id')
            ->whereNull('master_tujuan_sasaran_id')
            ->where('tahun', $this->currentYear)
            ->get();

        foreach ($savedSasaranData as $data) {
            $this->savedRealisasiData['sasaran'][$data->master_sasaran_id] = [
                'target_tahun' => $data->target_tahun,
                'tw1' => $data->realisasi_tw1,
                'tw2' => $data->realisasi_tw2,
                'tw3' => $data->realisasi_tw3,
                'tw4' => $data->realisasi_tw4,
            ];
        }
    }

    /**
     * Initialize realisasi data dengan data yang sudah tersimpan
     */
    public function initializeRealisasiData()
    {
        foreach ($this->savedRealisasiData['tujuan'] as $id => $data) {
            $this->realisasiData['tujuan'][$id] = $data;
        }

        foreach ($this->savedRealisasiData['sasaran'] as $id => $data) {
            $this->realisasiData['sasaran'][$id] = $data;
        }

        // Reset change tracking
        $this->hasChanges = false;
        $this->changedItems = [];

        // Emit ke parent page
        $this->dispatch('dataChanged', ['hasChanges' => $this->hasChanges]);
    }

    /**
     * Track perubahan data untuk UI feedback
     */
    public function updated($propertyName)
    {
        if (str_contains($propertyName, 'realisasiData')) {
            $this->hasChanges = true;

            // Extract type and item ID from property name
            // realisasiData.tujuan.1.target_tahun -> tujuan_1
            preg_match('/realisasiData\.(tujuan|sasaran)\.(\d+)\./', $propertyName, $matches);
            if (count($matches) >= 3) {
                $key = $matches[1] . '_' . $matches[2];
                $this->changedItems[$key] = true;
            }

            // Emit ke parent page
            $this->dispatch('dataChanged', ['hasChanges' => $this->hasChanges]);
        }
    }

    /**
     * Check if there are any changes in the form
     */
    public function getHasChangesProperty()
    {
        return $this->hasChanges;
    }

    /**
     * Check if specific item has unsaved changes
     */
    public function hasUnsavedChanges($type, $itemId)
    {
        $key = $type . '_' . $itemId;
        return isset($this->changedItems[$key]);
    }

    /**
     * Simpan semua data sekaligus - METHOD UTAMA
     */
    public function saveAllData()
    {
        try {
            $savedCount = 0;
            $errors = [];

            // Validate all data first
            $this->validateAllData();

            // Save all tujuan data
            foreach ($this->realisasiData['tujuan'] as $itemId => $data) {
                if (!empty(array_filter($data, function ($value) {
                    return !is_null($value) && $value !== '';
                }))) {
                    try {
                        $this->saveRealisasi('tujuan', $itemId);
                        $savedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Tujuan ID {$itemId}: " . $e->getMessage();
                    }
                }
            }

            // Save all sasaran data
            foreach ($this->realisasiData['sasaran'] as $itemId => $data) {
                if (!empty(array_filter($data, function ($value) {
                    return !is_null($value) && $value !== '';
                }))) {
                    try {
                        $this->saveRealisasi('sasaran', $itemId);
                        $savedCount++;
                    } catch (\Exception $e) {
                        $errors[] = "Sasaran ID {$itemId}: " . $e->getMessage();
                    }
                }
            }

            if (!empty($errors)) {
                $this->dispatch('dataSaved', [
                    'success' => false,
                    'message' => 'Sebagian data berhasil disimpan (' . $savedCount . '), namun ada error: ' . implode('; ', $errors)
                ]);
                session()->flash('warning', 'Sebagian data berhasil disimpan (' . $savedCount . '), namun ada error: ' . implode('; ', $errors));
            } else {
                $this->dispatch('dataSaved', [
                    'success' => true,
                    'count' => $savedCount,
                    'message' => "Berhasil menyimpan {$savedCount} data realisasi!"
                ]);
                session()->flash('message', "Berhasil menyimpan {$savedCount} data realisasi!");
            }

            // Reload data after save
            $this->loadSavedData();
            $this->initializeRealisasiData();

            // Emit event untuk notifikasi
            $this->dispatch('data-saved', ['count' => $savedCount]);
        } catch (\Exception $e) {
            $this->dispatch('dataSaved', [
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ]);
            session()->flash('error', 'Gagal menyimpan data: ' . $e->getMessage());
            $this->dispatch('save-error', ['message' => $e->getMessage()]);
        }
    }

    /**
     * Validasi semua data sebelum disimpan
     */
    private function validateAllData()
    {
        foreach ($this->realisasiData['tujuan'] as $itemId => $data) {
            if (!empty(array_filter($data))) {
                $this->validateRealisasiData('tujuan', $itemId);
            }
        }

        foreach ($this->realisasiData['sasaran'] as $itemId => $data) {
            if (!empty(array_filter($data))) {
                $this->validateRealisasiData('sasaran', $itemId);
            }
        }
    }

    /**
     * Simpan data realisasi individual
     */
    private function saveRealisasi($type, $itemId)
    {
        // Ambil data dari form
        $dataToSave = $this->realisasiData[$type][$itemId] ?? [];

        // Skip jika semua data kosong
        if (empty(array_filter($dataToSave, function ($value) {
            return !is_null($value) && $value !== '';
        }))) {
            return;
        }

        // Tentukan field yang akan diisi berdasarkan type
        $saveData = [
            'tahun' => $this->currentYear,
            'target_tahun' => $dataToSave['target_tahun'] ?? null,
            'realisasi_tw1' => $dataToSave['tw1'] ?? null,
            'realisasi_tw2' => $dataToSave['tw2'] ?? null,
            'realisasi_tw3' => $dataToSave['tw3'] ?? null,
            'realisasi_tw4' => $dataToSave['tw4'] ?? null,
            'updated_by' => Auth::id()
        ];

        // Set field yang sesuai berdasarkan type
        if ($type === 'tujuan') {
            $saveData['master_tujuan_sasaran_id'] = $itemId;
            $saveData['master_sasaran_id'] = null;
            $whereCondition = [
                'master_tujuan_sasaran_id' => $itemId,
                'tahun' => $this->currentYear
            ];
        } else {
            $saveData['master_sasaran_id'] = $itemId;
            $saveData['master_tujuan_sasaran_id'] = null;
            $whereCondition = [
                'master_sasaran_id' => $itemId,
                'tahun' => $this->currentYear
            ];
        }

        // Jika ini adalah record baru, tambahkan created_by
        $existingRecord = RealisasiTujuanSasaran::where($whereCondition)->first();
        if (!$existingRecord) {
            $saveData['created_by'] = Auth::id();
        }

        // Simpan atau update ke database
        RealisasiTujuanSasaran::updateOrCreate($whereCondition, $saveData);

        // Update saved data
        $this->savedRealisasiData[$type][$itemId] = [
            'target_tahun' => $dataToSave['target_tahun'] ?? null,
            'tw1' => $dataToSave['tw1'] ?? null,
            'tw2' => $dataToSave['tw2'] ?? null,
            'tw3' => $dataToSave['tw3'] ?? null,
            'tw4' => $dataToSave['tw4'] ?? null,
        ];
    }

    /**
     * Validasi data realisasi
     */
    private function validateRealisasiData($type, $itemId)
    {
        $rules = [
            "realisasiData.{$type}.{$itemId}.target_tahun" => 'nullable|numeric|min:0',
            "realisasiData.{$type}.{$itemId}.tw1" => 'nullable|numeric|min:0',
            "realisasiData.{$type}.{$itemId}.tw2" => 'nullable|numeric|min:0',
            "realisasiData.{$type}.{$itemId}.tw3" => 'nullable|numeric|min:0',
            "realisasiData.{$type}.{$itemId}.tw4" => 'nullable|numeric|min:0',
        ];

        $messages = [
            "realisasiData.{$type}.{$itemId}.*.numeric" => 'Data harus berupa angka',
            "realisasiData.{$type}.{$itemId}.*.min" => 'Data tidak boleh negatif',
        ];

        $this->validate($rules, $messages);

        // Custom validation: TW tidak boleh melebihi target
        $data = $this->realisasiData[$type][$itemId] ?? [];
        $targetTahun = $data['target_tahun'] ?? 0;

        if ($targetTahun > 0) {
            foreach (['tw1', 'tw2', 'tw3', 'tw4'] as $tw) {
                if (!empty($data[$tw]) && $data[$tw] > $targetTahun) {
                    throw new \Exception("Realisasi {$tw} tidak boleh melebihi target tahun pada " . ucfirst($type) . " ID: {$itemId}");
                }
            }
        }
    }

    /**
     * Reset form ke data terakhir yang tersimpan
     */
    public function resetForm()
    {
        $this->initializeRealisasiData();
        session()->flash('info', 'Form berhasil direset ke data terakhir yang tersimpan.');
    }

    /**
     * Ganti tahun dan reload data
     */
    public function changeYear($year)
    {
        $this->currentYear = $year;

        // Reset arrays
        $this->savedRealisasiData = ['tujuan' => [], 'sasaran' => []];
        $this->realisasiData = ['tujuan' => [], 'sasaran' => []];
        $this->hasChanges = false;
        $this->changedItems = [];

        // Load data untuk tahun yang baru
        $this->loadSavedData();
        $this->initializeRealisasiData();

        session()->flash('info', 'Data tahun ' . $year . ' berhasil dimuat');
    }

    /**
     * Check if item has any saved data
     */
    public function hasAnySavedData($type, $itemId)
    {
        $savedData = $this->savedRealisasiData[$type][$itemId] ?? [];
        return !empty(array_filter($savedData, function ($value) {
            return !is_null($value) && $value !== '';
        }));
    }

    /**
     * Get achievement percentage for specific item
     */
    public function getAchievementPercentage($type, $itemId)
    {
        $currentData = $this->realisasiData[$type][$itemId] ?? [];
        $targetTahun = $currentData['target_tahun'] ?? 0;

        if ($targetTahun <= 0) return 0;

        $totalRealisasi = ($currentData['tw1'] ?? 0) +
            ($currentData['tw2'] ?? 0) +
            ($currentData['tw3'] ?? 0) +
            ($currentData['tw4'] ?? 0);

        return round(($totalRealisasi / $targetTahun) * 100, 2);
    }

    /**
     * Get total items with data
     */
    public function getTotalItemsWithData()
    {
        $tujuanCount = 0;
        $sasaranCount = 0;

        foreach ($this->realisasiData['tujuan'] as $data) {
            if (!empty(array_filter($data))) $tujuanCount++;
        }

        foreach ($this->realisasiData['sasaran'] as $data) {
            if (!empty(array_filter($data))) $sasaranCount++;
        }

        return ['tujuan' => $tujuanCount, 'sasaran' => $sasaranCount];
    }

    /**
     * Render component
     */
    public function render()
    {
        $tujuans = MasterTujuanSasaran::where('is_active', true)->get();
        $sasarans = MasterSasaran::where('is_active', true)->get();

        return view('filament.components.realisasi-table', [
            'tujuans' => $tujuans,
            'sasarans' => $sasarans,
            'currentYear' => $this->currentYear
        ]);
    }
}
