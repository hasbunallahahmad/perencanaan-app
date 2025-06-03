<?php

namespace App\Filament\Resources\ProgramResource\Pages;

use App\Filament\Resources\ProgramResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateProgram extends CreateRecord
{
    protected static string $resource = ProgramResource::class;
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Program Berhasil Ditambahkan')
            ->body('Program' . $this->record->nama_program . 'berhasil ditambahkan');
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (empty($data['kode_program'])) {
            $lastProgram = $this->getModel()::latest('id')->first();
            $lastNumber = $lastProgram ? intval(substr($lastProgram->kode_program, -2)) : 0;
            $data['kode_program'] = '2.08.' . str_pad($lastNumber + 1, 2, '0', STR_PAD_LEFT);
        }

        return $data;
    }
}
