<?php

namespace App\Filament\Resources\Cafes;

use App\Filament\Resources\Cafes\Infolists\CafeInfolist;
use App\Filament\Resources\Cafes\Pages\CreateCafe;
use App\Filament\Resources\Cafes\Pages\EditCafe;
use App\Filament\Resources\Cafes\Pages\ListCafes;
use App\Filament\Resources\Cafes\Pages\ViewCafe;
use App\Filament\Resources\Cafes\Schemas\CafeForm;
use App\Filament\Resources\Cafes\Tables\CafesTable;
use App\Filament\Resources\Concerns\HasRoleNavigation;
use App\Models\Cafe;
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

class CafeResource extends Resource
{
    use HasRoleNavigation;

    protected static ?string $model = Cafe::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $roleNavigationGroup = 'Master Data';

    protected static array $allowedRoles = ['admin', 'manager', 'super_admin'];

    protected static ?SubNavigationPosition $subNavigationPosition = SubNavigationPosition::Top;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return CafeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CafesTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CafeInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = Auth::user();

        $query = parent::getEloquentQuery()->with(['manager.manager', 'subscription']);

        if ($user?->role === 'manager' && filled($user->cafe_id)) {
            return $query->whereKey($user->cafe_id);
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
                ->isActiveWhen(fn (): bool => $page::getRouteName() === ViewCafe::getRouteName())
                ->url(static::getUrl('view', ['record' => $record])),
            NavigationItem::make('Edit')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->isActiveWhen(fn (): bool => $page::getRouteName() === EditCafe::getRouteName())
                ->url(static::getUrl('edit', ['record' => $record])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCafes::route('/'),
            'view' => ViewCafe::route('/{record}'),
            'create' => CreateCafe::route('/create'),
            'edit' => EditCafe::route('/{record}/edit'),
        ];
    }
}
