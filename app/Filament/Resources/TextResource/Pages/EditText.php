<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Models\Text;
use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Http\Controllers\TextController;


class EditText extends EditRecord
{
    protected static string $resource = TextResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        TextController::set($data['key'], $data['value']);
        return $data;
    }

    protected function getHeaderActions(): array
    {
        return [];
    }
}
