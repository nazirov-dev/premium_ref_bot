<?php

namespace App\Filament\Resources\SettingResource\Pages;

use App\Filament\Resources\SettingResource;
use App\Models\Setting;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Cache;

class CreateSetting extends CreateRecord
{
    protected static string $resource = SettingResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array{
        // cache the Setting record 
        // cache()->put('settings', $data);
        $settings = new Setting();
        foreach($data as $key => $value){
            $settings->$key = $value;
        }
        Cache::put('settings', $settings);
        return $data;
    }
}
