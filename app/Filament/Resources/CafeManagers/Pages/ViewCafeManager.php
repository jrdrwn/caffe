<?php

namespace App\Filament\Resources\CafeManagers\Pages;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCafeManager extends ViewRecord
{
    protected static string $resource = 'App\\Filament\\Resources\\CafeManagers\\CafeManagerResource';

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
