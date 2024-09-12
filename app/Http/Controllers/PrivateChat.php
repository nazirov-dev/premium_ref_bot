<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TextController as Text;

use Illuminate\Support\Facades\Log;
use App\Models\BotUser;
use App\Models\Button;
use App\Models\Channel;
use App\Models\JoinRequest;
use App\Models\Message;
use App\Models\PremiumCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Number;
use Illuminate\Support\Facades\Storage;

class PrivateChat extends Controller
{
    public function __construct()
    {
    }
    public function check_user_subscribed_to_channels($bot, $user_id)
    {
        $not = 0;
        $not_subscribed_channels = [];
        $channels = Channel::where(['status' => 1])->get()->toArray();
        foreach ($channels as $channel) {
            $status = $bot->getChatMember([
                'chat_id' => $channel['channel_id'],
                'user_id' => $user_id
            ])['result']['status'] ?? null;
            if (is_null($status))
                $not++;
            if (!in_array($status, ['administrator', 'creator', 'member'])) {
                $JoinRequest = JoinRequest::where(['user_id' => $user_id, 'chat_id' => $channel['channel_id']])->exists();
                if (!$JoinRequest)
                    $not_subscribed_channels[] = $channel;
            }
        }
        if (count($not_subscribed_channels) - $not > 0) {
            return $not_subscribed_channels;
        } else {
            return true;
        }
    }

