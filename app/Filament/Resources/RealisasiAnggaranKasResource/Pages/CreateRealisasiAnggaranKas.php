<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use App\Models\RencanaAnggaranKas;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiAnggaranKas extends CreateRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Hanya validasi untuk existing rencana
        if (empty($data['rencana_anggaran_kas_id'])) {
            throw new \Exception("Silakan pilih rencana anggaran yang sudah ada.");
        }

        // Validasi apakah rencana masih aktif
        $rencana = RencanaAnggaranKas::find($data['rencana_anggaran_kas_id']);
        if (!$rencana || $rencana->status !== 'approved') {
            throw new \Exception("Rencana anggaran yang dipilih tidak valid atau belum disetujui.");
        }

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
