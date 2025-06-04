<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\Bidang;
use App\Models\Organisasi;
use App\Models\Seksi;
use App\Models\User;
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

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Users Management';
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
                'roles',
                'permissions',
                'organisasi',
                'bidang',
                'seksi'
            ]);
    }
    protected static function getCachedOrganisasi(): array
    {
        return Cache::remember('organisasi_options', 300, function () {
            return Organisasi::where('aktif', true)
                ->orderBy('nama')
                ->pluck('nama', 'id')
                ->toArray();
        });
    }

    protected static function getCachedBidangByOrganisasi(?int $organisasiId)
    {
        if (!$organisasiId) {
            return collect();
        }

        return Cache::remember("bidang_by_organisasi_{$organisasiId}", 300, function () use ($organisasiId) {
            return Bidang::where('organisasi_id', $organisasiId)
                ->where('aktif', true)
                ->orderBy('nama')
                ->get();
        });
    }
    protected static function getCachedSeksiByBidang(?int $bidangId)
    {
        if (!$bidangId) {
            return collect();
        }

        return Cache::remember("seksi_by_bidang_{$bidangId}", 300, function () use ($bidangId) {
            return Seksi::where('bidang_id', $bidangId)
                ->where('aktif', true)
                ->orderBy('nama')
                ->get();
        });
    }
    protected static function getCachedRoles(): array
    {
        return Cache::remember('roles_options', 300, function () {
            return Role::orderBy('name')
                ->pluck('name', 'name')
                ->toArray();
        });
    }
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
                            ->dehydrateStateUsing(fn($state) => Hash::make($state))
                            ->dehydrated(fn($state) => filled($state))
                            ->required(fn(string $context) => $context === 'create')
                            ->minLength(8)
                            ->maxLength(255),

                        Forms\Components\Select::make('organisasi_id')
                            ->label('Organisasi')
                            ->options(fn() => self::getCachedOrganisasi())
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(function (Forms\Set $set) {
                                $set('bidang_id', null);
                                $set('seksi_id', null);
                            }),
                        Forms\Components\Select::make('bidang_id')
                            ->label('Bidang/Sekretariat')
                            ->options(
                                fn(Get $get): array =>
                                self::getCachedBidangByOrganisasi($get('organisasi_id'))
                                    ->where('aktif', true)
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn(Forms\Set $set) => $set('seksi_id', null)),
                        Forms\Components\Select::make('seksi_id')
                            ->label('Seksi/Subbagian')
                            ->options(
                                fn(Get $get): array =>
                                self::getCachedSeksiByBidang($get('bidang_id'))
                                    ->where('aktif', true)
                                    ->pluck('nama', 'id')
                                    ->toArray()
                            )
                            ->searchable()
                            ->preload(),
                    ])
                    ->columns(4),
                Forms\Components\Section::make('Role & Permissions')
                    ->schema([
                        Forms\Components\Select::make('roles')
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->preload()
                            ->searchable()
                            ->label('Roles')
                            ->helperText('Anda Dapat Memilih Lebih Dari Satu Role'),
                        Forms\Components\Select::make('permissions')
                            ->relationship('permissions', 'name')
                            ->multiple()
                            ->searchable()
                            ->label('Direct Permissions')
                            ->helperText('Anda Dapat Memilih Lebih Dari Satu Permission'),
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
                    ->sortable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('roles.name')
                    ->badge()
                    ->separator(',')
                    ->color('success')
                    ->label('Roles'),
                Tables\Columns\TextColumn::make('organisasi.nama')
                    ->label('Organisasi')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('bidang.nama')
                    ->label('Bidang')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('seksi.nama')
                    ->label('Seksi')
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
                            $records->each(function ($record) {
                                $record->update(['email_verified_at' => now()]);
                            });
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
                            $records->each(function ($record) use ($data) {
                                $record->assignRole($data['role']);
                            });
                        })
                        ->deselectRecordsAfterCompletion(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
    public static function getNavigationBadge(): ?string
    {
        return Cache::remember('user_count', 300, function () {
            return static::getModel()::count();
        });
    }
}
