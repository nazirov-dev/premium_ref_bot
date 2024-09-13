<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Text;
use App\Http\Controllers\TextController;
use Illuminate\Support\Facades\Log;
class CreateText extends CreateRecord
{
    protected static string $resource = TextResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        Log::info('CreateText: ', $data);
        TextController::set($data['key'], $data['value']);
        return $data;
    }

}
