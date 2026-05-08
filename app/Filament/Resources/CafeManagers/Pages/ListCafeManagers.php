<?php

namespace App\Filament\Resources\CafeManagers\Pages;

use App\Filament\Resources\CafeManagers\CafeManagerResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCafeManagers extends ListRecords
{
    protected static string $resource = CafeManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
