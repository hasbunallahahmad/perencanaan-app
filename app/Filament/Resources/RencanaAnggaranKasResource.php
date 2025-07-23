<?php
// app/Filament/Resources/RencanaAnggaranKasResource.php

namespace App\Filament\Resources;

use App\Filament\Resources\RencanaAnggaranKasResource\Pages;
use App\Models\RencanaAnggaranKas;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;

class RencanaAnggaranKasResource extends Resource
{
    protected static ?string $model = RencanaAnggaranKas::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Jenis Anggaran';

    protected static ?string $modelLabel = 'Jenis Anggaran ';

    protected static ?string $pluralModelLabel = 'Jenis Anggaran ';
    protected static ?int $navigationSort = 1;
    protected static ?string $navigationGroup = 'Manajemen Anggaran';
    public static function canAccess(): bool
    {
        return \Illuminate\Support\Facades\Auth::user()->hasRole('super_admin');
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Dasar')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\Select::make('tahun')
                                    ->label('Tahun')
                                    ->options(function () {
                                        $currentYear = date('Y');
                                        $years = [];
                                        for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                                            $years[$i] = $i;
                                        }
                                        return $years;
                                    })
                                    ->default(date('Y'))
                                    ->required(),

                                Forms\Components\Select::make('jenis_anggaran')
                                    ->label('Pagu Anggaran')
                                    ->options([
                                        'anggaran_murni' => 'Anggaran Murni',
                                        'pergeseran' => 'Pergeseran',
                                        'perubahan' => 'Perubahan',
                                    ])
                                    ->required()
                                    ->default('anggaran_murni'),
                            ]),

                        Forms\Components\Textarea::make('deskripsi')
                            ->label('Deskripsi')
                            ->rows(3)
                            ->placeholder('Deskripsi detail mengenai rencana anggaran'),
                    ]),

                Forms\Components\Section::make('Detail Anggaran')
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Forms\Components\TextInput::make('jumlah_rencana')
                                    ->label('Pagu (Rp)')
                                    ->numeric()
                                    ->required()
                                    ->prefix('Rp')
                                    ->placeholder('0'),

                                Forms\Components\DatePicker::make('tanggal_rencana')
                                    ->label('Tanggal Rencana')
                                    ->required()
                                    ->default(now()),
                            ]),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'approved' => 'Disetujui',
                                'rejected' => 'Ditolak',
                            ])
                            ->default('approved')
                            ->required(),

                        Forms\Components\Textarea::make('catatan')
                            ->label('Catatan')
                            ->rows(3)
                            ->placeholder('Catatan tambahan'),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tahun')
                    ->label('Tahun')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('jenis_anggaran')
                    ->label('Jenis Anggaran')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'anggaran_murni' => 'Anggaran Murni',
                        'pergeseran' => 'Pergeseran',
                        'perubahan' => 'Perubahan',
                        default => $state,
                    })
                    ->colors([
                        'primary' => 'anggaran_murni',
                        'warning' => 'pergeseran',
                        'success' => 'perubahan',
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('jumlah_rencana')
                    ->label('Pagu Anggaran')
                    ->money('IDR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'success' => 'approved',
                        'danger' => 'rejected',
                    ])
                    ->formatStateUsing(fn(string $state): string => match ($state) {
                        'draft' => 'Draft',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                        default => $state,
                    }),

                Tables\Columns\TextColumn::make('tanggal_rencana')
                    ->label('Tanggal Rencana')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('tahun')
                    ->label('Tahun')
                    ->options(function () {
                        $currentYear = date('Y');
                        $years = [];
                        for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                SelectFilter::make('jenis_anggaran')
                    ->label('Jenis Anggaran')
                    ->options([
                        'anggaran_murni' => 'Anggaran Murni',
                        'pergeseran' => 'Pergeseran',
                        'perubahan' => 'Perubahan',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'approved' => 'Disetujui',
                        'rejected' => 'Ditolak',
                    ]),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Action::make('approve')
                        ->label('Setujui')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->visible(fn(RencanaAnggaranKas $record): bool => $record->status === 'draft')
                        ->action(fn(RencanaAnggaranKas $record) => $record->update(['status' => 'approved']))
                        ->requiresConfirmation(),
                    Action::make('reject')
                        ->label('Tolak')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->visible(fn(RencanaAnggaranKas $record): bool => $record->status === 'draft')
                        ->action(fn(RencanaAnggaranKas $record) => $record->update(['status' => 'rejected']))
                        ->requiresConfirmation(),
                    Tables\Actions\DeleteAction::make(),
                ]),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListRencanaAnggaranKas::route('/'),
            'create' => Pages\CreateRencanaAnggaranKas::route('/create'),
            // 'view' => Pages\ViewRencanaAnggaranKas::route('/{record}'),
            'edit' => Pages\EditRencanaAnggaranKas::route('/{record}/edit'),
        ];
    }
}
