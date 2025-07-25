<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanResource\Pages;
use App\Filament\Resources\KegiatanResource\RelationManagers;
use App\Models\Kegiatan;
use App\Models\Program;
use App\Models\MasterIndikator; // Tambahkan import ini
use App\Services\YearContext;
use App\Traits\HasYearFilter;
use Filament\Forms;
use Filament\Forms\Components\Section;
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
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Unique;

class KegiatanResource extends BaseResource
{
    protected static ?string $model = Kegiatan::class;
    protected static ?string $navigationLabel = 'Kegiatan';
    protected static ?string $pluralLabel = 'Kegiatan';
    protected static ?string $pluralModelLabel = 'Kegiatan';
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?int $navigationSort = 2;
    use HasYearFilter;
    protected static function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('tahun', YearContext::getActiveYear());
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('tahun', YearContext::getActiveYear());
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('tahun')
                    ->default(YearContext::getActiveYear()),

                Forms\Components\Section::make('Data Kegiatan')
                    ->schema([
                        TextInput::make('kode_kegiatan')
                            ->label('Kode Kegiatan')
                            ->required()
                            ->maxLength(50)
                            ->unique(
                                table: 'kegiatan',
                                column: 'kode_kegiatan',
                                ignoreRecord: true,
                                modifyRuleUsing: function (Unique $rule) {
                                    return $rule->where('tahun', YearContext::getActiveYear());
                                }
                            )
                            ->placeholder('Contoh: 2.08.01.2.01'),

                        TextInput::make('nama_kegiatan')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama kegiatan'),

                        Select::make('id_program')
                            ->label('Program')
                            ->options(function () {
                                return Program::where('tahun', YearContext::getActiveYear())
                                    ->pluck('nama_program', 'id_program');
                            })
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih program')
                            ->getOptionLabelFromRecordUsing(fn(Program $record) => "{$record->kode_program} - {$record->nama_program}"),

                        Select::make('indikator_id')
                            ->label('Indikator Kegiatan')
                            ->relationship('indikator', 'nama_indikator')
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->placeholder('Pilih indikator untuk kegiatan ini')
                            ->helperText('Indikator yang akan digunakan untuk mengukur kinerja kegiatan')
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
                            ->columnSpan(2),
                    ])
                    ->columns(3),

