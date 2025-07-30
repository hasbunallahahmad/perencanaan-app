<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SeksiResource\Pages;
use App\Models\Seksi;
use App\Models\Bidang;
use App\Services\CacheService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class SeksiResource extends Resource
{
    protected static ?string $model = Seksi::class;
    protected static ?string $navigationGroup = 'Pengguna dan SOTK';
    protected static ?int $navigationSort = 4;
    protected static ?string $modelLabel = 'Seksi';
    protected static ?string $pluralModelLabel = 'Seksi';
    private static $queryCache = null;
    private static $cacheTimeout = 300;

    public static function getEloquentQuery(): Builder
    {
        $cacheKey = 'seksi_query_' . Auth::id();

        // Hapus cache untuk query complex - tidak efektif untuk query builder
        return parent::getEloquentQuery()
            ->select([
                'seksis.id',
                'seksis.nama',
                'seksis.bidang_id',
                'seksis.jenis',
                'seksis.aktif',
                'seksis.created_at'
            ])
            ->with([
                'bidang' => function ($query) {
                    $query->select(['id', 'nama', 'organisasi_id', 'is_sekretariat'])
                        ->with(['organisasi' => function ($subQuery) {
                            $subQuery->select(['id', 'nama']);
                        }]);
                }
            ])
            ->orderBy('bidang_id')
            ->orderBy('nama');
    }

    public static function canAccess(): bool
    {
        $cacheKey = 'seksi_access_' . Auth::id();
        return Cache::remember($cacheKey, 300, function () {
            return Auth::user()?->hasRole('super_admin') ?? false;
        });
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('organisasi_id')
                    ->label('Organisasi')
                    ->options(function () {
                        try {
                            return CacheService::getOrganisasiAktif();
                        } catch (\Exception $e) {
                            Log::error('Error getting organisasi: ' . $e->getMessage());
                            return [];
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn(Set $set) => $set('bidang_id', null))
                    ->afterStateHydrated(function (Forms\Components\Select $component, $state, $record) {
                        if ($record && $record->bidang) {
                            $component->state($record->bidang->organisasi_id);
                        }
                    }),

                Forms\Components\Select::make('bidang_id')
                    ->label('Bidang/Sekretariat')
                    ->options(function (Get $get, $record = null): array {
                        $organisasiId = $get('organisasi_id');

                        if (!$organisasiId && $record && $record->bidang) {
                            $organisasiId = $record->bidang->organisasi_id;
                        }

                        if (!$organisasiId) {
                            return [];
                        }

                        try {
                            return CacheService::getBidangByOrganisasi($organisasiId);
                        } catch (\Exception $e) {
                            Log::error('Error getting bidang by organisasi: ' . $e->getMessage());
                            return [];
                        }
                    })
                    ->required()
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function (Get $get, Set $set) {
                        $bidangId = $get('bidang_id');
                        if ($bidangId) {
                            try {
                                $bidang = CacheService::getBidangDetail($bidangId);
                                if ($bidang && isset($bidang->is_sekretariat)) {
                                    $set('jenis', $bidang->is_sekretariat ? 'subbagian' : 'seksi');
                                }
                            } catch (\Exception $e) {
                                Log::error('Error getting bidang detail: ' . $e->getMessage());
                                // Fallback to default
                                $set('jenis', 'seksi');
                            }
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
                Tables\Columns\TextColumn::make('bidang.organisasi.nama')
                    ->label('Organisasi')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('bidang_id')
                    ->label('Bidang/Sekretariat')
                    ->options(function () {
                        try {
                            return CacheService::getBidangWithOrganisasi();
                        } catch (\Exception $e) {
                            Log::error('Error getting bidang with organisasi: ' . $e->getMessage());
                            return [];
                        }
                    })
                    ->searchable(),
                Tables\Filters\SelectFilter::make('jenis')
                    ->options([
                        'seksi' => 'Seksi',
                        'subbagian' => 'Subbagian',
                    ]),
                Tables\Filters\TernaryFilter::make('aktif'),
                Tables\Filters\SelectFilter::make('organisasi')
                    ->label('Organisasi')
                    ->options(function () {
                        try {
                            return CacheService::getOrganisasiAktif();
                        } catch (\Exception $e) {
                            Log::error('Error getting organisasi aktif: ' . $e->getMessage());
                            return [];
                        }
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn(Builder $query, $value): Builder => $query->whereHas(
                                'bidang.organisasi',
                                fn(Builder $query) => $query->where('id', $value)
                            )
                        );
                    })
                    ->searchable(),
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
                    Tables\Actions\DeleteBulkAction::make()
                        ->after(function () {
                            self::clearResourceCache();
                        }),
                ]),
            ])
            ->defaultSort('bidang_id')
            ->paginated([10, 25, 50, 100]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSeksis::route('/'),
            'create' => Pages\CreateSeksi::route('/create'),
            'edit' => Pages\EditSeksi::route('/{record}/edit'),
        ];
    }

    // public static function getNavigationBadge(): ?string
    // {
    //     try {
    //         $cacheKey = 'seksi_badge_count';
    //         return Cache::remember($cacheKey, 600, function () {
    //             return (string) Seksi::count();
    //         });
    //     } catch (\Exception $e) {
    //         Log::warning('Failed to get cached seksi count: ' . $e->getMessage());
    //         return null;
    //     }
    // }

    public static function clearResourceCache(): void
    {
        $userId = Auth::id();
        $cacheKeys = [
            'seksi_query_' . $userId,
            'seksi_access_' . $userId,
            'seksi_badge_count',
        ];

        foreach ($cacheKeys as $key) {
            Cache::forget($key);
        }

        try {
            CacheService::clearAllCaches();
        } catch (\Exception $e) {
            Log::warning('Failed to clear cache service: ' . $e->getMessage());
        }
    }

    public static function getEloquentQueryForBulkActions(): Builder
    {
        return parent::getEloquentQuery()
            ->select(['seksis.id', 'seksis.bidang_id'])
            ->with(['bidang:id,organisasi_id']);
    }

    public static function warmUpCache(): void
    {
        try {
            // Warm up cache dalam background
            dispatch(function () {
                $userId = Auth::id();

                // Cache query - simplified
                try {
                    $cacheKey = 'seksi_query_' . $userId;
                    if (!Cache::has($cacheKey)) {
                        Seksi::with(['bidang.organisasi'])->limit(10)->get();
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to warm up query cache: ' . $e->getMessage());
                }

                // Cache badge count
                try {
                    if (!Cache::has('seksi_badge_count')) {
                        Seksi::count();
                    }
                } catch (\Exception $e) {
                    Log::warning('Failed to warm up badge cache: ' . $e->getMessage());
                }

                // Cache service data
                try {
                    CacheService::getOrganisasiAktif();
                } catch (\Exception $e) {
                    Log::warning('Failed to warm up cache service: ' . $e->getMessage());
                }
            })->afterResponse();
        } catch (\Exception $e) {
            Log::warning('Failed to warm up cache: ' . $e->getMessage());
        }
    }
}
