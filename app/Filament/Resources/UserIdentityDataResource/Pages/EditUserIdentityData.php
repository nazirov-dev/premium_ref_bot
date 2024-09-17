<?php

namespace App\Filament\Resources\UserIdentityDataResource\Pages;

use App\Filament\Resources\UserIdentityDataResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserIdentityData extends EditRecord
{
    protected static string $resource = UserIdentityDataResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
