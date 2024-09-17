<?php

namespace App\Filament\Resources\UserIdentityDataResource\Pages;

use App\Filament\Resources\UserIdentityDataResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserIdentityData extends ListRecords
{
    protected static string $resource = UserIdentityDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
