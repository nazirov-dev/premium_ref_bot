<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Http\Controllers\TextController;
use Illuminate\Support\Facades\Log;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('CreateMessage::mutateFormDataBeforeCreate: ', $data);
        $cleaned = TextController::sanitizeHtmlForTelegram($data['text']);
        $data['text'] = $cleaned;
        return $data;
    }
}
