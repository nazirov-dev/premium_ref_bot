<?php

namespace App\Filament\Resources\NotificationResource\Pages;

use App\Filament\Resources\NotificationResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;
use App\Services\TelegramService;

class CreateNotification extends CreateRecord
{
    protected static string $resource = NotificationResource::class;
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $bot = new TelegramService();
        $message = $bot->forwardMessage([
            'chat_id' => config('env.ADMIN_ID'),
            'from_chat_id' => config('env.ADS_TELEGRAM_CHANNEL_ID'),
            'message_id' => $data['message_id']
        ]);

        if($data['sending_type'] == 'copymessage' && !empty($message['result']['reply_markup'])) {
            $data['keyboard'] = base64_encode(json_encode($message['result']['reply_markup']));
        }

        $data['status'] = 'waiting';
        $data['admin_info_message_id'] = null;

        $data['sent'] = 0;
        $data['not_sent'] = 0;
        $data['sending_end_time'] = null;
        return $data;
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
