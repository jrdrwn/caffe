<?php

namespace App\Filament\Resources\UserActivityLogs\Pages;

use App\Filament\Resources\UserActivityLogs\UserActivityLogResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserActivityLog extends EditRecord
{
    protected static string $resource = UserActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
