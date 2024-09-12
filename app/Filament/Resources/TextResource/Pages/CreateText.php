<?php

namespace App\Filament\Resources\TextResource\Pages;

use App\Filament\Resources\TextResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Text;

class CreateText extends CreateRecord
{
    protected static string $resource = TextResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $lang_code = $data['lang_code'];
        unset($data['lang_code']);

        // Get the key of the last element
        end($data); // Move the internal pointer of the array to the end
        $last_key = key($data); // Get the key of the current element (which is now at the end)

        $last_value = $data[$last_key]; // Get the value of the last element

        // Remove the last element from the array
        unset($data[$last_key]);
        function filterUnsupportedTags($text)
        {
            return str_replace(['<p>', '</p>', "<br>", '&nbsp;'], ['', '', "\n", "\n"], $text);
        }
        foreach ($data as $key => $value) {
            Text::create([
                'key' => $key,
                'value' => filterUnsupportedTags($value),
                'lang_code' => $lang_code
            ]);
        }

        return ['key' => $last_key, 'value' => $last_value, 'lang_code' => $lang_code];
    }

}
