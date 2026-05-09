<?php

namespace App\Filament\Resources\UserActivityLogs\Pages;

use App\Filament\Resources\UserActivityLogs\UserActivityLogResource;
use Filament\Resources\Pages\ListRecords;

class ListUserActivityLogs extends ListRecords
{
    protected static string $resource = UserActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
