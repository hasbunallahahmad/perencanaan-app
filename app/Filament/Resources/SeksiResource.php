<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeksiResource\Pages;
use App\Models\Seksi;
use App\Models\Bidang;
use App\Models\Organisasi;
use Faker\Core\Color;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class SeksiResource extends Resource
{
    protected static ?string $model = Seksi::class;
    // protected static ?string $navigationIcon = 'heroicon-o-rectangle-group';
    protected static ?string $navigationGroup = 'Pengguna dan SOTK';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Seksi';
    protected static ?string $pluralModelLabel = 'Seksi';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['bidang.organisasi']);
    }
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organisasi_id')
                    ->label('Organisasi')
                    ->options(Organisasi::where('aktif', true)->pluck('nama', 'id'))
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn(Forms\Set $set) => $set('bidang_id', null))
                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                        // Untuk form edit, set organisasi_id berdasarkan bidang yang dipilih
                        if ($record && $record->bidang) {
                            $component->state($record->bidang->organisasi_id);
                        }
                    }),

                Forms\Components\Select::make('bidang_id')
                    ->label('Bidang/Sekretariat')
                    ->options(function (Get $get, $record): array {
                        $organisasiId = $get('organisasi_id');

                        // Jika form edit dan belum ada organisasi_id yang dipilih, ambil dari record
                        if (!$organisasiId && $record && $record->bidang) {
                            $organisasiId = $record->bidang->organisasi_id;
                        }

                        if (!$organisasiId) {
                            return [];
                        }

                        return Bidang::where('organisasi_id', $organisasiId)
                            ->where('aktif', true)
                            ->pluck('nama', 'id')
                            ->toArray();
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Forms\Set $set) {
                        $bidang = Bidang::find($get('bidang_id'));
                        if ($bidang) {
                            $set('jenis', $bidang->is_sekretariat ? 'subbagian' : 'seksi');
                        }
                    }),

                Forms\Components\Select::make('jenis')
                    ->label('Jenis Unit')
                    ->options([
                        'seksi' => 'Seksi',
                        'subbagian' => 'Subbagian',
                    ])
                    ->required()
                    ->live(),

                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama Seksi/Subbagian'),

                Forms\Components\TextInput::make('kode')
                    ->maxLength(50)
                    ->placeholder('Kode Unit (opsional)'),

                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull()
                    ->placeholder('Deskripsi tugas dan fungsi'),

                Forms\Components\Toggle::make('aktif')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang/Sekretariat')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('jenis_label')
                    ->badge()
                    ->label('Jenis')
                    ->colors([
                        'primary' => 'Subbagian',
                        'success' => 'Seksi',
                    ]),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang/Sekretariat')
                    ->options(Bidang::with('organisasi')->get()->mapWithKeys(function ($bidang) {
                        return [$bidang->id => $bidang->organisasi->nama . ' - ' . $bidang->nama];
                    })),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'seksi' => 'Seksi',
                        'subbagian' => 'Subbagian',
                    ]),
                Tables\Filters\TernaryFilter::make('aktif'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->icon('heroicon-s-pencil-square')
                    ->color('danger')
                    ->label('')
                    ->tooltip('Edit'),
                Tables\Actions\ViewAction::make()
                    ->icon('heroicon-s-eye')
                    ->color('info')
                    ->label('')
                    ->tooltip('View'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('bidang_id');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeksis::route('/'),
            'create' => Pages\CreateSeksi::route('/create'),
            'edit' => Pages\EditSeksi::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

    protected static function getCachedOrganisasi(): array
    {
        return Cache::remember('organisasi_aktif', 300, function () {
            return Organisasi::aktif(true)->pluck('nama', 'id')->toArray();
        });
    }

    protected static function getCachedBidangWithOrganisasi(): array
    {
        return Cache::remember('bidang_with_organisasi', 300, function () {
            return Bidang::with('organisasi')
                ->get()
                ->mapWithKeys(function ($bidang) {
                    return [$bidang->id => $bidang->organisasi->nama . ' - ' . $bidang->nama];
                })
                ->toArray();
        });
    }
}
