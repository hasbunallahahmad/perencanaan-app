<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MasterTujuanSasaranResource\Pages;
use App\Models\MasterTujuanSasaran;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MasterTujuanSasaranResource extends Resource
{
    protected static ?string $model = MasterTujuanSasaran::class;
    protected static ?string $navigationGroup = 'Master Data';
    protected static ?string $navigationLabel = 'Master Tujuan ';
    protected static ?string $modelLabel = 'Master Tujuan ';
    protected static ?string $pluralModelLabel = 'Master Tujuan ';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Master Tujuan & Sasaran')
                    ->description('Data master yang akan digunakan untuk membuat tujuan & sasaran')
                    ->schema([
                        Forms\Components\Textarea::make('tujuan')
                            ->label('Tujuan')
                            ->required()
                            ->rows(4)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('indikator_tujuan')
                            ->label('Indikator Tujuan')
                            ->required()
                            ->rows(3)
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('target')
                            ->label('Target')
                            ->required()
                            ->maxLength(65535)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('satuan')
                            ->label('Satuan Target')
                            ->required()
                            ->maxLength(65535)
                            ->helperText('Contoh: Orang, Unit, Ton, dll')
                            ->rows(2)
                            ->columnSpanFull(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Status Aktif')
                            ->default(true)
                            ->helperText('Data yang tidak aktif tidak akan muncul di pilihan')
                            ->columnSpanFull()
                            ->hidden(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('tujuan')
                    ->label('Tujuan')
                    ->limit(255)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('indikator_tujuan')
                    ->label('Tujuan')
                    ->limit(255)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('target')
                    ->label('Target')
                    ->limit(255)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('satuan')
                    ->label('Satuan ')
                    ->limit(255)
                    ->wrap()
                    ->tooltip(function (Tables\Columns\TextColumn $column): ?string {
                        $state = $column->getState();
                        return strlen($state) > 60 ? $state : null;
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Diperbarui')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),

                Tables\Filters\Filter::make('active')
                    ->label('Hanya yang Aktif')
                    ->query(fn(Builder $query): Builder => $query->where('is_active', true))
                    ->default(),

                Tables\Filters\Filter::make('used')
                    ->label('Sudah Digunakan')
                    ->query(fn(Builder $query): Builder => $query->has('tujas')),

                Tables\Filters\Filter::make('unused')
                    ->label('Belum Digunakan')
                    ->query(fn(Builder $query): Builder => $query->doesntHave('tujas')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
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
            'index' => Pages\ListMasterTujuanSasarans::route('/'),
            'create' => Pages\CreateMasterTujuanSasaran::route('/create'),
            // 'view' => Pages\ViewMasterTujuanSasaran::route('/{record}'),
            'edit' => Pages\EditMasterTujuanSasaran::route('/{record}/edit'),
        ];
    }
}
