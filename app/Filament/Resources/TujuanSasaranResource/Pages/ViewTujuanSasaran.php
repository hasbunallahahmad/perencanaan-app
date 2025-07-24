<?php

namespace App\Filament\Resources\TujuanSasaranResource\Pages;

use App\Filament\Resources\TujuanSasaranResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewTujuanSasaran extends ViewRecord
{
    protected static string $resource = TujuanSasaranResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('reset_realisasi')
                ->label('Reset Realisasi')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Reset Realisasi')
                ->modalDescription('Apakah Anda yakin ingin mengatur ulang semua realisasi ke 0?')
                ->action(fn() => $this->record->resetRealisasi())
                ->successNotificationTitle('Realisasi berhasil direset')
                ->after(fn() => $this->refreshRecord()),
        ];
    }
}
