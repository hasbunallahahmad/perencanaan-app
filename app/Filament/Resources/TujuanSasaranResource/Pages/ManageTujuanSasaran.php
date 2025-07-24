<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use App\Models\Tujas;
use App\Models\MasterTujuanSasaran;
use App\Models\MasterSasaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Concerns\InteractsWithFormActions;
use Filament\Resources\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Support\Exceptions\Halt;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Filament\Forms\Components\Livewire;
use Livewire\Component;

class ManageTujuanSasaran extends Page implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;
    use InteractsWithFormActions;

    protected static string $resource = TujuanSasaranResource::class;
    protected static string $view = 'filament.resources.tujuan-sasaran-resource.pages.manage-tujuan-sasaran';

    public ?array $data = [];
    public ?int $selectedTahun = null;
    public Collection $tujuanSasaranData;

    public function mount(): void
    {
        $this->selectedTahun = date('Y');
        $this->loadTujuanSasaranData();
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('tahun')
                    ->label('Pilih Tahun')
                    ->options(array_combine(
                        range(date('Y') - 5, date('Y') + 5),
                        range(date('Y') - 5, date('Y') + 5)
                    ))
                    ->default(date('Y'))
                    ->live()
                    ->afterStateUpdated(function ($state) {
                        $this->selectedTahun = $state;
                        $this->loadTujuanSasaranData();
                    }),
            ])
            ->statePath('data')
            ->columns(1);
    }

    public function loadTujuanSasaranData(): void
    {
        // Get all active master tujuan dan sasaran
        $masterTujuan = MasterTujuanSasaran::where('is_active', true)->get();
        $masterSasaran = MasterSasaran::where('is_active', true)->get();

        // Get existing data for selected year
        $existingData = Tujas::where('tahun', $this->selectedTahun)->get()->keyBy(function ($item) {
            return $item->master_tujuan_sasaran_id . '_' . $item->master_sasaran_id;
        });

        $this->tujuanSasaranData = collect();

        foreach ($masterTujuan as $tujuan) {
            foreach ($masterSasaran as $sasaran) {
                $key = $tujuan->id . '_' . $sasaran->id;
                $existing = $existingData->get($key);

                $this->tujuanSasaranData->push([
                    'id' => $existing?->id,
                    'master_tujuan_sasaran_id' => $tujuan->id,
                    'master_sasaran_id' => $sasaran->id,
                    'tujuan' => $tujuan->tujuan,
                    'sasaran' => $sasaran->sasaran,
                    'indikator' => $existing?->indikator ?? '',
                    'target' => $existing?->target ?? 0,
                    'satuan' => $existing?->satuan ?? '%',
                    'realisasi_tw_1' => $existing?->realisasi_tw_1 ?? 0,
                    'realisasi_tw_2' => $existing?->realisasi_tw_2 ?? 0,
                    'realisasi_tw_3' => $existing?->realisasi_tw_3 ?? 0,
                    'realisasi_tw_4' => $existing?->realisasi_tw_4 ?? 0,
                    'total_realisasi' => $existing?->total_realisasi ?? 0,
                    'persentase_calculated' => $existing?->persentase_calculated ?? 0,
                    'status_pencapaian' => $existing?->status_pencapaian ?? '-',
                    'is_existing' => $existing !== null,
                ]);
            }
        }
    }

    public function updateRealisasi($index, $field, $value): void
    {
        $item = $this->tujuanSasaranData->get($index);

        if (!$item) {
            return;
        }

        // Validate input
        $value = max(0, floatval($value));

        // Update collection
        $this->tujuanSasaranData[$index][$field] = $value;

        // Calculate totals
        $totalRealisasi =
            ($this->tujuanSasaranData[$index]['realisasi_tw_1'] ?? 0) +
            ($this->tujuanSasaranData[$index]['realisasi_tw_2'] ?? 0) +
            ($this->tujuanSasaranData[$index]['realisasi_tw_3'] ?? 0) +
            ($this->tujuanSasaranData[$index]['realisasi_tw_4'] ?? 0);

        $this->tujuanSasaranData[$index]['total_realisasi'] = $totalRealisasi;

        $target = $this->tujuanSasaranData[$index]['target'] ?? 1;
        $persentase = $target > 0 ? ($totalRealisasi / $target) * 100 : 0;
        $this->tujuanSasaranData[$index]['persentase_calculated'] = $persentase;

        // Update status
        if ($persentase >= 100) {
            $this->tujuanSasaranData[$index]['status_pencapaian'] = 'Tercapai';
        } elseif ($persentase >= 75) {
            $this->tujuanSasaranData[$index]['status_pencapaian'] = 'Baik';
        } elseif ($persentase >= 50) {
            $this->tujuanSasaranData[$index]['status_pencapaian'] = 'Cukup';
        } else {
            $this->tujuanSasaranData[$index]['status_pencapaian'] = 'Kurang';
        }

        // Auto-save if record exists
        $this->autoSaveRecord($index);
    }

    public function updateTarget($index, $target): void
    {
        $target = max(0, floatval($target));
        $this->tujuanSasaranData[$index]['target'] = $target;
        $this->updateRealisasi($index, 'target', $target);
    }

    public function updateIndikator($index, $indikator): void
    {
        $this->tujuanSasaranData[$index]['indikator'] = trim($indikator);
        $this->autoSaveRecord($index);
    }

    private function autoSaveRecord($index): void
    {
        $item = $this->tujuanSasaranData->get($index);

        if (!$item || empty($item['target']) || empty($item['indikator'])) {
            return;
        }

        try {
            $data = [
                'master_tujuan_sasaran_id' => $item['master_tujuan_sasaran_id'],
                'master_sasaran_id' => $item['master_sasaran_id'],
                'tujuan' => $item['tujuan'],
                'sasaran' => $item['sasaran'],
                'indikator' => $item['indikator'],
                'tahun' => $this->selectedTahun,
                'target' => $item['target'],
                'satuan' => $item['satuan'],
                'realisasi_tw_1' => $item['realisasi_tw_1'] ?? 0,
                'realisasi_tw_2' => $item['realisasi_tw_2'] ?? 0,
                'realisasi_tw_3' => $item['realisasi_tw_3'] ?? 0,
                'realisasi_tw_4' => $item['realisasi_tw_4'] ?? 0,
            ];

            if ($item['id']) {
                // Update existing
                Tujas::find($item['id'])->update($data);
            } else {
                // Create new
                $newRecord = Tujas::create($data);
                $this->tujuanSasaranData[$index]['id'] = $newRecord->id;
                $this->tujuanSasaranData[$index]['is_existing'] = true;
            }
        } catch (\Exception $e) {
            // Silent fail for auto-save, user can use manual save button
        }
    }

    public function saveAllData(): void
    {
        try {
            foreach ($this->tujuanSasaranData as $item) {
                // Skip if no target or indikator
                if (empty($item['target']) || empty($item['indikator'])) {
                    continue;
                }

                $data = [
                    'master_tujuan_sasaran_id' => $item['master_tujuan_sasaran_id'],
                    'master_sasaran_id' => $item['master_sasaran_id'],
                    'tujuan' => $item['tujuan'],
                    'sasaran' => $item['sasaran'],
                    'indikator' => $item['indikator'],
                    'tahun' => $this->selectedTahun,
                    'target' => $item['target'],
                    'satuan' => $item['satuan'],
                    'realisasi_tw_1' => $item['realisasi_tw_1'] ?? 0,
                    'realisasi_tw_2' => $item['realisasi_tw_2'] ?? 0,
                    'realisasi_tw_3' => $item['realisasi_tw_3'] ?? 0,
                    'realisasi_tw_4' => $item['realisasi_tw_4'] ?? 0,
                ];

                if ($item['id']) {
                    // Update existing
                    Tujas::find($item['id'])->update($data);
                } else {
                    // Create new
                    Tujas::create($data);
                }
            }

            Notification::make()
                ->title('Data berhasil disimpan')
                ->success()
                ->send();

            $this->loadTujuanSasaranData();
        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal menyimpan data')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Simpan Semua Data')
                ->color('success')
                ->icon('heroicon-o-check')
                ->action('saveAllData'),
        ];
    }

    public static function getNavigationLabel(): string
    {
        return 'Manage Tujuan & Sasaran';
    }
}
