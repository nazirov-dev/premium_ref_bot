<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Models\Text;
use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\TextController;
use Illuminate\Support\Facades\Log;

class EditText extends EditRecord
{
    protected static string $resource = TextResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        Log::info('Before sanitizing: ', $data);
        $cleaned = TextController::sanitizeHtmlForTelegram($data['value']);
        $data['value'] = $cleaned;
        Log::info('After sanitezing: ', $data);
        TextController::set($data['key'], $cleaned);
        return $data;
    }
    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['value'] = TextController::sanitizeHtmlForFilament($data['value']);
        return $data;
    }
    protected function getHeaderActions(): array
    {
        return [];
    }
}
