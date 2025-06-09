<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TujuanSasaranResource\Pages;
use App\Filament\Resources\TujuanSasaranResource\RelationManagers;
use App\Models\Tujas;
use App\Models\TujuanSasaran;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
use Carbon\Carbon;
use Faker\Provider\Base;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use PhpParser\Node\Stmt\Label;
use Filament\Notifications\Notification;

class TujuanSasaranResource extends BaseResource
{
    protected static ?string $model = Tujas::class;
    protected static ?string $navigationGroup = 'Capaian Kinerja';
    protected static ?string $title = 'Tujuan & Sasaran';
    protected static ?string $navigationLabel = 'Tujuan & Sasaran';
    protected static ?string $modelLabel = 'Tujuan & Sasaran';
    protected static ?string $pluralModelLabel = 'Tujuan & Sasaran';
    use HasYearFilter;
    // protected static function getYearColumn(): string
    // {
    //     return 'tahun'; // atau 'year' sesuai dengan struktur tabel
    // }
    private function canAccessQuarter(int $quarter): bool
    {
        $currentMonth = Carbon::now()->month;
        return match ($quarter) {
            1 => $currentMonth >= 1 && $currentMonth <= 3,
            2 => $currentMonth >= 4 && $currentMonth <= 6,
            3 => $currentMonth >= 7 && $currentMonth <= 9,
            4 => $currentMonth >= 10 && $currentMonth <= 12,
            default => false
        };
    }

    private function getQuarterMonths(int $quarter): string
    {
        return match ($quarter) {
            1 => 'Januari - Maret',
            2 => 'April - Juni',
            3 => 'Juli - September',
            4 => 'Oktober - Desember',
            default => ''
        };
    }

