<?php

namespace App\Filament\Resources\BoostChannelResource\Pages;

use App\Filament\Resources\BoostChannelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListBoostChannels extends ListRecords
{
    protected static string $resource = BoostChannelResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
