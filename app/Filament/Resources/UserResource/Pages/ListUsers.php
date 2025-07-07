<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Facades\FilamentView;
use Filament\Tables\View\TablesRenderHook;
use Illuminate\Support\Facades\Blade;
use PhpParser\Node\Stmt\Label;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->Label('Tambah pengguna'),
        ];
    }
    public function mount(): void
    {
        // FilamentView::registerRenderHook(
        //     TablesRenderHook::TOOLBAR_START,
        //     function () {
        //         return Blade::render('<x-filament::button tag="a" href="{{ $link }}">Create</x-filament::button>', [
        //             'link' => self::$resource::getUrl('create')
        //         ]);
        //     }
        // );

        // parent::mount();
    }
}
