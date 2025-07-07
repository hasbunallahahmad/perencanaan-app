<?php

namespace App\Filament\Resources\SeksiResource\Pages;

use App\Filament\Resources\SeksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Auth;

class ListSeksis extends ListRecords
{
    protected static string $resource = SeksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
    public function mount(): void
    {
        parent::mount();
        if (!cache()->has('seksi_query_' . Auth::id())) {
            SeksiResource::warmUpCache();
        }
    }
    protected function afterCreate(): void
    {
        SeksiResource::clearResourceCaches();
    }

    protected function afterSave(): void
    {
        SeksiResource::clearResourceCaches();
    }

    protected function afterDelete(): void
    {
        SeksiResource::clearResourceCaches();
    }
}
