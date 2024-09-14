<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\TextController;

class EditMessage extends EditRecord
{
    protected static string $resource = MessageResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $cleaned = TextController::sanitizeHtmlForTelegram($data['text']);
        $data['text'] = $cleaned;

        return $data;
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
