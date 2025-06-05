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
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Grid;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Notifications\Notification;

class PerencanaanKinerja extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'Perencanaan';
    protected static string $view = 'filament.pages.perencanaan-kinerja';
    protected static ?string $title = 'Target  SubKegiatan';
    protected static ?string $navigationLabel = 'Target  SubKegiatan';
    protected static ?string $pluralLabel = 'Target  SubKegiatan';
    protected static ?string $pluralModelLabel = 'Target  SubKegiatan';
    protected static ?int $navigationSort = 2;

    public ?array $data = [];
    // Hanya untuk Super Admin
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }
    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Grid::make(4)
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
                            ->afterStateUpdated(fn(callable $set) => $set('id_sub_kegiatan', null))
                            ->required(),

                        Select::make('id_sub_kegiatan')
                            ->label('Sub Kegiatan')
                            ->placeholder('Silahkan Pilih Sub Kegiatan')
                            ->options(function (callable $get) {
                                $kegiatanId = $get('id_kegiatan');
                                if (!$kegiatanId) {
                                    return [];
                                }
                                return SubKegiatan::where('id_kegiatan', $kegiatanId)
                                    ->pluck('nama_sub_kegiatan', 'id_sub_kegiatan');
                            })
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
            ->query(CapaianKinerjaModel::query()->with(['program', 'kegiatan', 'subKegiatan']))
            ->columns([
                // TextColumn::make('program.kode_program')
                //     ->label('Kode Program')
                //     ->sortable(),

                // TextColumn::make('program.nama_program')
                //     ->label('Nama Program')
                //     ->wrap()
                //     ->sortable(),

                // TextColumn::make('kegiatan.kode_kegiatan')
                //     ->label('Kode Kegiatan')
                //     ->sortable(),

                // TextColumn::make('kegiatan.nama_kegiatan')
                //     ->label('Nama Kegiatan')
                //     ->wrap()
                //     ->sortable(),

                TextColumn::make('subKegiatan.kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->sortable(),

                TextColumn::make('subKegiatan.nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->wrap()
                    ->sortable(),

                TextColumn::make('target_dokumen')
                    ->label('Satuan')
                    ->alignCenter(),

                TextColumn::make('target_nilai')
                    ->label('Target (Nilai)')
                    ->alignCenter()
                    ->weight('bold'),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->alignCenter(),

                TextColumn::make('status_perencanaan')
                    ->label('Status')
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
            ->actions([
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Hidden::make('id'),

                        Grid::make(4)
                            ->schema([
                                Select::make('id_program')
                                    ->label('Program')
                                    ->options(Program::pluck('nama_program', 'id_program'))
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('id_kegiatan')
                                    ->label('Kegiatan')
                                    ->options(function ($record) {
                                        if (!$record->id_program) return [];
                                        return Kegiatan::where('id_program', $record->id_program)
                                            ->pluck('nama_kegiatan', 'id_kegiatan');
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                Select::make('id_sub_kegiatan')
                                    ->label('Sub Kegiatan')
                                    ->options(function ($record) {
                                        if (!$record->id_kegiatan) return [];
                                        return SubKegiatan::where('id_kegiatan', $record->id_kegiatan)
                                            ->pluck('nama_sub_kegiatan', 'id_sub_kegiatan');
                                    })
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('tahun')
                                    ->label('Tahun')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(),
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
                    ])
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function (array $data, $record) {
                        $record->update($data);

                        Notification::make()
                            ->title('Target Sukegiatan  berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
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
            ]);
        // ->headerActions([
        //     Action::make('create')
        //         ->label('Tambah Perencanaan')
        //         ->icon('heroicon-o-plus')
        //         ->action(function () {
        //             $this->save();
        //         }),
        // ]);
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Check if combination already exists
        $exists = CapaianKinerjaModel::where([
            'id_program' => $data['id_program'],
            'id_kegiatan' => $data['id_kegiatan'],
            'id_sub_kegiatan' => $data['id_sub_kegiatan'],
            'tahun' => $data['tahun'],
        ])->exists();

        if ($exists) {
            Notification::make()
                ->title('Data sudah ada')
                ->body('Kombinasi Program, Kegiatan, Sub Kegiatan, dan Tahun sudah ada dalam sistem')
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

        CapaianKinerjaModel::create($data);

        $this->form->fill();

        Notification::make()
            ->title('Data perencanaan berhasil disimpan')
            ->success()
            ->send();
    }
}
