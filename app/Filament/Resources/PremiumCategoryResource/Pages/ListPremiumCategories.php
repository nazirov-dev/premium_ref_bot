<?php

namespace App\Filament\Resources\PremiumCategoryResource\Pages;

use App\Filament\Resources\PremiumCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPremiumCategories extends ListRecords
{
    protected static string $resource = PremiumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