    private function isPastQuarter(int $quarter): bool
    {
        $currentMonth = Carbon::now()->month;
        return match ($quarter) {
            1 => $currentMonth > 3,
            2 => $currentMonth > 6,
            3 => $currentMonth > 9,
            4 => false,
            default => false
        };
    }
    private function calculateTotalInModal(callable $set, callable $get): void
    {
        $tw1 = (float) $get('realisasi_tw_1') ?? 0;
        $tw2 = (float) $get('realisasi_tw_2') ?? 0;
        $tw3 = (float) $get('realisasi_tw_3') ?? 0;
        $tw4 = (float) $get('realisasi_tw_4') ?? 0;

        $total = $tw1 + $tw2 + $tw3 + $tw4;
        $set('total_realisasi', $total);

        if ($get('target') > 0) {
            $persentase = ($total / $get('target')) * 100;
            $set('persentase_preview', number_format($persentase, 2) . '%');
        } else {
            $set('persentase_preview', '0%');
        }
    }
    use HasYearFilter;
    protected static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('tahun', YearContext::getActiveYear());
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ])
            ->where('tahun', YearContext::getActiveYear());
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Textarea::make('tujuan')
                                    ->label('Tujuan')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('sasaran')
                                    ->label('Sasaran')
                                    ->required()
                                    ->rows(3)
                                    ->columnSpanFull(),

                                Forms\Components\Textarea::make('indikator')
                                    ->label('Indikator')
                                    ->required()
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ]),
                    ]),

                Forms\Components\Section::make('Target & Satuan')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('target')
                                    ->label('Target')
                                    ->required()
                                    ->numeric()
                                    ->step(0.001)
                                    ->prefix('📊'),

                                Forms\Components\TextInput::make('satuan')
                                    ->label('Satuan')
                                    ->required()
                                    ->maxLength(50)
                                    ->placeholder('Contoh: Persen, Unit, Orang, dll')
                                    ->prefix('📏'),
                            ]),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->wrap()
                    ->extraAttributes(['class' => 'compact-cell'])
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sasaran')
                    ->label('Sasaran')
                    ->wrap()
                    ->extraAttributes(['class' => 'compact-cell'])
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 50 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('indikator')
                    ->label('Indikator')
                    ->limit(40)
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 40 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('target')
                    ->label('Target')
                    ->numeric(4)
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('realisasi_tw_1')
                    ->label('TW 1')
                    ->numeric(2)
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('realisasi_tw_2')
                    ->label('TW 2')
                    ->numeric(2)
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('realisasi_tw_3')
                    ->label('TW 3')
                    ->numeric(2)
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('realisasi_tw_4')
                    ->label('TW 4')
                    ->numeric(2)
                    ->sortable()
                    ->alignEnd(),

                Tables\Columns\TextColumn::make('persentase_calculated')
                    ->label('Pencapaian (%)')
                    ->state(function (Tujas $record): float {
                        $totalRealisasi = ($record->realisasi_tw_1 ?? 0) +
                            ($record->realisasi_tw_2 ?? 0) +
                            ($record->realisasi_tw_3 ?? 0) +
                            ($record->realisasi_tw_4 ?? 0);

                        return $record->target > 0 ? ($totalRealisasi / $record->target) * 100 : 0;
                    })
                    ->numeric(2)
                    ->suffix('%')
                    ->sortable()
                    ->alignEnd()
                    ->color(fn(string $state): string => match (true) {
                        $state >= 100 => 'success',
                        $state >= 75 => 'warning',
                        default => 'danger',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\SelectFilter::make('satuan')
                    ->label('Satuan')
                    ->options(function () {
                        return Tujas::distinct('satuan')
                            ->pluck('satuan', 'satuan')
                            ->toArray();
                    }),

                Tables\Filters\Filter::make('pencapaian_tinggi')
                    ->label('Pencapaian ≥ 100%')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('
                        (COALESCE(realisasi_tw_1, 0) + 
                         COALESCE(realisasi_tw_2, 0) + 
                         COALESCE(realisasi_tw_3, 0) + 
                         COALESCE(realisasi_tw_4, 0)) / target * 100 >= 100
                    ')),

                Tables\Filters\Filter::make('pencapaian_rendah')
                    ->label('Pencapaian < 75%')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('
                        (COALESCE(realisasi_tw_1, 0) + 
                         COALESCE(realisasi_tw_2, 0) + 
                         COALESCE(realisasi_tw_3, 0) + 
                         COALESCE(realisasi_tw_4, 0)) / target * 100 < 75
                    ')),
            ])
            ->actions([
                Tables\Actions\Action::make('input_realisasi')
                    ->label('Input Realisasi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form([
                        Forms\Components\Section::make('Input Realisasi Triwulan')
                            ->description('Masukkan realisasi untuk setiap triwulan')
                            ->schema([
                                Forms\Components\Placeholder::make('info')
                                    ->label('Informasi Target')
                                    ->content(function (Tujas $record): string {
                                        return "Target: {$record->target} {$record->satuan}";
                                    }),

                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('realisasi_tw_1')
                                            ->label('Realisasi Triwulan 1')
                                            ->numeric()
                                            ->step(0.001)
                                            ->prefix('1️⃣')
                                            ->placeholder('0.00')
                                            ->default(fn(Tujas $record) => $record->realisasi_tw_1)
                                            ->reactive()
                                            ->helperText(function (Tujas $record) {
                                                $resource = new static();
                                                if ($record->realisasi_tw_1 > 0) {
                                                    return 'TW 1 sudah terkunci karena sudah diinput';
                                                }
                                                if (!$resource->canAccessQuarter(1) && $resource->isPastQuarter(1)) {
                                                    return 'Periode TW 1 (' . $resource->getQuarterMonths(1) . ') sudah terlewat';
                                                }
                                                if (!$resource->canAccessQuarter(1)) {
                                                    return 'TW 1 hanya dapat diisi pada periode ' . $resource->getQuarterMonths(1);
                                                }
                                                return 'Realisasi Triwulan 1 (' . $resource->getQuarterMonths(1) . ')';
                                            })
                                            ->disabled(function (Tujas $record) {
                                                $resource = new static();
                                                return $record->realisasi_tw_1 > 0 || !$resource->canAccessQuarter(1);
                                            })
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $resource = new static();
                                                $resource->calculateTotalInModal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('realisasi_tw_2')
                                            ->label('Realisasi Triwulan 2')
                                            ->numeric()
                                            ->step(0.001)
                                            ->prefix('2️⃣')
                                            ->placeholder('0.00')
                                            ->default(fn(Tujas $record) => $record->realisasi_tw_2)
                                            ->reactive()
                                            ->helperText(function (Tujas $record) {
                                                $resource = new static();
                                                if ($record->realisasi_tw_2 > 0) {
                                                    return 'TW 2 sudah terkunci karena sudah diinput';
                                                }
                                                if (!$resource->canAccessQuarter(2) && $resource->isPastQuarter(2)) {
                                                    return 'Periode TW 2 (' . $resource->getQuarterMonths(2) . ') sudah terlewat';
                                                }
                                                if (!$resource->canAccessQuarter(2)) {
                                                    return 'TW 2 hanya dapat diisi pada periode ' . $resource->getQuarterMonths(2);
                                                }
                                                return 'Realisasi Triwulan 2 (' . $resource->getQuarterMonths(2) . ')';
                                            })
                                            ->disabled(function (Tujas $record) {
                                                $resource = new static();
                                                return $record->realisasi_tw_2 > 0 || !$resource->canAccessQuarter(2);
                                            })
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $resource = new static();
                                                $resource->calculateTotalInModal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('realisasi_tw_3')
                                            ->label('Realisasi Triwulan 3')
                                            ->numeric()
                                            ->step(0.001)
                                            ->prefix('3️⃣')
                                            ->placeholder('0.00')
                                            ->default(fn(Tujas $record) => $record->realisasi_tw_3)
                                            ->reactive()
                                            ->helperText(function (Tujas $record) {
                                                $resource = new static();
                                                if ($record->realisasi_tw_3 > 0) {
                                                    return 'TW 3 sudah terkunci karena sudah diinput';
                                                }
                                                if (!$resource->canAccessQuarter(3) && $resource->isPastQuarter(3)) {
                                                    return 'Periode TW 3 (' . $resource->getQuarterMonths(3) . ') sudah terlewat';
                                                }
                                                if (!$resource->canAccessQuarter(3)) {
                                                    return 'TW 3 hanya dapat diisi pada periode ' . $resource->getQuarterMonths(3);
                                                }
                                                return 'Realisasi Triwulan 3 (' . $resource->getQuarterMonths(3) . ')';
                                            })
                                            ->disabled(function (Tujas $record) {
                                                $resource = new static();
                                                return $record->realisasi_tw_3 > 0 || !$resource->canAccessQuarter(3);
                                            })
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $resource = new static();
                                                $resource->calculateTotalInModal($set, $get);
                                            }),

                                        Forms\Components\TextInput::make('realisasi_tw_4')
                                            ->label('Realisasi Triwulan 4')
                                            ->numeric()
                                            ->step(0.001)
                                            ->prefix('4️⃣')
                                            ->placeholder('0.00')
                                            ->default(fn(Tujas $record) => $record->realisasi_tw_4)
                                            ->reactive()
                                            ->helperText(function (Tujas $record) {
                                                $resource = new static();
                                                if ($record->realisasi_tw_4 > 0) {
                                                    return 'TW 4 sudah terkunci karena sudah diinput';
                                                }
                                                if (!$resource->canAccessQuarter(4) && $resource->isPastQuarter(4)) {
                                                    return 'Periode TW 4 (' . $resource->getQuarterMonths(4) . ') sudah terlewat';
                                                }
                                                if (!$resource->canAccessQuarter(4)) {
                                                    return 'TW 4 hanya dapat diisi pada periode ' . $resource->getQuarterMonths(4);
                                                }
                                                return 'Realisasi Triwulan 4 (' . $resource->getQuarterMonths(4) . ')';
                                            })
                                            ->disabled(function (Tujas $record) {
                                                $resource = new static();
                                                return $record->realisasi_tw_4 > 0 || !$resource->canAccessQuarter(4);
                                            })
                                            ->afterStateUpdated(function (callable $set, callable $get) {
                                                $resource = new static();
                                                $resource->calculateTotalInModal($set, $get);
                                            }),
                                    ]),
                                Forms\Components\Hidden::make('target')
                                    ->default(fn(Tujas $record) => $record->target),
                                Forms\Components\Grid::make(2)
                                    ->schema([
                                        Forms\Components\TextInput::make('total_realisasi')
                                            ->label('Total Realisasi')
                                            ->numeric()
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefix('📊')
                                            ->default(function (Tujas $record) {
                                                return ($record->realisasi_tw_1 ?? 0) +
                                                    ($record->realisasi_tw_2 ?? 0) +
                                                    ($record->realisasi_tw_3 ?? 0) +
                                                    ($record->realisasi_tw_4 ?? 0);
                                            }),

                                        Forms\Components\TextInput::make('persentase_preview')
                                            ->label('Pencapaian (%)')
                                            ->disabled()
                                            ->dehydrated(false)
                                            ->prefix('📈')
                                            ->default(function (Tujas $record) {
                                                $total = ($record->realisasi_tw_1 ?? 0) +
                                                    ($record->realisasi_tw_2 ?? 0) +
                                                    ($record->realisasi_tw_3 ?? 0) +
                                                    ($record->realisasi_tw_4 ?? 0);
                                                $persentase = $record->target > 0 ? ($total / $record->target) * 100 : 0;
                                                return number_format($persentase, 2) . '%';
                                            }),
                                    ]),
                            ]),
                    ])
                    ->action(function (array $data, Tujas $record): void {
                        $updateData = [];
                        $resource = new static();
                        if (isset($data['realisasi_tw_1']) && $record->realisasi_tw_1 <= 0 && $resource->canAccessQuarter(1)) {
                            $updateData['realisasi_tw_1'] = $data['realisasi_tw_1'];
                        }
                        if (isset($data['realisasi_tw_2']) && $record->realisasi_tw_2 <= 0 && $resource->canAccessQuarter(2)) {
                            $updateData['realisasi_tw_2'] = $data['realisasi_tw_2'];
                        }
                        if (isset($data['realisasi_tw_3']) && $record->realisasi_tw_3 <= 0 && $resource->canAccessQuarter(3)) {
                            $updateData['realisasi_tw_3'] = $data['realisasi_tw_3'];
                        }
                        if (isset($data['realisasi_tw_4']) && $record->realisasi_tw_4 <= 0 && $resource->canAccessQuarter(4)) {
                            $updateData['realisasi_tw_4'] = $data['realisasi_tw_4'];
                        }

                        if (!empty($updateData)) {
                            $record->update($updateData);

                            $totalRealisasi = ($record->fresh()->realisasi_tw_1 ?? 0) +
                                ($record->fresh()->realisasi_tw_2 ?? 0) +
                                ($record->fresh()->realisasi_tw_3 ?? 0) +
                                ($record->fresh()->realisasi_tw_4 ?? 0);

                            $persentase = $record->target > 0 ? ($totalRealisasi / $record->target) * 100 : 0;

                            Notification::make()
                                ->title('Realisasi berhasil disimpan!')
                                ->body("Total Realisasi: {$totalRealisasi} {$record->satuan} | Pencapaian: " . number_format($persentase, 2) . "%")
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Tidak ada perubahan')
                                ->body('Semua field sudah terkunci atau di luar periode yang diizinkan')
                                ->warning()
                                ->send();
                        }
                    })
                    ->modalWidth('2xl')
                    ->modalSubmitActionLabel('Simpan Realisasi')
                    ->modalCancelActionLabel('Batal')
                    ->visible(function (Tujas $record) {
                        $resource = new static();
                        return ($record->realisasi_tw_1 <= 0 && $resource->canAccessQuarter(1)) ||
                            ($record->realisasi_tw_2 <= 0 && $resource->canAccessQuarter(2)) ||
                            ($record->realisasi_tw_3 <= 0 && $resource->canAccessQuarter(3)) ||
                            ($record->realisasi_tw_4 <= 0 && $resource->canAccessQuarter(4)) ||
                            ($record->realisasi_tw_1 > 0 || $record->realisasi_tw_2 > 0 ||
                                $record->realisasi_tw_3 > 0 || $record->realisasi_tw_4 > 0);
                    }),

                Tables\Actions\DeleteAction::make()
                    ->label(''),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTujuanSasarans::route('/'),
            'create' => Pages\CreateTujuanSasaran::route('/create'),
            'view' => Pages\ViewTujuanSasaran::route('/{record}'),
            'edit' => Pages\EditTujuanSasaran::route('/{record}/edit'),
        ];
    }
}
