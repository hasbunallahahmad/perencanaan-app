<?php

namespace App\Filament\Resources;

use App\Filament\Resources\KegiatanResource\Pages;
use App\Filament\Resources\KegiatanResource\RelationManagers;
use App\Models\Kegiatan;
use App\Models\Program;
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

class KegiatanResource extends BaseResource
{
    protected static ?string $model = Kegiatan::class;

    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

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
                            ->unique(ignoreRecord: true)
                            ->placeholder('Contoh: 2.08.01.2.01'),

                        TextInput::make('nama_kegiatan')
                            ->label('Nama Kegiatan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Masukkan nama kegiatan'),

                        Select::make('id_program')
                            ->label('Program')
                            ->options(function () {
                                return Program::with('organisasi')
                                    ->get()
                                    ->mapWithKeys(function ($program) {
                                        return [$program->id => $program->kode_program . ' - ' . $program->nama_program];
                                    });
                            })
                            ->required()
                            ->searchable()
                            ->placeholder('Pilih program')
                            ->getOptionLabelFromRecordUsing(fn(Program $record) => "{$record->kode_program} - {$record->nama_program}"),
                    ])
                    ->columns(2),
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


                TextColumn::make('program.organisasi.nama')
                    ->label('Organisasi')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('anggaran')
                    ->label('Total Anggaran')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('realisasi')
                    ->label('Total Realisasi')
                    ->money('IDR')
                    ->sortable()
                    ->alignEnd(),

                TextColumn::make('persentase_serapan')
                    ->label('Serapan (%)')
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
                SelectFilter::make('id_program')
                    ->label('Program')
                    ->options(Program::all()->pluck('nama_program', 'id'))
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
                            fn(Builder $query, $value): Builder => $query->whereHas('program.organisasi', fn(Builder $query) => $query->where('id', $value))
                        );
                    })
                    ->searchable(),
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

    // public static function getNavigationBadge(): ?string
    // {
    //     return static::getModel()::count();
    // }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_kegiatan', 'nama_kegiatan', 'program.kode_program', 'program.nama_program'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Program' => optional($record->program)->kode_program . ' - ' . optional($record->program)->nama_program,
            'Organisasi' => optional($record->program->organisasi)->nama,
        ];
    }
}
