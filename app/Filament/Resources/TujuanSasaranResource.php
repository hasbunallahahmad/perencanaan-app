<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TujuanSasaranResource\Pages;
use App\Models\Tujas;
use App\Models\MasterTujuanSasaran;
use App\Models\MasterSasaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Forms\Get;
use Filament\Forms\Set;

class TujuanSasaranResource extends Resource
{
    protected static bool $shouldRegisterNavigation = false;
    protected static ?string $model = Tujas::class;
    protected static ?string $navigationLabel = 'Tujuan & Sasaran';
    protected static ?string $modelLabel = 'Tujuan & Sasaran';
    protected static ?string $pluralModelLabel = 'Tujuan & Sasaran';
    protected static ?string $navigationGroup = 'Perencanaan';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\TextInput::make('tahun')
                            ->label('Tahun')
                            ->required()
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->default(date('Y'))
                            ->columnSpanFull(),
                    ]),

                // SECTION 1: TUJUAN
                Forms\Components\Section::make('TUJUAN')
                    ->description('Pilih tujuan dan lengkapi informasi terkait')
                    ->schema([
                        Forms\Components\Select::make('master_tujuan_sasaran_id')
                            ->label('Pilih Tujuan')
                            ->relationship('masterTujuanSasaran', 'tujuan')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Reset tujuan when master changes
                                $set('tujuan', '');
                                $set('indikator_tujuan_text', '');

                                // Auto-fill tujuan from master
                                if ($state) {
                                    $master = MasterTujuanSasaran::find($state);
                                    if ($master) {
                                        $set('tujuan', $master->tujuan);
                                        $set('indikator_tujuan_text', $master->indikator ?? '');
                                    }
                                }
                            }),
                        Forms\Components\Hidden::make('tujuan'),

                        Forms\Components\Select::make('indikator_tujuan_text')
                            ->label('Indikator Tujuan')
                            ->relationship('masterTujuanSasaran', 'indikator_tujuan')
                            ->preload()
                            ->required()
                            ->live()
                            ->visible(fn(Get $get): bool => filled($get('tujuan')))
                            ->helperText('Indikator untuk mengukur pencapaian tujuan'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('target_tujuan')
                                    ->label('Target Tujuan')
                                    ->required()
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn(Get $get): bool => filled($get('indikator_tujuan_text'))),

                                Forms\Components\TextInput::make('satuan_tujuan')
                                    ->label('Satuan Tujuan')
                                    ->required()
                                    ->live()
                                    ->placeholder('Contoh: %, unit, orang')
                                    ->default('unit')
                                    ->visible(fn(Get $get): bool => filled($get('indikator_tujuan_text'))),
                            ]),
                    ]),

                // SECTION 2: SASARAN
                Forms\Components\Section::make('SASARAN')
                    ->description('Pilih sasaran dan lengkapi informasi terkait')
                    ->schema([
                        Forms\Components\Select::make('master_sasaran_id')
                            ->label('Pilih Master Sasaran')
                            ->relationship('masterSasaran', 'sasaran')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->visible(fn(Get $get): bool => filled($get('target_tujuan')) && filled($get('satuan_tujuan')))
                            ->afterStateUpdated(function (Set $set, Get $get, $state) {
                                // Reset sasaran when master changes
                                $set('sasaran', '');
                                $set('indikator_sasaran_text', '');

                                // Auto-fill sasaran from master
                                if ($state) {
                                    $master = MasterSasaran::find($state);
                                    if ($master) {
                                        $set('sasaran', $master->sasaran);
                                        $set('indikator_sasaran_text', $master->indikator ?? '');
                                    }
                                }
                            }),

                        Forms\Components\Hidden::make('sasaran'),

                        Forms\Components\Select::make('indikator_sasaran_text')
                            ->label('Indikator Sasaran')
                            ->preload()
                            ->relationship('masterSasaran', 'indikator_sasaran')
                            ->required()
                            ->live()
                            ->visible(fn(Get $get): bool => filled($get('sasaran')))
                            ->helperText('Indikator untuk mengukur pencapaian sasaran'),

                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('target_sasaran')
                                    ->label('Target Sasaran')
                                    ->required()
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0)
                                    ->visible(fn(Get $get): bool => filled($get('indikator_sasaran_text'))),

                                Forms\Components\TextInput::make('satuan_sasaran')
                                    ->label('Satuan Sasaran')
                                    ->required()
                                    ->live()
                                    ->placeholder('Contoh: %, unit, orang')
                                    ->default('unit')
                                    ->visible(fn(Get $get): bool => filled($get('indikator_sasaran_text'))),
                            ]),
                    ]),

                // SECTION 3: REALISASI (Only for editing/updating)
                Forms\Components\Section::make('Realisasi Tujuan')
                    ->description('Data realisasi per triwulan - dapat diupdate oleh user')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('realisasi_tujuan_tw_1')
                                    ->label('TW 1')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_tujuan_tw_2')
                                    ->label('TW 2')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_tujuan_tw_3')
                                    ->label('TW 3')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_tujuan_tw_4')
                                    ->label('TW 4')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                    ])
                    ->visible(fn($operation): bool => $operation === 'edit'),

                Forms\Components\Section::make('Realisasi Sasaran')
                    ->description('Data realisasi per triwulan - dapat diupdate oleh user')
                    ->schema([
                        Forms\Components\Grid::make(4)
                            ->schema([
                                Forms\Components\TextInput::make('realisasi_sasaran_tw_1')
                                    ->label('TW 1')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_sasaran_tw_2')
                                    ->label('TW 2')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_sasaran_tw_3')
                                    ->label('TW 3')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),

                                Forms\Components\TextInput::make('realisasi_sasaran_tw_4')
                                    ->label('TW 4')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0)
                                    ->default(0),
                            ]),
                    ])
                    ->visible(fn($operation): bool => $operation === 'edit'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('sasaran')
                    ->label('Sasaran')
                    ->searchable()
                    ->wrap()
                    ->limit(50),

                Tables\Columns\TextColumn::make('target_tujuan')
                    ->label('Target Tujuan')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->satuan_tujuan),

                Tables\Columns\TextColumn::make('total_realisasi_tujuan')
                    ->label('Realisasi Tujuan')
                    ->numeric(decimalPlaces: 3)
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->satuan_tujuan),

                Tables\Columns\TextColumn::make('persentase_tujuan_calculated')
                    ->label('% Tujuan')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->sortable()
                    ->color(fn($record) => $record->status_tujuan_color),

                Tables\Columns\BadgeColumn::make('status_tujuan_pencapaian')
                    ->label('Status Tujuan')
                    ->color(fn($record) => $record->status_tujuan_color),

                Tables\Columns\TextColumn::make('target_sasaran')
                    ->label('Target Sasaran')
                    ->numeric(decimalPlaces: 3)
                    ->sortable()
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->satuan_sasaran)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('total_realisasi_sasaran')
                    ->label('Realisasi Sasaran')
                    ->numeric(decimalPlaces: 3)
                    ->formatStateUsing(fn($state, $record) => $state . ' ' . $record->satuan_sasaran)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('persentase_sasaran_calculated')
                    ->label('% Sasaran')
                    ->numeric(decimalPlaces: 2)
                    ->suffix('%')
                    ->color(fn($record) => $record->status_sasaran_color)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\BadgeColumn::make('status_sasaran_pencapaian')
                    ->label('Status Sasaran')
                    ->color(fn($record) => $record->status_sasaran_color)
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $years = range(date('Y') - 5, date('Y') + 2);
                        return array_combine($years, $years);
                    }),

                Tables\Filters\SelectFilter::make('status_tujuan')
                    ->label('Status Tujuan')
                    ->options([
                        'Tercapai' => 'Tercapai',
                        'Baik' => 'Baik',
                        'Cukup' => 'Cukup',
                        'Kurang' => 'Kurang',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        if (!$data['value']) {
                            return $query;
                        }

                        return match ($data['value']) {
                            'Tercapai' => $query->highAchievement(100),
                            'Baik' => $query->achievementBetween(75, 99.99),
                            'Cukup' => $query->achievementBetween(50, 74.99),
                            'Kurang' => $query->lowAchievement(50),
                            default => $query,
                        };
                    }),

                Tables\Filters\Filter::make('high_achievement')
                    ->label('Pencapaian Tinggi (≥100%)')
                    ->query(fn(Builder $query): Builder => $query->highAchievement()),

                Tables\Filters\Filter::make('low_achievement')
                    ->label('Pencapaian Rendah (<50%)')
                    ->query(fn(Builder $query): Builder => $query->lowAchievement()),

                Tables\Filters\TrashedFilter::make(),
            ], layout: FiltersLayout::AboveContent)
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),

                // Modal untuk update realisasi (untuk user biasa)
                Tables\Actions\Action::make('update_realisasi')
                    ->label('Update Realisasi')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->modalHeading('Update Realisasi')
                    ->modalDescription(fn($record) => "Update realisasi untuk: {$record->tujuan}")
                    ->modalWidth('7xl')
                    ->form([
                        Forms\Components\Section::make('Realisasi Tujuan')
                            ->description(fn($record) => "Target: " . number_format($record->target_tujuan, 3) . " " . $record->satuan_tujuan)
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('realisasi_tujuan_tw_1')
                                            ->label('TW 1')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_tujuan_tw_2')
                                            ->label('TW 2')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_tujuan_tw_3')
                                            ->label('TW 3')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_tujuan_tw_4')
                                            ->label('TW 4')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),
                                    ]),
                            ]),

                        Forms\Components\Section::make('Realisasi Sasaran')
                            ->description(fn($record) => "Target: " . number_format($record->target_sasaran, 3) . " " . $record->satuan_sasaran)
                            ->schema([
                                Forms\Components\Grid::make(4)
                                    ->schema([
                                        Forms\Components\TextInput::make('realisasi_sasaran_tw_1')
                                            ->label('TW 1')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_sasaran_tw_2')
                                            ->label('TW 2')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_sasaran_tw_3')
                                            ->label('TW 3')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),

                                        Forms\Components\TextInput::make('realisasi_sasaran_tw_4')
                                            ->label('TW 4')
                                            ->numeric()
                                            ->step(0.001)
                                            ->minValue(0),
                                    ]),
                            ]),
                    ])
                    ->fillForm(fn($record) => [
                        'realisasi_tujuan_tw_1' => $record->realisasi_tujuan_tw_1,
                        'realisasi_tujuan_tw_2' => $record->realisasi_tujuan_tw_2,
                        'realisasi_tujuan_tw_3' => $record->realisasi_tujuan_tw_3,
                        'realisasi_tujuan_tw_4' => $record->realisasi_tujuan_tw_4,
                        'realisasi_sasaran_tw_1' => $record->realisasi_sasaran_tw_1,
                        'realisasi_sasaran_tw_2' => $record->realisasi_sasaran_tw_2,
                        'realisasi_sasaran_tw_3' => $record->realisasi_sasaran_tw_3,
                        'realisasi_sasaran_tw_4' => $record->realisasi_sasaran_tw_4,
                    ])
                    ->action(function (array $data, Tujas $record): void {
                        $record->update($data);
                    })
                    ->successNotificationTitle('Realisasi berhasil diupdate'),

                Tables\Actions\Action::make('reset_realisasi')
                    ->label('Reset Realisasi')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->modalHeading('Reset Realisasi')
                    ->modalDescription('Apakah Anda yakin ingin mengatur ulang semua realisasi ke 0?')
                    ->action(fn(Tujas $record) => $record->resetRealisasi())
                    ->successNotificationTitle('Realisasi berhasil direset'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\BulkAction::make('bulk_reset_realisasi')
                        ->label('Reset Realisasi')
                        ->icon('heroicon-o-arrow-path')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->modalHeading('Reset Realisasi')
                        ->modalDescription('Apakah Anda yakin ingin mengatur ulang semua realisasi yang dipilih ke 0?')
                        ->action(function ($records) {
                            foreach ($records as $record) {
                                $record->resetRealisasi();
                            }
                        })
                        ->deselectRecordsAfterCompletion()
                        ->successNotificationTitle('Realisasi berhasil direset'),
                ]),
            ])
            ->defaultSort('tahun', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('tahun')
                                    ->label('Tahun')
                                    ->weight(FontWeight::Bold),

                                Infolists\Components\TextEntry::make('masterTujuanSasaran.nama')
                                    ->label('Master Tujuan Sasaran'),
                            ]),

                        Infolists\Components\TextEntry::make('tujuan')
                            ->label('Tujuan')
                            ->columnSpanFull(),

                        Infolists\Components\TextEntry::make('sasaran')
                            ->label('Sasaran')
                            ->columnSpanFull(),

                        Infolists\Components\Grid::make(2)
                            ->schema([
                                Infolists\Components\TextEntry::make('indikator_tujuan_text')
                                    ->label('Indikator Tujuan'),

                                Infolists\Components\TextEntry::make('indikator_sasaran_text')
                                    ->label('Indikator Sasaran'),
                            ]),
                    ]),

                Infolists\Components\Section::make('Target & Realisasi Tujuan')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('target_tujuan')
                                    ->label('Target')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),

                                Infolists\Components\TextEntry::make('total_realisasi_tujuan')
                                    ->label('Total Realisasi')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),

                                Infolists\Components\TextEntry::make('persentase_tujuan_calculated')
                                    ->label('Persentase')
                                    ->badge()
                                    ->color(fn($record) => $record->status_tujuan_color)
                                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%'),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('realisasi_tujuan_tw_1')
                                    ->label('TW 1')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),

                                Infolists\Components\TextEntry::make('realisasi_tujuan_tw_2')
                                    ->label('TW 2')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),

                                Infolists\Components\TextEntry::make('realisasi_tujuan_tw_3')
                                    ->label('TW 3')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),

                                Infolists\Components\TextEntry::make('realisasi_tujuan_tw_4')
                                    ->label('TW 4')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_tujuan),
                            ]),

                        Infolists\Components\TextEntry::make('status_tujuan_pencapaian')
                            ->label('Status Pencapaian')
                            ->badge()
                            ->color(fn($record) => $record->status_tujuan_color),
                    ]),

                Infolists\Components\Section::make('Target & Realisasi Sasaran')
                    ->schema([
                        Infolists\Components\Grid::make(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('target_sasaran')
                                    ->label('Target')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),

                                Infolists\Components\TextEntry::make('total_realisasi_sasaran')
                                    ->label('Total Realisasi')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),

                                Infolists\Components\TextEntry::make('persentase_sasaran_calculated')
                                    ->label('Persentase')
                                    ->badge()
                                    ->color(fn($record) => $record->status_sasaran_color)
                                    ->formatStateUsing(fn($state) => number_format($state, 2) . '%'),
                            ]),

                        Infolists\Components\Grid::make(4)
                            ->schema([
                                Infolists\Components\TextEntry::make('realisasi_sasaran_tw_1')
                                    ->label('TW 1')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),

                                Infolists\Components\TextEntry::make('realisasi_sasaran_tw_2')
                                    ->label('TW 2')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),

                                Infolists\Components\TextEntry::make('realisasi_sasaran_tw_3')
                                    ->label('TW 3')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),

                                Infolists\Components\TextEntry::make('realisasi_sasaran_tw_4')
                                    ->label('TW 4')
                                    ->formatStateUsing(fn($state, $record) => number_format($state, 3) . ' ' . $record->satuan_sasaran),
                            ]),

                        Infolists\Components\TextEntry::make('status_sasaran_pencapaian')
                            ->label('Status Pencapaian')
                            ->badge()
                            ->color(fn($record) => $record->status_sasaran_color),
                    ]),
            ]);
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

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['masterTujuanSasaran', 'masterSasaran']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['tujuan', 'sasaran', 'tahun', 'masterTujuanSasaran.nama', 'masterSasaran.nama'];
    }

    public static function getGlobalSearchResultDetails(\Illuminate\Database\Eloquent\Model $record): array
    {
        return [
            'Tahun' => $record->tahun,
            'Target Tujuan' => number_format($record->target_tujuan, 3) . ' ' . $record->satuan_tujuan,
            'Status' => $record->status_tujuan_pencapaian,
        ];
    }
}
