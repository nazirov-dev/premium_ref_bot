<?php

namespace App\Filament\Resources\MessageResource\Pages;

use App\Filament\Resources\MessageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Http\Controllers\TextController;

class CreateMessage extends CreateRecord
{
    protected static string $resource = MessageResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        function convertToTelegramInlineKeyboard($text, $limitPerRow = 5)
        {
            $keyboard = [];
            $key = [];

            // Split the input into rows based on newlines
            $rows = explode("\n", $text);

            foreach ($rows as $row) {
                // Match [text-url] pattern
                preg_match_all('/\[(.*?)\-(.*?)\]/', $row, $matches, PREG_SET_ORDER);

                foreach ($matches as $match) {
                    $text = $match[1];
                    $url = $match[2];

                    // Add button to current row
                    $key[] = ["text" => $text, "url" => $url];

                    // If row reaches limit, add to keyboard and start a new row
                    if (count($key) >= $limitPerRow) {
                        $keyboard[] = $key;
                        $key = [];
                    }
                }

                // Add remaining buttons in the row
                if (!empty($key)) {
                    $keyboard[] = $key;
                    $key = [];
                }
            }

            return json_encode($keyboard);
        }
        $cleaned = TextController::sanitizeHtmlForTelegram($data['text']);
        $data['reply_markup'] = convertToTelegramInlineKeyboard($data['buttons']);
        $data['text'] = $cleaned;
        return $data;
    }
}
