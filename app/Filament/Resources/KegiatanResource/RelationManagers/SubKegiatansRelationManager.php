<?php

namespace App\Filament\Resources\KegiatanResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\ViewAction;

class SubKegiatansRelationManager extends RelationManager
{
  protected static string $relationship = 'subKegiatans';

  protected static ?string $title = 'Sub Kegiatan';

  protected static ?string $modelLabel = 'Sub Kegiatan';

  protected static ?string $pluralModelLabel = 'Sub Kegiatan';

  public function form(Form $form): Form
  {
    return $form
      ->schema([
        Forms\Components\Section::make('Informasi Sub Kegiatan')
          ->description('Masukkan data sub kegiatan')
          ->schema([
            TextInput::make('kode_sub_kegiatan')
              ->label('Kode Sub Kegiatan')
              ->required()
              ->maxLength(50)
              ->unique(ignoreRecord: true)
              ->placeholder('Contoh: 2.08.01.2.01.0001'),

            TextInput::make('nama_sub_kegiatan')
              ->label('Nama Sub Kegiatan')
              ->required()
              ->maxLength(255)
              ->placeholder('Masukkan nama sub kegiatan')
              ->columnSpanFull(),
          ])
          ->columns(2)
      ]);
  }

  public function table(Table $table): Table
  {
    return $table
      ->recordTitleAttribute('nama_sub_kegiatan')
      ->columns([
        TextColumn::make('kode_sub_kegiatan')
          ->label('Kode Sub Kegiatan')
          ->searchable()
          ->sortable()
          ->copyable()
          ->weight('medium'),

        TextColumn::make('nama_sub_kegiatan')
          ->label('Nama Sub Kegiatan')
          ->searchable()
          ->sortable()
          ->wrap()
          ->limit(60),

        TextColumn::make('total_anggaran')
          ->label('Total Anggaran')
          ->money('IDR')
          ->sortable()
          ->alignEnd(),

        TextColumn::make('total_realisasi')
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

        TextColumn::make('serapan_anggaran_count')
          ->label('Data Serapan')
          ->counts('serapanAnggaran')
          ->sortable()
          ->alignCenter(),

        TextColumn::make('created_at')
          ->label('Dibuat')
          ->dateTime('d/m/Y H:i')
          ->sortable()
          ->toggleable(isToggledHiddenByDefault: true),
      ])
      ->filters([
        //
      ])
      ->headerActions([
        CreateAction::make()
          ->label('Tambah Sub Kegiatan'),
      ])
      ->actions([
        ViewAction::make(),
        EditAction::make(),
        DeleteAction::make(),
      ])
      ->bulkActions([
        Tables\Actions\BulkActionGroup::make([
          Tables\Actions\DeleteBulkAction::make(),
        ]),
      ])
      ->defaultSort('kode_sub_kegiatan')
      ->striped()
      ->paginated([10, 25, 50]);
  }

  public function isReadOnly(): bool
  {
    return false;
  }
}
