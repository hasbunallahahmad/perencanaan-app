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
use Illuminate\Database\Eloquent\Builder;
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

class CapaianKinerja extends Page implements HasForms, HasTable
{
    use InteractsWithForms;
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.capaian-kinerja';
    protected static ?string $title = 'Capaian Kinerja';
    protected static ?string $navigationLabel = 'Capaian Kinerja';
    protected static ?string $pluralLabel = 'Capaian Kinerja';
    protected static ?string $pluralModelLabel = 'Capaian Kinerja';

    public ?array $data = [];

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
                        TextInput::make('target_dokumen')
                            ->label('Target (Dokumen)')
                            ->placeholder('Isilah Sesuai Tipe Target Anda')
                            ->required(),

                        TextInput::make('target_nilai')
                            ->label('Target (Nilai/Angka)')
                            ->numeric()
                            ->placeholder('Isilah Sesuai Value/Nilai Target Anda')
                            ->required(),
                    ]),

                Grid::make(4)
                    ->schema([
                        TextInput::make('tw1')
                            ->label('TW 1')
                            ->numeric()
                            ->reactive()
                            ->helperText('Isilah 1 Per 1 Sampai Total Nilai dan Persentase Muncul')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $this->calculateTotal($set, $get);
                            }),

                        TextInput::make('tw2')
                            ->label('TW 2')
                            ->numeric()
                            ->reactive()
                            ->helperText('Isilah 1 Per 1 Sampai Total Nilai dan Persentase Muncul')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $this->calculateTotal($set, $get);
                            }),

                        TextInput::make('tw3')
                            ->label('TW 3')
                            ->numeric()
                            ->reactive()
                            ->helperText('Isilah 1 Per 1 Sampai Total Nilai dan Persentase Muncul')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $this->calculateTotal($set, $get);
                            }),

                        TextInput::make('tw4')
                            ->label('TW 4')
                            ->numeric()
                            ->reactive()
                            ->helperText('Isilah 1 Per 1 Sampai Total Nilai dan Persentase Muncul')
                            ->afterStateUpdated(function (callable $set, callable $get) {
                                $this->calculateTotal($set, $get);
                            }),
                    ]),

                Grid::make(2)
                    ->schema([
                        TextInput::make('total')
                            ->label('Total')
                            ->numeric()
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Total = TW 1 + TW 2 + TW 3 + TW 4'),

                        TextInput::make('persentase')
                            ->label('Persentase (%)')
                            ->disabled()
                            ->dehydrated()
                            ->helperText('Persentase = Total / Target')
                            ->suffix('%'),
                    ]),
            ])
            ->statePath('data');
    }

    private function calculateTotal(callable $set, callable $get): void
    {
        $tw1 = (float) ($get('tw1') ?? 0);
        $tw2 = (float) ($get('tw2') ?? 0);
        $tw3 = (float) ($get('tw3') ?? 0);
        $tw4 = (float) ($get('tw4') ?? 0);

        $total = $tw1 + $tw2 + $tw3 + $tw4;
        $set('total', $total);

        $target = (float) ($get('target_nilai') ?? 0);
        if ($target > 0) {
            $persentase = round(($total / $target) * 100, 2);
            $set('persentase', $persentase);
        } else {
            $set('persentase', 0);
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(CapaianKinerjaModel::query()->with(['program', 'kegiatan', 'subKegiatan']))
            ->columns([
                TextColumn::make('program.kode_program')
                    ->label('Kode Program')
                    ->sortable(),

                TextColumn::make('program.nama_program')
                    ->label('Nama Program')
                    ->wrap()
                    ->sortable(),

                TextColumn::make('kegiatan.kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->sortable(),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->wrap()
                    ->sortable(),

                TextColumn::make('subKegiatan.kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->sortable(),

                TextColumn::make('subKegiatan.nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->wrap()
                    ->sortable(),

                TextColumn::make('target_dokumen')
                    ->label('Target (Dok)')
                    ->alignCenter(),

                TextColumn::make('target_nilai')
                    ->label('Target (Nilai)')
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
                    ->alignCenter()
                    ->weight('bold'),

                TextColumn::make('persentase')
                    ->label('Persentase (%)')
                    ->alignCenter()
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->color(fn($state) => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger'
                    }),

                TextColumn::make('tahun')
                    ->label('Tahun')
                    ->alignCenter(),
            ])
            ->actions([
                Action::make('edit')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Hidden::make('id'),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('target_dokumen')
                                    ->label('Target (Dokumen)')
                                    ->required()
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('target_nilai')
                                    ->label('Target (Nilai/Angka)')
                                    ->numeric()
                                    ->required(),
                            ]),

                        Grid::make(4)
                            ->schema([
                                TextInput::make('tw1')
                                    ->label('TW 1')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $this->calculateTotalInModal($set, $get);
                                    }),

                                TextInput::make('tw2')
                                    ->label('TW 2')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $this->calculateTotalInModal($set, $get);
                                    }),

                                TextInput::make('tw3')
                                    ->label('TW 3')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $this->calculateTotalInModal($set, $get);
                                    }),

                                TextInput::make('tw4')
                                    ->label('TW 4')
                                    ->numeric()
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get) {
                                        $this->calculateTotalInModal($set, $get);
                                    }),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('total')
                                    ->label('Total')
                                    ->disabled()
                                    ->dehydrated(),

                                TextInput::make('persentase')
                                    ->label('Persentase (%)')
                                    ->disabled()
                                    ->dehydrated()
                                    ->suffix('%'),
                            ]),
                    ])
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function (array $data, $record) {
                        $record->update($data);

                        Notification::make()
                            ->title('Data berhasil diperbarui')
                            ->success()
                            ->send();
                    }),

                Action::make('delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->delete();

                        Notification::make()
                            ->title('Data berhasil dihapus')
                            ->success()
                            ->send();
                    }),
            ])
            ->headerActions([
                Action::make('create')
                    ->label('Tambah Data')
                    ->icon('heroicon-o-plus')
                    ->action(function () {
                        $this->save();
                    }),
            ]);
    }

    private function calculateTotalInModal(callable $set, callable $get): void
    {
        $tw1 = (float) ($get('tw1') ?? 0);
        $tw2 = (float) ($get('tw2') ?? 0);
        $tw3 = (float) ($get('tw3') ?? 0);
        $tw4 = (float) ($get('tw4') ?? 0);

        $total = $tw1 + $tw2 + $tw3 + $tw4;
        $set('total', $total);

        $target = (float) ($get('target_nilai') ?? 0);
        if ($target > 0) {
            $persentase = round(($total / $target) * 100, 2);
            $set('persentase', $persentase);
        } else {
            $set('persentase', 0);
        }
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // Calculate total and percentage
        $total = ($data['tw1'] ?? 0) + ($data['tw2'] ?? 0) + ($data['tw3'] ?? 0) + ($data['tw4'] ?? 0);
        $data['total'] = $total;

        if ($data['target_nilai'] > 0) {
            $data['persentase'] = round(($total / $data['target_nilai']) * 100, 2);
        } else {
            $data['persentase'] = 0;
        }

        CapaianKinerjaModel::create($data);

        $this->form->fill();

        Notification::make()
            ->title('Data berhasil disimpan')
            ->success()
            ->send();
    }
}