    public function replacePlaceholders($array, $replacements)
    {
        // Check if the input is an array
        if (is_array($array)) {
            $newArray = [];
            foreach ($array as $key => $value) {
                // Recursively process each element of the array
                $newArray[$key] = $this->replacePlaceholders($value, $replacements);
            }
            return $newArray;
        }

        // If the value is a string, replace placeholders
        if (is_string($array)) {
            return str_replace(array_keys($replacements), array_values($replacements), $array);
        }

        // Return the original value for non-string, non-array types
        return $array;
    }
    public function getMainButtons($settings, $bot)
    {
        $keyboard = [];
        if ($settings->giveaway_status) {
            $button = Button::where(['slug' => 'giveaway_button'])->first();
            $keyboard[] = [['text' => $button->name]];
        }
        if ($settings->premium_store_status) {
            $button = Text::get('premium_store_button_label');
            $keyboard[] = [['text' => $button]];
        }
        $keyboard[] = [['text' => Button::where(['slug' => 'premium_prices_button'])->first()->name], ['text' => Text::get('my_balance_button_label')]];
        if ($settings->bonus_menu_status) {
            $keyboard[] = [['text' => Text::get('bonus_menu_button_label')]];
        }
        $keyboard[] = [['text' => Button::where(['slug' => 'instructions_button'])->first()->name], ['text' => Button::where(['slug' => 'administrator_button'])->first()->name]];

        return $bot->buildKeyBoard($keyboard, resize_keyboard: true);
    }
    public function sendMessage($bot, Message $message, $chat_id, $replacements = [])
    {
        $keyboard = null;
        if (!is_null($message->reply_markup)) {
            $keyboard = json_decode($message->reply_markup, true);
            $keyboard = $this->replacePlaceholders($keyboard, $replacements);
            $keyboard = $bot->buildInlineKeyBoard($keyboard);
        }
        if (!empty($message->text)) {
            $message->text = $this->replacePlaceholders($message->text, $replacements);
        }
        if ($message->type === 'text') {
            $bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => $message->text,
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            ]);
        } elseif ($message->type === 'photo') {
            $bot->sendPhoto([
                'chat_id' => $chat_id,
                'photo' => $message->file_id,
                'caption' => $message->text,
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            ]);
        } elseif ($message->type === 'video') {
            $bot->sendVideo([
                'chat_id' => $chat_id,
                'video' => $message->file_id,
                'caption' => $message->text,
                'reply_markup' => $keyboard,
                'parse_mode' => 'HTML'
            ]);
        }
    }
    public function handle($bot)
    {
        $text = $bot->Text();
        $chat_id = $bot->ChatID();
        $update_type = $bot->getUpdateType();

        if (!is_null($text)) {
            //cached settings for 1 day
            $settings = Cache::remember('bot_settings', 60 * 60 * 24, function () {
                return \App\Models\Setting::first();
            });

            // user model
            $user = BotUser::where('user_id', $chat_id)->first();

            // check if user not exists in database
            if (is_null($user)) {
                #check is user visited via referral link, link like: https://t.me/your_bot?start=user_id
                if (strpos($text, '/start ') !== false and $settings->referral_status) {
                    $referral_id = explode(' ', $text)[1] ?? null;
                }
                $user = BotUser::create([
                    'user_id' => $chat_id,
                    'name' => $bot->FirstName() . ' ' . $bot->LastName(),
                    'username' => $bot->Username(),
                    'status' => true,
                    'balance' => 0,
                    'refferrer_id' => $referral_id,
                    'is_premium' => $bot->isPremiumUser()
                ]);
                Cache::set($chat_id . '.step', 'start');
            }
            $check_subscription = $this->check_user_subscribed_to_channels($bot, $chat_id);
            if ($check_subscription !== true) {
                $bot->deleteThisMessage();
                $keyboard = [];
                foreach ($check_subscription as $channel) {
                    $keyboard[] = [['text' => $channel['name'], 'url' => $channel['invite_link']]];
                }
                $keyboard[] = [['text' => Text::get('check_button_label'), 'callback_data' => 'check']];
                $bot->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => Text::get('you_are_still_not_member'),
                    'reply_markup' => $bot->buildInlineKeyBoard($keyboard)
                ]);
            }
            // get step from cache
            $step = Cache::get($chat_id . '.step');

            if (!is_null($step) and $step == 'start') {
                if (!is_null($user->refferrer_id) and $settings->referral_status) {
                    if ($bot->isPremiumUser() and $settings->premium_referral_status) {
                        $bonus = $settings->premium_referral_bonus;
                    } else {
                        $bonus = $settings->referral_bonus;
                    }
                    $refferrer = BotUser::where('user_id', $user->refferrer_id)->first();
                    if ($refferrer) {
                        $refferrer->balance += $bonus;
                        $refferrer->save();
                    }
                    $bot->sendMessage([
                        'chat_id' => $user->refferrer_id,
                        'text' => $this->replacePlaceholders(Text::get('referral_bonus_message'), [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id,
                            '{bonus}' => $bonus
                        ]),
                        'parse_mode' => 'HTML'
                    ]);
                }
                $user->balance = 0;
                $user->save();
                Cache::forget($chat_id . '.step');
            }

            if (empty($user->phone_number)) {
                if ($update_type == 'contact') {
                    $user->phone_number = $bot->ContactPhoneNumber();
                    $user->save();
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => Text::get('phone_number_saved'),
                        'reply_markup' => $this->getMainButtons($settings, $bot)
                    ]);
                }
                $bot->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => Text::get('phone_number_request'),
                    'reply_markup' => $bot->buildReplyKeyBoard([
                        [['text' => Text::get('send_phone_number'), 'request_contact' => true]]
                    ])
                ]);
                return response()->json(['ok' => true], 200);
            }
            if ($update_type == 'message') {
                if ($user) {
                    if (!$user->status)
                        $user->status = true;

                    if ($user->is_premium and !$bot->isPremiumUser())
                        $user->is_premium = false;
                    $user->save();
                }
                if ($text == '/start') {
                    $start_message = Text::get('start_message');
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $start_message,
                        'reply_markup' => $this->getMainButtons($settings, $bot)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == Text::get('premium_store_button_label')) {
                    if (!$settings->premium_store_status) {
                        $replacements = [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id
                        ];
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $this->replacePlaceholders(Text::get('premium_store_not_available'), $replacements),
                            'reply_markup' => $this->getMainButtons($settings, $bot)
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    $store_message = Text::get('premium_store_message');
                    $premium_categoires = PremiumCategory::where(['status' => 1])->get();
                    $available_premiums_info = '';
                    foreach ($premium_categoires as $category) {
                        $available_premiums_info .= "\n\n<b>" . $category->name . "</b>\nNarxi: " . Number::format($category->price_in_uzs) . " so'm yoki " . Number::format($category->price_in_stars) . " yulduz\nMavjud premiumlar soni: " . $category->count . " ta";
                    }
                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id,
                        '{available_premiums_info}' => $available_premiums_info
                    ];
                    $store_message = $this->replacePlaceholders($store_message, $replacements);
                    $premium_category_buttons = [];
                    foreach ($premium_categoires as $category) {
                        $premium_category_buttons[] = [['text' => $category->name, 'callback_data' => 'premium_category_' . $category->id]];
                    }
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $store_message,
                        'reply_markup' => $bot->buildInlineKeyBoard($premium_category_buttons)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == Text::get('my_balance_button_label')) {
                    $counts = BotUser::selectRaw('COUNT(*) as total, SUM(is_premium = 0) as frens_count, SUM(is_premium = 1) as frens_premium_count')
                        ->where('referrer_id', $chat_id)
                        ->first();
                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id,
                        '{phone_number}' => $user->phone_number,
                        '{balance}' => $user->balance,
                        '{frens_count}' => $counts->frens_count,
                        '{frens_premium_count}' => $counts->frens_premium_count,
                        '{total_frens}' => $counts->total
                    ];
                    $buttons = [
                        [['text' => Text::get('withdraw_request_button_label'), 'callback_data' => 'withdraw_request']],
                    ];
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $this->replacePlaceholders(Text::get('my_balance_message'), $replacements),
                        'reply_markup' => $bot->buildInlineKeyBoard($buttons)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == Text::get('bonus_menu_button_label')) {
                    $bonus_menu_message = Text::get('bonus_menu_message');
                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id
                    ];
                    $bonus_menu_message = $this->replacePlaceholders($bonus_menu_message, $replacements);
                    $emoji = $user->daily_bonus_status ? '✅' : '❌';
                    $bonus_menu_buttons = [
                        [['text' => Text::get('get_bonus_via_boosting_channels'), 'callback_data' => 'boost_channels']],
                        [['text' => Text::get('daily_bonus_button_label') . $emoji, 'callback_data' => 'daily_bonus']]
                    ];
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $bonus_menu_message,
                        'reply_markup' => $bot->buildInlineKeyBoard($bonus_menu_buttons)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == '/dev') {
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => '<b>👨‍💻 Dasturchi:</b> @Cyber_Senior',
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [['text' => '📃 Blog', 'url' => 'https://t.me/Nazirov_Blog']]
                            ]
                        ])
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == '/panel' and in_array($chat_id, [config('env.ADMIN_ID'), config('env.DEV_ID')])) {
                    $admin_dashboard_url = config('app.url') . "/admin";
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => "Quyidagi ssilka orqali panelga kirishingiz mumkin:\n\n$admin_dashboard_url",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [['text' => 'Panelga kirish', 'url' => $admin_dashboard_url]],
                                [
                                    [
                                        'text' => 'Panelga web app orqali kirish',
                                        'web_app' => [
                                            'url' => $admin_dashboard_url
                                        ]
                                    ],
                                ]
                            ]
                        ])
                    ]);
                    return response()->json(['ok' => true], 200);
                } else {
                    $findButton = Button::where(['name' => $text])->first();
                    if ($findButton) {
                        $messages = $findButton->messages;
                        $replacements = [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id,
                            '{phone_number}' => $user->phone_number,
                            '{balance}' => $user->balance
                        ];
                        foreach ($messages as $message) {
                            $this->sendMessage($bot, $message, $chat_id, $replacements);
                        }
                    } else {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::get('command_not_found'),
                            'reply_markup' => $this->getMainButtons($settings, $bot)
                        ]);
                    }
                    return response()->json(['ok' => true], 200);
                }
            } elseif ($update_type == 'callback_query') {
                $callback_data = $bot->Callback_Data();
                if ($callback_data == 'boost_channels') {

                } elseif ($callback_data == 'daily_bonus') {
                    if($settings->daily_bonus_status){
                        if($user->daily_bonus_status){
                            $bot->answerCallbackQuery([
                                'callback_query_id' => $bot->Callback_ID(),
                                'text' => Text::get('daily_bonus_already_received')
                            ]);
                        } else{
                            $user->daily_bonus_status = true;
                            $user->balance += $settings->daily_bonus_amount;
                            $user->save();
                            $bot->answerCallbackQuery([
                                'callback_query_id' => $bot->Callback_ID(),
                                'text' => Text::get('daily_bonus_received')
                            ]);
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => Text::get('daily_bonus_received_message'),
                                'reply_markup' => $this->getMainButtons($settings, $bot)
                            ]);
                        }
                    } else{
                        $bot->answerCallbackQuery([
                            'callback_query_id' => $bot->Callback_ID(),
                            'text' => Text::get('daily_bonus_not_available')
                        ]);
                    }
                } elseif ($callback_data == 'withdraw_request') {

                } else{
                    if(stripos($callback_data, 'premium_category_') !== false){
                        $category_id = explode('_', $callback_data)[2];
                        $category = PremiumCategory::find($category_id);

                    }
                }
            }
        }
    }
}
