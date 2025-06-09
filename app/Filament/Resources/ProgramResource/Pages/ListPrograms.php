<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use App\Services\ProgramCalculationService;
use Filament\Actions;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListPrograms extends ListRecords
{
    protected static string $resource = ProgramResource::class;

    public function table(Table $table): Table
    {
        return $this->getResource()::table($table)
            ->emptyStateHeading('Belum Ada Data Program')
            ->emptyStateDescription('Mulai dengan menambahkan program organisasi Anda.')
            ->emptyStateIcon('heroicon-o-clipboard-document-list')
            ->emptyStateActions([
                Tables\Actions\CreateAction::make()
                    ->label('Tambahkan Program Sesuai Nomenklatur Organisasi')
                    ->icon('heroicon-o-plus-circle')
                    ->button()
                    ->color('primary'),
            ]);
    }
}
