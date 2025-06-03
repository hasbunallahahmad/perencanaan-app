<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgramResource\Pages;
use App\Models\Program;
use App\Models\Organisasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ProgramResource extends Resource
{
    protected static ?string $model = Program::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationLabel = 'Program';

    protected static ?string $modelLabel = 'Program';

    protected static ?string $pluralModelLabel = 'Program';

    protected static ?string $navigationGroup = 'Master Data';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Program')
                    ->description('Masukkan detail program')
                    ->schema([
                        TextInput::make('kode_program')
                            ->label('Kode Program')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('Contoh: 2.08.01')
                            ->helperText('Format: X.XX.XX'),

                        TextInput::make('nama_program')
                            ->label('Nama Program')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Masukkan nama program'),

                        TextInput::make('anggaran')
                            ->label('Jumlah Anggaran')
                            ->required()
                            ->maxLength(500)
                            ->columnSpanFull()
                            ->placeholder('Masukkan Total Anggaran'),

                        Select::make('organisasi_id')
                            ->label('Organisasi')
                            ->relationship('organisasi', 'nama')
                            ->required()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('nama')
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('kode')
                                    ->maxLength(50),
                                Textarea::make('alamat')
                                    ->maxLength(500),
                            ])
                            ->helperText('Pilih organisasi yang mengelola program ini'),

                        Textarea::make('deskripsi')
                            ->label('Deskripsi Program')
                            ->maxLength(1000)
                            ->columnSpanFull()
                            ->placeholder('Deskripsi singkat tentang program (opsional)')
                            ->rows(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('kode_program')
                    ->label('Kode Program')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage('Kode program berhasil disalin!')
                    ->weight('medium'),

                TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->limit(80)
                    ->tooltip(function (TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 80 ? $state : null;
                    }),

                TextColumn::make('anggaran')
                    ->label('Anggaran')
                    ->searchable()
                    ->sortable()
                    ->limit(50)
                    ->badge(),

                TextColumn::make('kegiatan_count')
                    ->label('Jumlah Kegiatan')
                    ->counts('kegiatan')
                    ->alignCenter()
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d M Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('organisasi')
                    ->relationship('organisasi', 'nama')
                    ->searchable()
                    ->preload()
                    ->multiple(),

                Tables\Filters\Filter::make('has_kegiatan')
                    ->label('Memiliki Kegiatan')
                    ->query(fn(Builder $query): Builder => $query->has('kegiatan'))
                    ->toggle(),

                Tables\Filters\Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('created_from')
                            ->label('Dibuat dari tanggal'),
                        Forms\Components\DatePicker::make('created_until')
                            ->label('Dibuat sampai tanggal'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['created_from'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['created_until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
            ])
            ->actions([
                ViewAction::make()
                    ->color('info'),
                EditAction::make()
                    ->color('warning'),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Hapus Program')
                    ->modalDescription('Apakah Anda yakin ingin menghapus program ini? Data kegiatan yang terkait juga akan terhapus.')
                    ->modalSubmitActionLabel('Ya, Hapus'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Hapus Program Terpilih')
                        ->modalDescription('Apakah Anda yakin ingin menghapus program yang dipilih? Data kegiatan yang terkait juga akan terhapus.')
                        ->modalSubmitActionLabel('Ya, Hapus Semua'),
                ]),
            ])
            ->defaultSort('kode_program', 'asc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->poll('30s');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Detail Program')
                    ->schema([
                        TextEntry::make('kode_program')
                            ->label('Kode Program')
                            ->copyable()
                            ->copyMessage('Kode program berhasil disalin!')
                            ->badge()
                            ->color('primary'),

                        TextEntry::make('nama_program')
                            ->label('Nama Program')
                            ->columnSpanFull(),

                        TextEntry::make('organisasi.nama')
                            ->label('Organisasi')
                            ->badge()
                            ->color('success'),

                        TextEntry::make('deskripsi')
                            ->label('Deskripsi')
                            ->columnSpanFull()
                            ->placeholder('Tidak ada deskripsi'),
                    ])
                    ->columns(2),

                Section::make('Statistik')
                    ->schema([
                        TextEntry::make('kegiatan_count')
                            ->label('Total Kegiatan')
                            ->state(function (Program $record): int {
                                return $record->kegiatan()->count();
                            })
                            ->badge()
                            ->color('info'),

                        TextEntry::make('sub_kegiatan_count')
                            ->label('Total Sub Kegiatan')
                            ->state(function (Program $record): int {
                                return $record->kegiatan()->withCount('subKegiatan')->get()->sum('sub_kegiatan_count');
                            })
                            ->badge()
                            ->color('warning'),

                        TextEntry::make('created_at')
                            ->label('Dibuat')
                            ->dateTime('d F Y, H:i')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('updated_at')
                            ->label('Terakhir Diperbarui')
                            ->dateTime('d F Y, H:i')
                            ->badge()
                            ->color('gray'),
                    ])
                    ->columns(2),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPrograms::route('/'),
            'create' => Pages\CreateProgram::route('/create'),
            'view' => Pages\ViewProgram::route('/{record}'),
            'edit' => Pages\EditProgram::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        try {
            return static::getModel()::count();
        } catch (\Illuminate\Database\QueryException $e) {
            return static::getModel()::withoutGlobalScopes()->count();
        }
    }

    public static function getNavigationBadgeColor(): string|array|null
    {
        try {
            $count = static::getModel()::count();
            return $count > 10 ? 'success' : 'primary';
        } catch (\Illuminate\Database\QueryException $e) {
            $count = static::getModel()::withoutGlobalScopes()->count();
            return $count > 10 ? 'success' : 'primary';
        }
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['organisasi']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['kode_program', 'nama_program', 'organisasi.nama'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Organisasi' => $record->organisasi?->nama,
            'Kode' => $record->kode_program,
        ];
    }
}
