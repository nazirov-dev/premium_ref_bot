<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\TelegramService;
use App\Http\Controllers\PrivateChat;
use App\Models\JoinRequest;


class TelegramController extends Controller
{
    public function handle(Request $request)
    {
        $input = $request->all();
        $bot = new TelegramService;
        // $bot->sendMessage([
        //     'chat_id' => 1996292437,
        //     'text' => json_encode($bot->getData(), 128)
        // ]);
        // \Illuminate\Support\Facades\Log::channel('telegram_updates')->info("Telegram Update (" . date('Y-m-d H:i:s') . "): \n", $bot->getData());

        if (isset($input['message']))
            $chat_type = $input['message']['chat']['type'] ?? null;
        elseif (isset($input['callback_query']))
            $chat_type = $input['callback_query']['message']['chat']['type'] ?? null;
        else {
            if (isset($input['chat_join_request'])) {
                $user_id = $input['chat_join_request']['from']['id'];
                $chat_id = $input['chat_join_request']['chat']['id'];

                $joinRequest = JoinRequest::firstOrNew(['user_id' => $user_id, 'chat_id' => $chat_id]);
                if (!$joinRequest->exists) {
                    $joinRequest->save();
                }
                exit;
            } elseif (isset($input['removed_chat_boost']) or isset($input['chat_boost'])) {
                $chat_type = 'private';
            } elseif (isset($input['my_chat_member'])) {
                if (isset($input['my_chat_member']['new_chat_member']) and strtolower($input['my_chat_member']['new_chat_member']['user']['username']) == 'jasurpremiumbot' and in_array($input['my_chat_member']['new_chat_member']['status'], ['administrator', 'member'])) {
                    $bot->leaveChat(['chat_id' => $input['my_chat_member']['chat']['id']]);
                    return response()->json(['ok' => true], 200);
                }
            } else {
                return response()->json(['ok' => true], 200);
            }
        }
        if ($chat_type == 'private') {
            $run = new PrivateChat();
            return $run->handle($bot);
        }
    }
}
