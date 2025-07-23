<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRealisasiAnggaranKas extends EditRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->icon('heroicon-o-arrow-left')
                ->color('gray')
                ->url($this->getResource()::getUrl('index')),
            Actions\EditAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Jika ada relasi dengan rencana anggaran kas, isi data yang diperlukan untuk display
        if ($this->record->rencanaAnggaranKas) {
            $rencana = $this->record->rencanaAnggaranKas;

            // Hanya isi jika field ini ada di form dan dibutuhkan untuk display
            $data['jenis_anggaran'] = $rencana->jenis_anggaran_text;
            $data['pagu'] = $rencana->jumlah_rencana;

            // Calculate rencana per triwulan (pagu / 4) - jika dibutuhkan
            $rencanaPerTriwulan = $rencana->jumlah_rencana / 4;
            $data['rencana_tw_1'] = $rencanaPerTriwulan;
            $data['rencana_tw_2'] = $rencanaPerTriwulan;
            $data['rencana_tw_3'] = $rencanaPerTriwulan;
            $data['rencana_tw_4'] = $rencanaPerTriwulan;
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