                Section::make('Informasi Anggaran')
                    ->schema([
                        Forms\Components\Placeholder::make('anggaran_info')
                            ->label('Total Anggaran')
                            ->content(fn($record) => $record ? 'Rp ' . number_format($record->anggaran, 0, ',', '.') : 'Rp 0')
                            ->visible(fn($context) => $context === 'edit' || $context === 'view'),

                        Forms\Components\Placeholder::make('realisasi_info')
                            ->label('Total Realisasi')
                            ->content(fn($record) => $record ? 'Rp ' . number_format($record->realisasi, 0, ',', '.') : 'Rp 0')
                            ->visible(fn($context) => $context === 'edit' || $context === 'view'),

                        Forms\Components\Placeholder::make('persentase_info')
                            ->label('Persentase Serapan')
                            ->content(fn($record) => $record ? $record->persentase_serapan . '%' : '0%')
                            ->visible(fn($context) => $context === 'edit' || $context === 'view'),
                    ])
                    ->columns(3)
                    ->visible(fn($context) => $context === 'edit' || $context === 'view')
                    ->description('Anggaran dan realisasi dihitung otomatis dari sub kegiatan')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(
                fn(Builder $query) =>
                $query->withSum(['subKegiatan' => function ($query) {
                    $query->whereNull('deleted_at');
                }], 'anggaran')
                    ->withSum(['subKegiatan' => function ($query) {
                        $query->whereNull('deleted_at');
                    }], 'realisasi')
                    ->with(['program.organisasi', 'indikator'])
            )
            ->columns([
                TextColumn::make('kode_kegiatan')
                    ->label('Kode Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('medium'),

                TextColumn::make('nama_kegiatan')
                    ->label('Nama Kegiatan')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(100),

                // TextColumn::make('program.organisasi.nama')
                //     ->label('Organisasi')
                //     ->searchable()
                //     ->sortable()
                //     ->wrap()
                //     ->limit(30)
                //     ->toggleable(isToggledHiddenByDefault: true),

                // TextColumn::make('indikator.nama_indikator')
                //     ->label('Indikator')
                //     ->searchable()
                //     ->wrap()
                //     // ->limit(60)
                //     ->placeholder('Belum ada indikator')
                //     ->toggleable(isToggledHiddenByDefault: false),

                TextColumn::make('sub_kegiatan_sum_anggaran')
                    ->label('Total Anggaran')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('sub_kegiatan_sum_realisasi')
                    ->label('Total Realisasi')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd()
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state ?? 0, 0, ',', '.')),

                TextColumn::make('persentase_serapan')
                    // ->label('Serapan (%)')
                    // ->formatStateUsing(fn($state) => $state . '%')
                    // ->sortable()
                    // ->alignCenter()
                    // ->badge()
                    // ->color(fn($state) => match (true) {
                    //     $state >= 80 => 'success',
                    //     $state >= 60 => 'warning',
                    //     default => 'danger',
                    // }),
                    ->getStateUsing(function ($record) {
                        $anggaran = $record->sub_kegiatan_sum_anggaran ?? 0;
                        $realisasi = $record->sub_kegiatan_sum_realisasi ?? 0;

                        if ($anggaran > 0) {
                            return round(($realisasi / $anggaran) * 100, 2);
                        }
                        return 0;
                    })
                    ->formatStateUsing(fn($state) => $state . '%')
                    ->sortable()
                    ->alignCenter()
                    ->badge()
                    ->color(fn($state) => match (true) {
                        $state >= 80 => 'success',
                        $state >= 60 => 'warning',
                        default => 'danger',
                    }),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                // SelectFilter::make('id_program')
                //     ->label('Program')
                //     ->options(function () {
                //         return Program::where('tahun', YearContext::getActiveYear())
                //             ->pluck('nama_program', 'id_program');
                //     })
                //     ->searchable(),

                // SelectFilter::make('indikator_id')
                //     ->label('Indikator')
                //     ->options(MasterIndikator::all()->pluck('nama_indikator', 'id'))
                //     ->searchable(),

                SelectFilter::make('serapan')
                    ->label('Tingkat Serapan')
                    ->options([
                        'tinggi' => 'Tinggi (≥80%)',
                        'sedang' => 'Sedang (60-79%)',
                        'rendah' => 'Rendah (<60%)',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        return $query->when($value, function (Builder $query) use ($value) {
                            if ($value === 'tinggi') {
                                return $query->serapanTinggi(80);
                            } elseif ($value === 'sedang') {
                                return $query->whereHas('subKegiatan')
                                    ->havingRaw('
                                        (SELECT SUM(realisasi) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan) / 
                                        NULLIF((SELECT SUM(anggaran) FROM sub_kegiatan WHERE sub_kegiatan.id_kegiatan = kegiatan.id_kegiatan), 0) * 100 BETWEEN 60 AND 79.99
                                    ');
                            } elseif ($value === 'rendah') {
                                return $query->serapanRendah(60);
                            }
                        });
                    }),
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
                    ->hidden(fn() => Auth::user()
                        ->hasRole('panel_user'))
                    ->tooltip('Ubah'),
                DeleteAction::make()
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->label('')
                    ->hidden(fn() => Auth::user()
                        ->hasRole('panel_user'))
                    ->tooltip('Hapus'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('kode_kegiatan')
            ->striped()
            ->paginated([10, 25, 50, 100]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubKegiatansRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListKegiatans::route('/'),
            'create' => Pages\CreateKegiatan::route('/create'),
            'view' => Pages\ViewKegiatan::route('/{record}'),
            'edit' => Pages\EditKegiatan::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return [
            'kode_kegiatan',
            'nama_kegiatan',
            'program.kode_program',
            'program.nama_program',
            'indikator.nama_indikator' // Tambahkan indikator ke global search
        ];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Program' => optional($record->program)->kode_program . ' - ' . optional($record->program)->nama_program,
            'Organisasi' => optional($record->program->organisasi)->nama,
            'Indikator' => optional($record->indikator)->nama_indikator ?: 'Belum ada indikator',
        ];
    }
}
