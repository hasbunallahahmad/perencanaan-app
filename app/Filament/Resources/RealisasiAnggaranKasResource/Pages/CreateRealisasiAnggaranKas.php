<?php

namespace App\Filament\Resources\RealisasiAnggaranKasResource\Pages;

use App\Filament\Resources\RealisasiAnggaranKasResource;
use App\Models\RencanaAnggaranKas;
use Filament\Pages\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRealisasiAnggaranKas extends CreateRecord
{
    protected static string $resource = RealisasiAnggaranKasResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ensure required fields are not null before creation
        if (empty($data['tahun']) || empty($data['triwulan']) || empty($data['kategori'])) {
            if (!empty($data['rencana_anggaran_kas_id'])) {
                $rencana = RencanaAnggaranKas::find($data['rencana_anggaran_kas_id']);
                if ($rencana) {
                    $data['tahun'] = $rencana->tahun;
                    $data['triwulan'] = $rencana->triwulan;
                    $data['kategori'] = $rencana->kategori;
                }
            }
        }

        return $data;
    }
}
