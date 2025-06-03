<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ProgramResource;
use App\Models\Program;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentProgramsWidget extends BaseWidget
{
    protected static ?string $heading = 'Program Terbaru';

    protected static ?int $sort = 3;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Program::query()
                    ->withCount('kegiatan')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('kode_program')
                    ->label('Kode')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('nama_program')
                    ->label('Nama Program')
                    ->limit(60)
                    ->wrap(),

                Tables\Columns\TextColumn::make('organisasi.nama')
                    ->label('Organisasi')
                    ->limit(30)
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('kegiatan_count')
                    ->label('Kegiatan')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->since()
                    ->badge()
                    ->color('gray'),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->url(fn(Program $record): string => ProgramResource::getUrl('view', ['record' => $record]))
                    ->icon('heroicon-m-eye'),
            ]);
    }
}
