<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SubKegiatanResource\Pages;
use App\Filament\Resources\SubKegiatanResource\RelationManagers;
use App\Models\SubKegiatan;
use App\Models\Kegiatan;
use App\Models\MasterIndikator;
use App\Models\Program;
use App\Services\YearContext;
use App\Traits\HasYearFilter;
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
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\Grid;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;
use Spatie\Permission\Traits\HasRoles;

class SubKegiatanResource extends BaseResource
{
    use HasYearFilter;
    protected static ?string $model = SubKegiatan::class;

    // protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Sub Kegiatan';
    protected static ?string $pluralLabel = 'Sub Kegiatan';
    protected static ?string $pluralModelLabel = 'Sub Kegiatan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 3;
    protected static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('tahun', YearContext::getActiveYear());
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tahun', YearContext::getActiveYear());
    }
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Informasi Dasar Sub Kegiatan')
                    ->description('Data dasar sub kegiatan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('kode_sub_kegiatan')
                                    ->label('Kode Sub Kegiatan')
                                    ->icon('heroicon-o-hashtag')
                                    ->copyable(),

                                TextEntry::make('kegiatan.nama_kegiatan')
                                    ->label('Kegiatan')
                                    ->icon('heroicon-o-folder-open'),
                            ]),

                        TextEntry::make('nama_sub_kegiatan')
                            ->label('Nama Sub Kegiatan')
                            ->icon('heroicon-o-document-text')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Indikator & Sumber Dana')
                    ->description('Indikator kinerja dan sumber pendanaan')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Grid::make(2)
                            ->schema([
                                TextEntry::make('indikator.nama_indikator')
                                    ->label('Indikator Sub Kegiatan')
                                    ->icon('heroicon-o-chart-pie')
                                    ->placeholder('-'),

                                TextEntry::make('sumber_dana')
                                    ->label('Sumber Dana')
                                    ->icon('heroicon-o-currency-dollar')
                                    ->getStateUsing(function ($record) {
                                        $sumberDana = $record->sumber_dana;
                                        if (is_array($sumberDana) && !empty($sumberDana)) {
                                            return implode(', ', array_values(array_unique(array_filter($sumberDana))));
                                        }
                                        return '-';
                                    })
                                    ->badge()
                                    ->separator(','),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Section::make('Anggaran & Realisasi')
                    ->description('Data keuangan sub kegiatan')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Grid::make(3)
                            ->schema([
                                TextEntry::make('anggaran')
                                    ->label('Anggaran')
                                    ->icon('heroicon-o-wallet')
                                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                                TextEntry::make('realisasi')
                                    ->label('Realisasi')
                                    ->icon('heroicon-o-check-circle')
                                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                                TextEntry::make('serapan')
                                    ->label('Persentase Serapan')
                                    ->getStateUsing(function ($record): string {
                                        if ($record->anggaran == 0) {
                                            return '0%';
                                        }
                                        $serapan = ($record->realisasi / $record->anggaran) * 100;
                                        return number_format($serapan, 2) . '%';
                                    })
                                    ->badge()
                                    ->color(function ($record) {
                                        if ($record->anggaran == 0) {
                                            return 'gray';
                                        }
                                        $serapan = ($record->realisasi / $record->anggaran) * 100;
                                        return match (true) {
                                            $serapan >= 80 => 'success',
                                            $serapan >= 60 => 'warning',
                                            $serapan >= 40 => 'info',
                                            default => 'danger',
                                        };
                                    }),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->columns(1);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tahun')
                    ->default(YearContext::getActiveYear()),

                Forms\Components\Section::make('Informasi Dasar Sub Kegiatan')
                    ->description('Masukkan data dasar sub kegiatan')
                    ->icon('heroicon-o-information-circle')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                TextInput::make('kode_sub_kegiatan')
                                    ->label('Kode Sub Kegiatan')
                                    ->required()
                                    ->maxLength(50)
                                    ->unique(
                                        table: 'sub_kegiatan',
                                        column: 'kode_sub_kegiatan',
                                        ignoreRecord: true,
                                        modifyRuleUsing: function (Unique $rule) {
                                            return $rule->where('tahun', YearContext::getActiveYear());
                                        }
                                    )
                                    ->placeholder('Contoh: 2.08.01.2.01.0001')
                                    ->prefixIcon('heroicon-o-hashtag')
                                    ->columnSpan(1),

                                Select::make('id_kegiatan')
                                    ->label('Kegiatan')
                                    ->options(function () {
                                        return Kegiatan::where('tahun', YearContext::getActiveYear())
                                            ->pluck('nama_kegiatan', 'id_kegiatan');
                                    })
                                    ->required()
                                    ->searchable()
                                    ->preload()
                                    ->placeholder('Pilih kegiatan untuk sub kegiatan ini')
                                    ->prefixIcon('heroicon-o-folder-open')
                                    ->getOptionLabelFromRecordUsing(fn(Kegiatan $record) => "{$record->kode_kegiatan} - {$record->nama_kegiatan}")
                                    ->columnSpan(1),
                            ]),

                        TextInput::make('nama_sub_kegiatan')
                            ->label('Nama Sub Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama sub kegiatan')
                            ->prefixIcon('heroicon-o-document-text')
                            ->columnSpanFull(),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Indikator & Sumber Dana')
                    ->description('Atur indikator kinerja dan sumber pendanaan')
                    ->icon('heroicon-o-chart-bar')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('indikator_id')
                                    ->label('Indikator Sub Kegiatan')
                                    ->relationship('indikator', 'nama_indikator')
                                    ->searchable()
                                    ->preload()
                                    ->nullable()
                                    ->placeholder('Pilih indikator untuk sub kegiatan ini')
                                    ->helperText('Indikator yang akan digunakan untuk mengukur kinerja sub kegiatan')
                                    ->prefixIcon('heroicon-o-chart-pie')
                                    ->createOptionForm([
                                        TextInput::make('nama_indikator')
                                            ->label('Nama Indikator')
                                            ->required()
                                            ->maxLength(255)
                                            ->placeholder('Masukkan nama indikator baru'),
                                    ])
                                    ->createOptionUsing(function (array $data) {
                                        $indikator = MasterIndikator::create($data);
                                        return $indikator->id;
                                    })
                                    ->editOptionForm([
                                        TextInput::make('nama_indikator')
                                            ->label('Nama Indikator')
                                            ->required()
                                            ->maxLength(255),
                                    ])
                                    ->columnSpan(1),

                                Select::make('sumber_dana')
                                    ->label('Sumber Dana')
                                    ->multiple()
                                    ->options([
                                        'APBD' => 'APBD',
                                        'BANKEU' => 'BANKEU',
                                        'APBN' => 'APBN',
                                        'DBHCHT' => 'DBHCHT',
                                        'DAK' => 'DAK',
                                    ])
                                    ->required()
                                    ->searchable()
                                    ->helperText('Pilih satu atau lebih sumber dana')
                                    ->prefixIcon('heroicon-o-currency-dollar')
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),

                Forms\Components\Section::make('Anggaran & Realisasi')
                    ->description('Masukkan data keuangan sub kegiatan')
                    ->icon('heroicon-o-banknotes')
                    ->schema([
                        Forms\Components\Grid::make(3)
                            ->schema([
                                TextInput::make('anggaran')
                                    ->label('Anggaran')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->step(1)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    // ->deformatStateUsing(fn($state): ?int => $state ? (int) str_replace(['.', ',', 'Rp', ' '], '', $state) : 0)
                                    ->formatStateUsing(fn($state): string => $state ? number_format($state, 0, ',', '.') : '0')
                                    ->prefixIcon('heroicon-o-wallet')
                                    ->columnSpan(1),

                                TextInput::make('realisasi')
                                    ->label('Realisasi')
                                    ->numeric()
                                    ->default(0)
                                    ->prefix('Rp')
                                    ->placeholder('0')
                                    ->step(1)
                                    ->minValue(0)
                                    ->live(onBlur: true)
                                    // ->deformatStateUsing(fn($state): ?int => $state ? (int) str_replace(['.', ',', 'Rp', ' '], '', $state) : 0)
                                    ->formatStateUsing(fn($state): string => $state ? number_format($state, 0, ',', '.') : '0')
                                    ->prefixIcon('heroicon-o-check-circle')
                                    ->columnSpan(1),

                                Forms\Components\Placeholder::make('serapan_preview')
                                    ->label('Persentase Serapan')
                                    ->content(function (Forms\Get $get): string {
                                        $anggaran = (float) str_replace(['.', ','], '', $get('anggaran') ?? '0');
                                        $realisasi = (float) str_replace(['.', ','], '', $get('realisasi') ?? '0');

                                        if ($anggaran == 0) {
                                            return '0%';
                                        }

                                        $serapan = ($realisasi / $anggaran) * 100;
                                        return number_format($serapan, 2) . '%';
                                    })
                                    ->live()
                                    ->columnSpan(1),
                            ]),
                    ])
                    ->columns(1)
                    ->collapsible(),
            ])
            ->columns(1);
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
                TextColumn::make('indikator.nama_indikator')
                    ->label('Indikator')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: false),
                TextColumn::make('sumber_dana')
                    ->label('Sumber Dana')
                    ->getStateUsing(function ($record) {
                        $sumberDana = $record->sumber_dana;
                        if (is_array($sumberDana) && !empty($sumberDana)) {
                            return array_values(array_unique(array_filter($sumberDana)));
                        }
                    })
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'APBD' => 'success',
                        'BANKEU' => 'info',
                        'APBN' => 'warning',
                        'DBHCHT' => 'gray',
                        'DAK' => 'danger',
                        default => 'gray',
                    })
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
                SelectFilter::make('indikator_id')
                    ->label('Indikator')
                    ->options(function () {
                        return MasterIndikator::all()
                            ->mapWithKeys(function ($kegiatan) {
                                return [$kegiatan->id_kegiatan => $kegiatan->kode_kegiatan . ' - ' . $kegiatan->nama_kegiatan];
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

                // Filter berdasarkan sumber dana (JSON)
                SelectFilter::make('sumber_dana')
                    ->label('Sumber Dana')
                    ->options([
                        'APBD' => 'APBD',
                        'BANKEU' => 'BANKEU',
                        'APBN' => 'APBN',
                        'DBHCHT' => 'DBHCHT',
                        'DAK' => 'DAK',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'] ?? null,
                            fn(Builder $query, $value): Builder => $query->whereJsonContains('sumber_dana', $value)
                        );
                    }),

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
                    ->hidden(fn() => Auth::user()->hasRole('panel_user'))
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->hidden(fn() => Auth::user()->hasRole('panel_user'))
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

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_sub_kegiatan',
            'nama_sub_kegiatan',
            'kegiatan.kode_kegiatan',
            'kegiatan.nama_kegiatan',
            'indikator.nama_indikator'
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Kegiatan' => optional($record->kegiatan)->kode_kegiatan . ' - ' . optional($record->kegiatan)->nama_kegiatan,
            'Program' => optional($record->kegiatan->program)->kode_program . ' - ' . optional($record->kegiatan->program)->nama_program,
            'Organisasi' => optional($record->kegiatan->program->organisasi)->nama,
            'Indikator' => optional($record->indikator)->nama_indikator,
        ];
    }
}
