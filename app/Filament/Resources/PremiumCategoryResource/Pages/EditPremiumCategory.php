<?php

namespace App\Filament\Resources\PremiumCategoryResource\Pages;

use App\Filament\Resources\PremiumCategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPremiumCategory extends EditRecord
{
    protected static string $resource = PremiumCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
