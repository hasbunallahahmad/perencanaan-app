<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BidangResource\Pages;
use App\Models\Bidang;
use App\Models\Organisasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class BidangResource extends Resource
{
    protected static ?string $model = Bidang::class;
    // protected static ?string $navigationIcon = 'heroicon-o-squares-2x2';
    protected static ?string $navigationGroup = 'Pengguna dan SOTK';
    protected static ?string $modelLabel = 'Bidang';
    protected static ?string $pluralModelLabel = 'Bidang';
    protected static ?int $navigationSort = 3;
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['organisasi:id,nama,aktif'])
            ->select([
                'id',
                'nama',
                'kode',
                'is_sekretariat',
                'aktif',
                'organisasi_id',
                'deskripsi',
                'created_at',
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organisasi_id')
                    ->label('Organisasi')
                    ->options(fn() => self::getCachedOrganisasi())
                    ->required()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama Bidang/Sekretariat'),
                Forms\Components\TextInput::make('kode')
                    ->maxLength(50)
                    ->placeholder('Kode Unit (opsional)'),
                Forms\Components\Toggle::make('is_sekretariat')
                    ->label('Unit Sekretariat')
                    ->helperText('Centang jika ini adalah unit sekretariat'),
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
                Tables\Columns\TextColumn::make('kode')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('jenis_unit')
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
                Tables\Filters\SelectFilter::make('organisasi_id')
                    ->label('Organisasi')
                    ->options(fn() => self::getCachedOrganisasi()),
                Tables\Filters\TernaryFilter::make('is_sekretariat')
                    ->label('Unit Sekretariat'),
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
            ->defaultSort('is_sekretariat', 'desc')
            ->deferLoading()
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBidang::route('/'),
            'create' => Pages\CreateBidang::route('/create'),
            'edit' => Pages\EditBidang::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('bidang_count', 600, function () {
            return static::getModel()::count();
        });
    }
    private static function getCachedOrganisasi(): array
    {
        return Cache::remember('organisasi_options', 3600, function () {
            return Organisasi::where('aktif', true)
                ->select(['id', 'nama'])
                ->orderBy('nama')
                ->pluck('nama', 'id')
                ->toArray();
        });
    }
}
