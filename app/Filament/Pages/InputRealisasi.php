<?php

namespace App\Filament\Pages;

use App\Models\MasterSasaran;
use App\Models\MasterTujuanSasaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;

class InputRealisasi extends Page implements HasForms
{
    use InteractsWithForms;
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static string $view = 'filament.pages.input-realisasi';
    protected static ?string $navigationLabel = 'Input Realisasi';
    protected static ?string $title = 'Input Realisasi Tujuan & Sasaran';
    protected static ?string $navigationGroup = 'Realisasi';
    protected static ?int $navigationSort = 1;

    public ?array $data = [];
    public Collection $tujuanSasaranData;
    public $selectedTujuan = null;
    public $currentYear;

    // Listeners untuk menerima event dari RealisasiTable component
    protected $listeners = [
        'dataChanged' => 'handleDataChange',
        'dataSaved' => 'handleDataSaved'
    ];

    public function mount(): void
    {
        $this->currentYear = date('Y');
        $this->loadTujuanSasaranData();
        $this->form->fill([
            'tahun' => $this->currentYear
        ]);
    }

    protected function loadTujuanSasaranData(): void
    {
        // Ambil semua tujuan yang aktif
        $tujuans = MasterTujuanSasaran::where('is_active', true)->get();

        // Ambil semua sasaran yang aktif
        $sasarans = MasterSasaran::where('is_active', true)->get();

        // Gabungkan data untuk tampilan
        $this->tujuanSasaranData = collect([
            'tujuans' => $tujuans,
            'sasarans' => $sasarans
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Pilih Periode dan Tahun')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('tahun')
                                    ->label('Tahun')
                                    ->options(function () {
                                        $years = [];
                                        for ($i = date('Y') - 2; $i <= date('Y') + 2; $i++) {
                                            $years[$i] = $i;
                                        }
                                        return $years;
                                    })
                                    ->default(date('Y'))
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state) {
                                        $this->currentYear = $state;
                                        // Emit event ke RealisasiTable component untuk update tahun
                                        $this->dispatch('yearChanged', ['year' => $state]);
                                    }),
                            ]),
                    ]),

                // Section untuk component table - gunakan view component instead of placeholder
                Forms\Components\Section::make('Data Realisasi')
                    ->schema([
                        Forms\Components\ViewField::make('realisasi_component')
                            ->label('')
                            ->view('filament.components.realisasi-component-wrapper')
                            ->viewData([
                                'currentYear' => fn() => $this->currentYear,
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        // Emit event ke RealisasiTable component untuk save
        $this->dispatch('saveTriggered');
    }

    public function updatedSelectedTujuan($value): void
    {
        $this->selectedTujuan = $value;
    }

    /**
     * Handle event dari RealisasiTable component
     */
    public function handleDataChange($data)
    {
        // Handle perubahan data dari component
        // Bisa tambahkan logic additional di sini jika diperlukan
    }

    public function handleDataSaved($result)
    {
        if ($result['success']) {
            Notification::make()
                ->title('Berhasil')
                ->body("Data realisasi berhasil disimpan ({$result['count']} item).")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title('Error')
                ->body($result['message'])
                ->danger()
                ->send();
        }
    }
}
