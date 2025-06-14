<?php

namespace App\Filament\Pages;

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
use App\Models\SubKegiatan;
use App\Models\CapaianKinerja as CapaianKinerjaModel;
use App\Models\CapaianKinerjaProgram;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;

class PerencanaanKinerjaProgram extends Page implements HasTable, HasForms
{
  use InteractsWithTable, InteractsWithForms, HasYearFilter;
  protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
  {
    $selectedYear = $this->data['tahun'] ?? YearContext::getActiveYear();
    return CapaianKinerjaModel::query()
      ->with(['program', 'kegiatan', 'subKegiatan'])
      ->where('tahun', YearContext::getActiveYear());
  }

  // protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
  protected static ?string $navigationGroup = 'Perencanaan';
  protected static string $view = 'filament.pages.perencanaan-kinerja-program';
  protected static ?string $title = 'Target Program';
  protected static ?string $navigationLabel = 'Target Program';
  protected static ?string $pluralLabel = 'Target Program';
  protected static ?string $pluralModelLabel = 'Target Program';
  protected static ?int $navigationSort = 1;
  public ?array $data = [];
  // protected static function getYearColumn(): string
  // {
  //   return 'tahun'; // atau 'year' sesuai dengan struktur tabel
  // }
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
        Section::make('Tambah Target Program Baru')
          ->description('Kelola target dan perencanaan kinerja kegiatan untuk setiap program')
          ->schema([
            Grid::make([
              'default' => 1,
              'sm' => 1,
              'md' => 2,
              'lg' => 2,
              'xl' => 2,
            ])
              ->schema([
                Select::make('id_program')
                  ->label('Program')
                  ->options(Program::pluck('nama_program', 'id_program'))
                  ->reactive()
                  ->placeholder('Silahkan Pilih Program')
                  ->afterStateUpdated(fn(callable $set) => $set('id_kegiatan', null))
                  ->searchable()
                  ->preload()
                  ->required()
                  ->columnSpan([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 1,
                  ]),

                TextInput::make('tahun')
                  ->label('Tahun')
                  ->placeholder('Masukkan tahun target')
                  ->default(date('Y'))
                  ->numeric()
                  ->minValue(2020)
                  ->maxValue(2030)
                  ->required()
                  ->columnSpan([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 1,
                  ]),
              ]),

            Grid::make([
              'default' => 1,
              'sm' => 1,
              'md' => 2,
              'lg' => 2,
              'xl' => 2,
            ])
              ->schema([
                TextInput::make('target_nilai')
                  ->label('Target (Nilai/Angka)')
                  ->numeric()
                  ->placeholder('Isilah Sesuai Value/Nilai Target Anda')
                  ->required()
                  ->columnSpan([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 1,
                  ]),

                TextInput::make('target_dokumen')
                  ->label('Satuan')
                  ->placeholder('Isilah Sesuai Tipe Target Anda')
                  ->required()
                  ->columnSpan([
                    'default' => 1,
                    'sm' => 1,
                    'md' => 1,
                    'lg' => 1,
                    'xl' => 1,
                  ]),
              ]),
          ])
          ->columnSpanFull(),
      ])
      ->statePath('data')
      ->columns([
        'default' => 1,
        'sm' => 1,
        'md' => 1,
        'lg' => 1,
        'xl' => 1,
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->query($this->getTableQuery())
      ->columns([
        TextColumn::make('program.kode_program')
          ->label('Kode Program')
          ->sortable()
          ->searchable(),

        TextColumn::make('program.nama_program')
          ->label('Nama Program')
          ->wrap()
          ->sortable()
          ->searchable(),

        TextColumn::make('target_dokumen')
          ->label('Satuan')
          ->alignCenter(),

        TextColumn::make('target_nilai')
          ->label('Target (Nilai/Angka)')
          ->alignCenter(),

        TextColumn::make('tahun')
          ->label('Tahun')
          ->alignCenter(),

        TextColumn::make('tw1')
          ->label('TW 1')
          ->alignCenter(),

        TextColumn::make('tw2')
          ->label('TW 2')
          ->alignCenter(),

        TextColumn::make('tw3')
          ->label('TW 3')
          ->alignCenter(),

        TextColumn::make('tw4')
          ->label('TW 4')
          ->alignCenter(),

        TextColumn::make('total')
          ->label('Total')
          ->alignCenter(),

        TextColumn::make('persentase')
          ->label('Persentase')
          ->alignCenter(),

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
            Section::make('Edit Target Program')
              ->schema([
                Grid::make([
                  'default' => 1,
                  'sm' => 1,
                  'md' => 2,
                  'lg' => 2,
                  'xl' => 2,
                ])
                  ->schema([
                    Select::make('program_id')
                      ->label('Program')
                      ->options(Program::all()->pluck('nama_program', 'id'))
                      ->placeholder('Silahkan Pilih Program')
                      ->required()
                      ->searchable()
                      ->preload()
                      ->columnSpan([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                      ]),

                    TextInput::make('tahun')
                      ->label('Tahun')
                      ->default(date('Y'))
                      ->numeric()
                      ->required()
                      ->columnSpan([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                      ]),
                  ]),

                Grid::make([
                  'default' => 1,
                  'sm' => 1,
                  'md' => 2,
                  'lg' => 2,
                  'xl' => 2,
                ])
                  ->schema([
                    TextInput::make('target_nilai')
                      ->label('Target (Nilai/Angka)')
                      ->placeholder('Isilah Sesuai Value/Nilai Target Anda')
                      ->numeric()
                      ->required()
                      ->columnSpan([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                      ]),

                    TextInput::make('target_dokumen')
                      ->label('Satuan')
                      ->placeholder('Isilah Sesuai Tipe Target Anda')
                      ->required()
                      ->columnSpan([
                        'default' => 1,
                        'sm' => 1,
                        'md' => 1,
                        'lg' => 1,
                        'xl' => 1,
                      ]),
                  ]),

                Select::make('status_perencanaan')
                  ->label('Status Perencanaan')
                  ->options([
                    'draft' => 'Draft',
                    'approved' => 'Disetujui',
                  ])
                  ->required()
                  ->columnSpanFull(),
              ]),
          ])
          // ->fillForm(function ($record) {
          //   return [
          //     'id' => $record->id,
          //     'nama_program' => $record->program->nama_program ?? '-',
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
              ->title('Target program berhasil diperbarui')
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
      ->emptyStateHeading('Belum Ada Data Target Program')
      ->emptyStateDescription('Silakan tambahkan target program baru menggunakan form di atas.')
      ->emptyStateIcon('heroicon-o-document-text');
  }

  public function save(): void
  {
    $data = $this->form->getState();

    $exists = CapaianKinerjaProgram::where([
      'id_program' => $data['id_program'],
      'tahun' => $data['tahun'],
    ])->exists();

    if ($exists) {
      Notification::make()
        ->title('Data sudah ada')
        ->body('Kombinasi Program dan Tahun sudah ada dalam sistem')
        ->danger()
        ->send();
      return;
    }

    // Get the program details
    $program = Program::find($data['id_program']);

    if (!$program) {
      Notification::make()
        ->title('Program tidak ditemukan')
        ->body('Program yang dipilih tidak valid')
        ->danger()
        ->send();
      return;
    }

    // Prepare data with all required fields
    $data['kode_program'] = $program->kode_program;
    $data['nama_program'] = $program->nama_program;

    // Initialize realization fields (your model will auto-calculate total and percentage)
    $data['tw1'] = 0;
    $data['tw2'] = 0;
    $data['tw3'] = 0;
    $data['tw4'] = 0;
    $data['total'] = 0;
    $data['persentase'] = 0;
    $data['status_perencanaan'] = 'draft';
    $data['status_realisasi'] = 'not_started';

    // Add organizational ID if needed (you may need to get this from session/auth)
    // $data['organisasi_id'] = auth()->user()->organisasi_id ?? null;

    CapaianKinerjaProgram::create($data);

    $this->form->fill();

    Notification::make()
      ->title('Data target program berhasil disimpan')
      ->success()
      ->send();
  }
  public function refreshTable(): void
  {
    $this->resetTable();
  }
}
