<?php

namespace App\Filament\Pages;

use App\Traits\HasYearFilter;
use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Tables\Contracts\HasTable;
use Filament\Forms\Contracts\HasForms;
use App\Models\Program;
use App\Models\Kegiatan;
use App\Models\CapaianKinerjaKegiatan as CapaianKinerjaKegiatanModel;
use App\Services\YearContext;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class PerencanaanKinerjaKegiatan extends Page implements HasForms, HasTable
{
  use HasYearFilter;
  use InteractsWithForms;
  use InteractsWithTable;
  protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
  {
    $selectedYear = $this->data['tahun'] ?? YearContext::getActiveYear();
    return CapaianKinerjaKegiatanModel::query()
      ->with(['program', 'kegiatan', 'subKegiatan'])
      ->where('tahun', YearContext::getActiveYear());
  }

  // protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
  protected static ?string $navigationGroup = 'Perencanaan';
  protected static string $view = 'filament.pages.perencanaan-kinerja-kegiatan';
  protected static ?string $title = 'Target Kegiatan';
  protected static ?string $navigationLabel = 'Target Kegiatan';
  protected static ?string $pluralLabel = 'Target Kegiatan';
  protected static ?string $pluralModelLabel = 'Target Kegiatan';
  protected static ?int $navigationSort = 2;
  // protected static function getYearColumn(): string
  // {
  //   return 'tahun'; // atau 'year' sesuai dengan struktur tabel

  // }

  public ?array $data = [];
  // Hanya untuk Super Admin
  public static function canAccess(): bool
  {
    return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
  }


  public function mount(): void
  {
    $this->form->fill(['tahun' => YearContext::getActiveYear()]);
  }

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Grid::make(3)
          ->schema([
            Select::make('id_program')
              ->label('Program')
              ->options(Program::pluck('nama_program', 'id_program'))
              ->reactive()
              ->placeholder('Silahkan Pilih Program')
              ->afterStateUpdated(fn(callable $set) => $set('id_kegiatan', null))
              ->required(),

            Select::make('id_kegiatan')
              ->label('Kegiatan')
              ->options(function (callable $get) {
                $programId = $get('id_program');
                if (!$programId) {
                  return [];
                }
                return Kegiatan::where('id_program', $programId)
                  ->pluck('nama_kegiatan', 'id_kegiatan');
              })
              ->placeholder('Silahkan Pilih Kegiatan')
              ->reactive()
              ->required(),

            TextInput::make('tahun')
              ->label('Tahun')
              ->numeric()
              ->default(date('Y'))
              ->required(),
          ]),

        Grid::make(2)
          ->schema([
            TextInput::make('target_nilai')
              ->label('Target (Nilai/Angka)')
              ->numeric()
              ->placeholder('Isilah Sesuai Value/Nilai Target Anda')
              ->required(),
            TextInput::make('target_dokumen')
              ->label('Satuan')
              ->placeholder('Isilah Sesuai Tipe Target Anda')
              ->required(),
          ]),
      ])
      ->statePath('data');
  }

  public function table(Table $table): Table
  {
    return $table
      ->query($this->getTableQuery())
      ->columns([
        TextColumn::make('kegiatan.kode_kegiatan')
          ->label('Kode Kegiatan')
          ->sortable()
          ->searchable(),

        TextColumn::make('kegiatan.nama_kegiatan')
          ->label('Nama Kegiatan')
          ->wrap()
          ->sortable()
          ->searchable(),

        TextColumn::make('target_dokumen')
          ->label('Satuan')
          ->alignCenter(),

        TextColumn::make('target_nilai')
          ->label('Target')
          ->alignCenter()
          ->weight('bold')
          ->color('primary'),

        TextColumn::make('tahun')
          ->label('Tahun')
          ->alignCenter()
          ->sortable(),

        TextColumn::make('status_perencanaan')
          ->label('Status Perencanaan')
          ->alignCenter()
          ->badge()
          ->color(fn($state) => match ($state) {
            'draft' => 'gray',
            'approved' => 'success',
            default => 'warning'
          })
          ->formatStateUsing(fn($state) => match ($state) {
            'draft' => 'Draft',
            'approved' => 'Disetujui',
            default => 'Belum Disetujui'
          }),
      ])
      ->filters([
        SelectFilter::make('status_perencanaan')
          ->label('Status')
          ->options([
            'draft' => 'Draft',
            'approved' => 'Disetujui',
          ]),
        SelectFilter::make('tahun')
          ->label('Tahun')
          ->options(function () {
            $years = [];
            $currentYear = date('Y');
            for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
              $years[$i] = $i;
            }
            return $years;
          }),
      ])
      ->actions([
        Action::make('edit')
          ->label('Edit')
          ->icon('heroicon-o-pencil')
          ->color('warning')
          ->form([
            Hidden::make('id'),

            Grid::make(3)
              ->schema([
                TextInput::make('nama_program')
                  ->label('Program')
                  ->disabled()
                  ->dehydrated(false),

                TextInput::make('nama_kegiatan')
                  ->label('Kegiatan')
                  ->disabled()
                  // ->options(function ($record) {
                  //   if (!$record->id_program) return [];
                  //   return Kegiatan::where('id_program', $record->id_program)
                  //     ->pluck('nama_kegiatan', 'id_kegiatan');
                  // })
                  ->formatStateUsing(fn($record) => $record->kegiatan->nama_kegiatan ?? '-')
                  ->dehydrated(),

                TextInput::make('tahun')
                  ->label('Tahun')
                  ->disabled(),
              ]),

            Grid::make(2)
              ->schema([
                TextInput::make('target_nilai')
                  ->label('Target (Nilai/Angka)')
                  ->numeric()
                  ->required(),

                TextInput::make('target_dokumen')
                  ->label('Satuan')
                  ->required(),
              ]),
            // Select::make('status_perencanaan')
            //   ->label('Status Perencanaan')
            //   ->options([
            //     'draft' => 'Draft',
            //     'approved' => 'Disetujui',
            //   ])
            //   ->required(),
          ])
          // ->fillForm(function ($record) {
          //   return [
          //     'id' => $record->id,
          //     'program_name' => $record->program->nama_program ?? '-',
          //     'kegiatan_name' => $record->kegiatan->nama_kegiatan ?? '-',
          //     'tahun' => $record->tahun,
          //     'target_nilai' => $record->target_nilai,
          //     'target_dokumen' => $record->target_dokumen,
          //     'status_perencanaan' => $record->status_perencanaan,
          //   ];
          // })
          ->fillForm(fn($record) => $record->toArray())
          ->action(function (array $data, $record) {
            $record->update($data);

            Notification::make()
              ->title('Target kegiatan berhasil diperbarui')
              ->success()
              ->send();
          }),
        Action::make('delete')
          ->label('Hapus')
          ->icon('heroicon-o-trash')
          ->color('danger')
          ->requiresConfirmation()
          ->modalHeading('Hapus Data Perencanaan')
          ->modalDescription('Apakah Anda yakin ingin menghapus data ini? Tindakan ini tidak dapat dikembalikan.')
          ->action(function ($record) {
            // Check if has realization data
            if ($record->tw1 || $record->tw2 || $record->tw3 || $record->tw4) {
              Notification::make()
                ->title('Tidak dapat menghapus')
                ->body('Data perencanaan ini sudah memiliki data realisasi')
                ->danger()
                ->send();
              return;
            }
            $record->delete();

            Notification::make()
              ->title('Data perencanaan berhasil dihapus')
              ->success()
              ->send();
          }),
      ])
      ->defaultSort('created_at', 'desc')
      ->emptyStateHeading('Belum Ada Data Target Kegiatan')
      ->emptyStateDescription('Silakan tambahkan target kegiatan baru menggunakan form di atas.')
      ->emptyStateIcon('heroicon-o-document-text');
  }

  public function save(): void
  {
    $data = $this->form->getState();

    // Check if combination already exists
    $exists = CapaianKinerjaKegiatanModel::where([
      'id_program' => $data['id_program'],
      'id_kegiatan' => $data['id_kegiatan'],
      'tahun' => $data['tahun'],
    ])->exists();

    if ($exists) {
      Notification::make()
        ->title('Data sudah ada')
        ->body('Kombinasi Program, Kegiatan, dan Tahun sudah ada dalam sistem')
        ->danger()
        ->send();
      return;
    }

    // Initialize realization fields
    $data['tw1'] = 0;
    $data['tw2'] = 0;
    $data['tw3'] = 0;
    $data['tw4'] = 0;
    $data['total'] = 0;
    $data['persentase'] = 0;
    $data['status_perencanaan'] = 'draft';

    CapaianKinerjaKegiatanModel::create($data);

    $this->form->fill();

    Notification::make()
      ->title('Data target kegiatan berhasil disimpan')
      ->success()
      ->send();
  }

  // Add method to get statistics for this page if needed
  public function getStats(): array
  {
    $model = new CapaianKinerjaKegiatanModel();
    return $model->getStats();
  }

  // Add method to get planning dashboard data
  public function getPlanningDashboard(): array
  {
    $model = new CapaianKinerjaKegiatanModel();
    return $model->getPlanningDashboard();
  }
  public function refreshTable(): void
  {
    $this->resetTable();
  }
}
