<?php

namespace App\Filament\Resources\BoostChannelResource\Pages;

use App\Filament\Resources\BoostChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBoostChannel extends EditRecord
{
    protected static string $resource = BoostChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
