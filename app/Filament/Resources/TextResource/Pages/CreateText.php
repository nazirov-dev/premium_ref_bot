<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Text;
use App\Http\Controllers\TextController;

class CreateText extends CreateRecord
{
    protected static string $resource = TextResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $cleaned = TextController::sanitizeHtmlForTelegram($data['value']);
        $data['value'] = $cleaned;

        TextController::set($data['key'], $cleaned);
        return $data;
    }

}
