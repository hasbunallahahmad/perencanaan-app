<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrganisasiResource\Pages;
use App\Models\Organisasi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class OrganisasiResource extends Resource
{
    protected static ?string $model = Organisasi::class;
    protected static ?string $navigationGroup = 'Pengguna dan SOTK';
    protected static ?string $modelLabel = 'Organisasi';
    protected static ?string $pluralModelLabel = 'Organisasi';
    protected static ?int $navigationSort = 2;
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }
    /*************  ✨ Windsurf Command ⭐  *************/
    /**
     * {@inheritdoc}
     *
     * Scope query untuk table organisasi.
     * Menggunakan scope `withAllCounts` untuk menghitung jumlah bidang, seksi, dan user.
     * Hanya select field yang diperlukan untuk table.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withAllCounts() // Menggunakan scope dari model
            ->select([
                'id',
                'nama',
                'kota',
                'aktif',
                'created_at'
                // Hanya select field yang diperlukan untuk table
            ]);
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Nama Organisasi'),
                Forms\Components\TextInput::make('kota')
                    ->maxLength(100)
                    ->placeholder('Kota/Kabupaten'),
                Forms\Components\Textarea::make('deskripsi')
                    ->columnSpanFull()
                    ->placeholder('Deskripsi organisasi'),
                Forms\Components\Textarea::make('alamat')
                    ->columnSpanFull()
                    ->placeholder('Alamat lengkap'),
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
                Tables\Columns\TextColumn::make('kota')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('bidangs_count')
                    ->counts('bidangs')
                    ->label('Total Unit'),
                Tables\Columns\TextColumn::make('sekretariat_count')
                    ->counts('sekretariat')
                    ->label('Sekretariat'),
                Tables\Columns\TextColumn::make('users_count')
                    ->counts('users')
                    ->label('Jumlah Pegawai'),
                Tables\Columns\IconColumn::make('aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
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
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->paginated([10, 25, 50, 100])
            ->deferLoading();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrganisasis::route('/'),
            'create' => Pages\CreateOrganisasi::route('/create'),
            'edit' => Pages\EditOrganisasi::route('/{record}/edit'),
        ];
    }
    public static function getNavigationBadge(): ?string
    {
        return Cache::remember(
            'organisasi_navigation_count',
            now()->addMinutes(10),
            fn() => number_format(static::getModel()::count())
        );
    }
}
