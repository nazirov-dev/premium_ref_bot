<?php

namespace App\Filament\Resources\ButtonResource\Pages;

use App\Filament\Resources\ButtonResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

class CreateButton extends CreateRecord
{
    protected static string $resource = ButtonResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('Info: ', $data);
        return $data;
    }
}
