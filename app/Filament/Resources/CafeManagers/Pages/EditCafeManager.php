<?php

namespace App\Filament\Resources\CafeManagers\Pages;

use App\Filament\Resources\CafeManagers\CafeManagerResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditCafeManager extends EditRecord
{
    protected static string $resource = CafeManagerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
