<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Filament\Resources\Subscriptions\SubscriptionResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Support\Facades\Auth;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        $role = Auth::user()?->role;
        $isManager = is_string($role) && in_array($role, ['manager'], true);

        if ($isManager) {
            return [];
        }

        return [
            EditAction::make(),
            DeleteAction::make(),
        ];
    }
}
