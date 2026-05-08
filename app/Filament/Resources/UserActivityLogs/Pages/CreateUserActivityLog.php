<?php

namespace App\Filament\Resources\UserActivityLogs\Pages;

use App\Filament\Resources\UserActivityLogs\UserActivityLogResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserActivityLog extends CreateRecord
{
    protected static string $resource = UserActivityLogResource::class;
}
