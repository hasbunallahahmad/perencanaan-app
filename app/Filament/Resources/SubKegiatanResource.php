<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubKegiatanResource\Pages;
use App\Filament\Resources\SubKegiatanResource\RelationManagers;
use App\Models\SubKegiatan;
use App\Models\Kegiatan;
use App\Models\Program;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;

class SubKegiatanResource extends Resource
{
    protected static ?string $model = SubKegiatan::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Sub Kegiatan';

    protected static ?string $pluralLabel = 'Sub Kegiatan';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Data Sub Kegiatan')
                    ->schema([
                        TextInput::make('kode_sub_kegiatan')
                            ->label('Kode Sub Kegiatan')
                            ->required()
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: 2.08.01.2.01.0001'),

                        TextInput::make('nama_sub_kegiatan')
                            ->label('Nama Sub Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama sub kegiatan'),

                        // Select::make('id_kegiatan')
                        //     ->label('Kegiatan')
                        //     ->options(function () {
                        //         return Kegiatan::with(['program.organisasi'])
                        //             ->get()
                        //             ->mapWithKeys(function ($kegiatan) {
                        //                 return [$kegiatan->id => $kegiatan->kode_kegiatan . ' - ' . $kegiatan->nama_kegiatan];
                        //             });
                        //     })
                        //     ->required()
                        //     ->searchable()
                        //     ->placeholder('Pilih kegiatan')
                        //     ->getOptionLabelFromRecordUsing(fn(Kegiatan $record) => "{$record->kode_kegiatan} - {$record->nama_kegiatan}"),
                        Select::make('id_kegiatan')
                            ->label('Kegiatan')
                            ->relationship(
                                name: 'kegiatan',
                                titleAttribute: 'nama_kegiatan',
                                modifyQueryUsing: fn(Builder $query) => $query->orderBy('kode_kegiatan')
                            )
                            ->getOptionLabelFromRecordUsing(
                                fn(Kegiatan $record): string =>
                                "{$record->kode_kegiatan} - {$record->nama_kegiatan}"
                            )
                            ->required()
                            ->searchable()
                            ->preload(),

                        Select::make('sumber_dana')
                            ->label('Sumber Dana')
                            ->multiple()
                            ->default('APBD')
                            ->options([
                                'APBD' => 'APBD',
                                'BANKEU' => 'BANKEU',
                                'APBN' => 'APBN',
                                'DBHCHT' => 'DBHCHT',
                                'DAK' => 'DAK',
                            ])
                            ->required(),

                        TextInput::make('anggaran')
                            ->label('Anggaran')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->step(1)
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true),

                        TextInput::make('realisasi')
                            ->label('Realisasi')
                            ->numeric()
                            ->default(0)
                            ->prefix('Rp')
                            ->placeholder('0')
                            ->step(1)
                            ->minValue(0)
                            ->required()
                            ->live(onBlur: true),
                    ])
                    ->columns(2)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_sub_kegiatan')
                    ->label('Kode Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium')
                    ->wrap(),

                TextColumn::make('nama_sub_kegiatan')
                    ->label('Nama Sub Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(60),

                TextColumn::make('sumber_dana')
                    ->label('Sumber Dana')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('kegiatan.kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kegiatan.nama_kegiatan')
                    ->label('Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(40)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('kegiatan.program.organisasi.nama')
                    ->label('Organisasi')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(25)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('anggaran')
                    ->label('Anggaran')
                    ->numeric()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->alignEnd()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->numeric()
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->label('Total Anggaran'),
                    ]),

                TextColumn::make('realisasi')
                    ->label('Realisasi')
                    ->numeric()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                    ->sortable()
                    ->alignEnd()
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->numeric()
                            ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.'))
                            ->label('Total Realisasi'),
                    ]),

                TextColumn::make('serapan')
                    ->label('Serapan (%)')
                    ->getStateUsing(function (SubKegiatan $record): string {
                        if ($record->anggaran == 0) {
                            return '0';
                        }
                        $serapan = ($record->realisasi / $record->anggaran) * 100;
                        return number_format($serapan, 2);
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderByRaw("
                            CASE 
                                WHEN anggaran = 0 THEN 0 
                                ELSE (realisasi / anggaran * 100) 
                            END {$direction}
                        ");
                    })
                    ->alignCenter()
                    ->badge()
                    ->color(function ($state) {
                        $value = (float) $state;
                        return match (true) {
                            $value >= 80 => 'success',
                            $value >= 60 => 'warning',
                            $value >= 40 => 'info',
                            default => 'danger',
                        };
                    })
                    ->formatStateUsing(fn($state) => $state . '%'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('id_kegiatan')
                    ->label('Kegiatan')
                    ->options(function () {
                        return Kegiatan::all()
                            ->mapWithKeys(function ($kegiatan) {
                                return [$kegiatan->id => $kegiatan->kode_kegiatan . ' - ' . $kegiatan->nama_kegiatan];
                            });
                    })
                    ->searchable(),

                SelectFilter::make('program')
                    ->label('Program')
                    ->options(function () {
                        return Program::all()
                            ->mapWithKeys(function ($program) {
                                return [$program->id => $program->kode_program . ' - ' . $program->nama_program];
                            });
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $value): Builder => $query->whereHas('kegiatan.program', fn(Builder $query) => $query->where('id', $value))
                        );
                    })
                    ->searchable(),

                SelectFilter::make('organisasi')
                    ->label('Organisasi')
                    ->options(function () {
                        return Program::with('organisasi')
                            ->get()
                            ->pluck('organisasi.nama', 'organisasi.id')
                            ->filter()
                            ->unique();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $value): Builder => $query->whereHas('kegiatan.program.organisasi', fn(Builder $query) => $query->where('id', $value))
                        );
                    })
                    ->searchable(),

                Tables\Filters\Filter::make('serapan_rendah')
                    ->label('Serapan < 60%')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('(realisasi / NULLIF(anggaran, 0) * 100) < 60')),

                Tables\Filters\Filter::make('serapan_tinggi')
                    ->label('Serapan ≥ 80%')
                    ->query(fn(Builder $query): Builder => $query->whereRaw('(realisasi / NULLIF(anggaran, 0) * 100) >= 80')),
            ])
            ->actions([
                ViewAction::make()
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->label('')
                    ->tooltip('Detail'),
                EditAction::make()
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->label('')
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->tooltip('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kode_sub_kegiatan')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            // Add relation managers if needed
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSubKegiatans::route('/'),
            'create' => Pages\CreateSubKegiatan::route('/create'),
            'view' => Pages\ViewSubKegiatan::route('/{record}'),
            'edit' => Pages\EditSubKegiatan::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_sub_kegiatan',
            'nama_sub_kegiatan',
            'kegiatan.kode_kegiatan',
            'kegiatan.nama_kegiatan'
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kegiatan' => optional($record->kegiatan)->kode_kegiatan . ' - ' . optional($record->kegiatan)->nama_kegiatan,
            'Program' => optional($record->kegiatan->program)->kode_program . ' - ' . optional($record->kegiatan->program)->nama_program,
            'Organisasi' => optional($record->kegiatan->program->organisasi)->nama,
        ];
    }
}
