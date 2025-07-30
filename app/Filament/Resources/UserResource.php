<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Bidang;
use App\Models\Organisasi;
use App\Models\Seksi;
use App\Models\User;
use App\Services\CacheService;
use DateTime;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\ArrayRule;
use Spatie\Permission\Models\Role;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    // protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Pengguna dan SOTK';
    protected static ?int $navigationSort = 1;
    protected static ?string $modelLabel = 'User';
    protected static ?string $pluralModelLabel = 'User';
    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with([
                'roles:id,name',
                'permissions:id,name',
                'organisasi:id,nama',
                'bidang:id,nama,organisasi_id',
                'seksi:id,nama,bidang_id'
            ])
            ->select([
                'users.id',
                'users.name',
                'users.email',
                'users.email_verified_at',
                'users.organisasi_id',
                'users.bidang_id',
                'users.seksi_id',
                'users.created_at',
                'users.updated_at'
            ]);
    }
    // protected static function getCachedOrganisasi(): array
    // {
    //     return Cache::remember('organisasi_options_v2', 3600, function () {
    //         return Organisasi::where('aktif', true)
    //             ->orderBy('nama')
    //             ->pluck('nama', 'id')
    //             ->toArray();
    //     });
    // }

    // protected static function getCachedBidangByOrganisasi(?int $organisasiId)
    // {
    //     if (!$organisasiId) {
    //         return [];
    //     }
    //     return Cache::remember("bidang_by_organisasi_v2_{$organisasiId}", 3600, function () use ($organisasiId) {
    //         return Bidang::where('organisasi_id', $organisasiId)
    //             ->where('aktif', true)
    //             ->orderBy('nama')
    //             ->pluck('nama', 'id')
    //             ->toArray();
    //     });
    // }
    // protected static function getCachedSeksiByBidang(?int $bidangId)
    // {
    //     if (!$bidangId) {
    //         return [];
    //     }

    //     return Cache::remember("seksi_by_bidang_v2_{$bidangId}", 3600, function () use ($bidangId) {
    //         return Seksi::where('bidang_id', $bidangId)
    //             ->where('aktif', true)
    //             ->orderBy('nama')
    //             ->pluck('nama', 'id')
    //             ->toArray();
    //     });
    // }
    // protected static function getCachedRoles(): array
    // {
    //     return Cache::remember('roles_options_v2', 3600, function () {
    //         return Role::orderBy('name')
    //             ->pluck('name', 'name')
    //             ->toArray();
    //     });
    // }
    // protected static function getCachedFormData(): array
    // {
    //     return Cache::remember('user_form_data_v2', 3600, function () {
    //         return [
    //             'organisasi' => Organisasi::where('aktif', true)->orderBy('nama')->get(['id', 'nama']),
    //             'bidang' => Bidang::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'organisasi_id']),
    //             'seksi' => Seksi::where('aktif', true)->orderBy('nama')->get(['id', 'nama', 'bidang_id']),
    //             'roles' => Role::orderBy('name')->pluck('name', 'name')->toArray()
    //         ];
    //     });
    // }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('User Information')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('email')
                            ->email()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                        Forms\Components\DatePicker::make('email_verified_at')
                            ->label('Email Verified At'),
                        Forms\Components\TextInput::make('password')
                            ->password()
                            ->revealable()
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context) => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255),
                        Forms\Components\Select::make('organisasi_id')
                            ->label('Organisasi')
                            ->options(CacheService::getOrganisasiAktif())
                            ->searchable()
                            ->preload()
                            ->live(onBlur: true) // Only trigger on blur, not every keystroke
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('bidang_id', null);
                                $set('seksi_id', null);
                            }),
                        Forms\Components\Select::make('bidang_id')
                            ->label('Bidang/Sekretariat')
                            ->options(function (Get $get): array {
                                $organisasiId = $get('organisasi_id');
                                return $organisasiId ? CacheService::getBidangByOrganisasi($organisasiId) : [];
                            })
                            ->searchable()
                            ->preload()
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('seksi_id', null)),
                        Forms\Components\Select::make('seksi_id')
                            ->label('Seksi/Subbagian')
                            ->options(function (Get $get): array {
                                $bidangId = $get('bidang_id');
                                return $bidangId ? CacheService::getSeksiByBidang($bidangId) : [];
                            })
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->label('Roles')
                            ->options(CacheService::getRoles())
                            ->preload()
                            ->searchable()
                            ->helperText('Anda Dapat Memilih Lebih Dari Satu Role')
                            ->default(function ($record) {
                                return $record ? $record->roles->pluck('name')->toArray() : [];
                            })
                            ->dehydrated(false),
                        Forms\Components\Select::make('permissions')
                            ->label('Direct Permissions')
                            ->options(CacheService::getPermissions())
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->helperText('Anda Dapat Memilih Lebih Dari Satu Permission')
                            ->default(function ($record) {
                                return $record ? $record->permissions->pluck('name')->toArray() : [];
                            })
                            ->dehydrated(false),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->label('nama')
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles_display')
                    ->label('Roles')
                    ->badge()
                    ->separator(',')
                    ->color('success')
                    ->state(function (User $record): string {
                        return $record->roles->pluck('name')->join(', ');
                    }),
                Tables\Columns\TextColumn::make('organisasi.nama')
                    ->label('Organisasi')
                    ->sortable()
                    ->state(fn(User $record): ?string => $record->organisasi?->nama)
                    ->wrap()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->state(fn(User $record): ?string => $record->bidang?->nama)
                    ->wrap()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->label('Verified'),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
                Tables\Filters\Filter::make('verified')
                    ->query(fn(Builder $query): Builder => $query->whereNotNull('email_verified_at'))
                    ->label('Verified Users'),
                Tables\Filters\Filter::make('unverified')
                    ->query(fn(Builder $query): Builder => $query->whereNull('email_verified_at'))
                    ->label('Unverified Users'),
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
                        ->requiresConfirmation(),
                    Tables\Actions\BulkAction::make('verify_email')
                        ->label('Verify Email')
                        ->icon('heroicon-o-check-badge')
                        ->color('success')
                        ->action(function ($records) {
                            User::whereIn('id', $records->pluck('id'))
                                ->update(['email_verified_at' => now()]);
                        })
                        ->requiresConfirmation()
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('assign_role')
                        ->label('Assign Role')
                        ->icon('heroicon-o-user-plus')
                        ->color('warning')
                        ->form([
                            Forms\Components\Select::make('role')
                                ->label('Role')
                                ->options(fn() => self::getCachedRoles())
                                ->required(),
                        ])
                        ->action(function (array $data, $records) {
                            foreach ($records as $record) {
                                $record->assignRole($data['role']);
                            }
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->deferLoading(); // Add this to improve initial load performance
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
    // public static function getNavigationBadge(): ?string
    // {
    //     return Cache::remember('user_count_v2', 3600, function () {
    //         return static::getModel()::count();
    //     });
    // }
}
