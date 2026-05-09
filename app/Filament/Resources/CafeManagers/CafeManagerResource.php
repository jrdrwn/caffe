<?php

namespace App\Filament\Resources\CafeManagers;

use App\Filament\Resources\CafeManagers\Infolists\CafeManagerInfolist;
use App\Filament\Resources\CafeManagers\Pages\CreateCafeManager;
use App\Filament\Resources\CafeManagers\Pages\EditCafeManager;
use App\Filament\Resources\CafeManagers\Pages\ListCafeManagers;
use App\Filament\Resources\CafeManagers\Pages\ViewCafeManager;
use App\Filament\Resources\CafeManagers\Schemas\CafeManagerForm;
use App\Filament\Resources\CafeManagers\Tables\CafeManagersTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\CafeManager;
use BackedEnum;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Enums\SubNavigationPosition;
use Filament\Resources\Pages\Page;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class CafeManagerResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = CafeManager::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'manager_id';

    protected static ?string $roleNavigationGroup = 'Manajemen Pengguna';

    protected static array $allowedRoles = ['admin', 'manager', 'super_admin'];

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    public static function form(Schema $schema): Schema
    {
        return CafeManagerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafeManagersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CafeManagerInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery();

        // eager-load related models to avoid N+1 when showing subscription badge
        $query = $query->with(['cafe.subscription', 'manager', 'assignedBy']);

        if ($user?->role === 'manager' && filled($user->cafe_id)) {
            return $query->where('cafe_id', $user->cafe_id);
        }

        return $query;
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    /**
     * @return array<NavigationItem>
     */
    public static function getRecordSubNavigation(Page $page): array
    {
        $record = $page->getRecord();

        return [
            NavigationItem::make('View')
                ->icon(Heroicon::OutlinedEye)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === ViewCafeManager::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
            NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === EditCafeManager::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafeManagers::route('/'),
            'view' => ViewCafeManager::route('/{record}'),
            'create' => CreateCafeManager::route('/create'),
            'edit' => EditCafeManager::route('/{record}/edit'),
        ];
    }
}
