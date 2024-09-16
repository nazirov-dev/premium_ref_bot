<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Controllers\TextController as Text;

use App\Models\BotUser;
use App\Models\Button;
use App\Models\Channel;
use App\Models\JoinRequest;
use App\Models\Message;
use App\Models\PremiumCategory;
use App\Models\BoostChannel;
use App\Models\PromoCode;
use App\Models\UserIdentityData;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Number;

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
        $button = Button::where(['slug' => 'giveaway_button'])->first();
        if ($button->status) {
            $keyboard[] = [['text' => $button->name], ['text' => Text::get('top_referrers_button_label')]];
        }

        $keyboard[] = [['text' => Button::where(['slug' => 'premium_prices_button'])->first()->name], ['text' => Text::get('my_balance_button_label')]];
        if ($settings->bonus_menu_status) {
            $keyboard[] = [['text' => Text::get('bonus_menu_button_label')]];
        }
        $keyboard[] = [['text' => Button::where(['slug' => 'instructions_button'])->first()->name], ['text' => Button::where(['slug' => 'administrator_button'])->first()->name]];

        return $bot->buildKeyBoard($keyboard, resize: true);
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
        $bot->sendMessage([
            'chat_id' => $chat_id,
            'text' => json_encode([
                'update_type' => $update_type,
            ])
        ]);
        $user = BotUser::where('user_id', $chat_id)->first();
        if ($user->is_banned) {
            $bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => Text::get('you_are_banned'),
                'reply_markup' => null,
            ]);
            return response()->json(['ok' => true], 200);
        }

        //cached settings for 1 day
        $settings = Cache::remember('bot_settings', 60 * 60 * 24, function () {
            return json_encode(\App\Models\Setting::first()->toArray());
        });
        $settings = json_decode($settings);
        if (!is_null($text)) {
            // user model
            if (!$user->is_verified) {
                $bot->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => Text::get('lets_verify_you_are_not_robot'),
                    'reply_markup' => $bot->buildKeyBoard([
                        [['text' => Text::get('verify_not_robot_button'), 'web_app' => ['url' => config('app.url') . '/verify-not-robot']]]
                    ], true, true)
                ]);
                return response()->json(['ok' => true], 200);
            }
            // check if user not exists in database
            if (is_null($user)) {
                $referral_id = null;
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
                    'referrer_id' => $referral_id,
                    'is_premium' => $bot->isPremiumUser(),
                    'is_banned' => false,
                    'is_verified' => false
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

            if (!is_null($step)) {
                if ($step == 'start') {
                    if (!is_null($user->referrer_id) and $settings->referral_status) {
                        if ($bot->isPremiumUser() and $settings->premium_referral_status) {
                            $bonus = $settings->premium_referral_bonus;
                        } else {
                            $bonus = $settings->referral_bonus;
                        }
                        $referrer = BotUser::where('user_id', $user->referrer_id)->first();
                        if ($referrer) {
                            $referrer->balance += $bonus;
                            $referrer->save();
                        }
                        $bot->sendMessage([
                            'chat_id' => $user->referrer_id,
                            'text' => $this->replacePlaceholders(Text::get('referral_bonus_message'), [
                                '{first_name}' => $bot->FirstName(),
                                '{last_name}' => $bot->LastName(),
                                '{username}' => $bot->Username(),
                                '{user_id}' => $chat_id,
                                '{bonus}' => $bonus,
                                '{new_balance}' => Number::format($referrer->balance)
                            ]),
                            'parse_mode' => 'HTML'
                        ]);
                    }
                    $user->balance = 0;
                    $user->save();
                    Cache::forget($chat_id . '.step');
                } else {
                    if (stripos($step, 'reject_promo_code_') !== false and $chat_id == $settings->admin_id) {
                        $promo_code_id = explode('_', $step)[3];
                        $promo_code = PromoCode::find($promo_code_id);
                        if ($promo_code) {
                            $promo_code->status = 'rejected';
                            $promo_code->reject_reason = $text;
                            $promo_code->save();

                            $promo_code_rejected_proof_message = $this->replacePlaceholders(Text::get('promo_code_rejected_proof_message'), [
                                '{promo_code}' => $promo_code->code,
                                '{category_name}' => $promo_code->category->name,
                                '{price}' => Number::format($promo_code->price),
                                '{user_id}' => $promo_code->user_id,
                                '{now}' => now()->format('Y-m-d H:i:s'),
                                '{reject_reason}' => $text
                            ]);
                            $bot->sendMessage([
                                'chat_id' => $promo_code->user_id,
                                'text' => $promo_code_rejected_proof_message
                            ]);

                            $bot->sendMessage([
                                'chat_id' => $settings->admin_id,
                                'text' => "Promo code rad etildi:\n\nPromo code: {$promo_code->code}\nRad etilish sababi: $text"
                            ]);
                            Cache::forget($chat_id . '.step');
                        } else {
                            $bot->sendMessage([
                                'chat_id' => $chat_id,
                                'text' => 'Promo code bazadan topilmadi!',
                                'reply_markup' => $this->getMainButtons($settings, $bot)
                            ]);
                            Cache::forget($chat_id . '.step');
                        }
                        return response()->json(['ok' => true], 200);
                    }
                }
            }
            if ($text == '/webapp') {
                $bot->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => "Here is your web app",
                    'reply_markup' => json_encode([
                        'keyboard' => [
                            [['text' => 'Web app', 'web_app' => ['url' => 'https://admin.samarkand24.live/check-bot']]]
                        ]
                    ])
                ]);
                return response()->json(['ok' => true], 200);
            }
            if (empty($user->phone_number)) {
                if ($update_type == 'contact' and $bot->getContactUserId() == $chat_id) {
                    $phone_number = preg_replace('/\D/', '', $bot->Text());
                    if (!preg_match("/^\+?998\d{9}$/", $phone_number)) {
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => Text::get('phone_number_invalid'),
                            'reply_markup' => $bot->buildKeyBoard([
                                [['text' => Text::get('send_phone_number'), 'request_contact' => true]]
                            ], true, true)
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    $user->phone_number = $phone_number;
                    $user->save();
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => Text::get('phone_number_saved'),
                        'reply_markup' => $bot->buildKeyBoard([
                            [['text' => Text::get('verify_not_robot_button'), 'web_app' => ['url' => config('app.url') . '/verify-not-robot']]]
                        ])
                    ]);
                    return response()->json(['ok' => true], 200);
                }
                $bot->sendMessage([
                    'chat_id' => $chat_id,
                    'text' => Text::get('phone_number_request'),
                    'reply_markup' => $bot->buildKeyBoard([
                        [['text' => Text::get('send_phone_number'), 'request_contact' => true]]
                    ], true, true)
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
                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id
                    ];
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $this->replacePlaceholders($start_message, $replacements),
                        'reply_markup' => $this->getMainButtons($settings, $bot)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($text == Text::get('my_balance_button_label')) {
                    $counts = BotUser::selectRaw('COUNT(*) as total, IFNULL(SUM(is_premium = 0), 0) as frens_count, IFNULL(SUM(is_premium = 1), 0) as frens_premium_count')
                        ->where('referrer_id', $chat_id)
                        ->first();

                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id,
                        '{phone_number}' => $user->phone_number,
                        '{balance}' => Number::format($user->balance),
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
                } elseif ($text == Text::get('top_referrers_button_label')) {
                    if (!$settings->referral_status) {
                        $referral_system_is_not_active_message = Text::get('referral_system_is_not_active');
                        $replacements = [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id
                        ];
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $this->replacePlaceholders($referral_system_is_not_active_message, $replacements),
                            'reply_markup' => $this->getMainButtons($settings, $bot)
                        ]);
                        return response()->json(['ok' => true], 200);
                    }

                    $top_users = BotUser::orderBy('balance', 'desc')->limit($settings->top_users_count)->get();
                    $top_users_message = Text::get('top_users_message');
                    $top_users_list = '';
                    $each_user_message_in_top = Text::get('each_user_message_in_top');
                    foreach ($top_users as $key => $user) {
                        $top_users_list .= $this->replacePlaceholders($each_user_message_in_top, [
                            '{number}' => $key + 1,
                            '{name}' => $user->name,
                            '{balance}' => Number::format($user->balance)
                        ]) . PHP_EOL;
                    }
                    $replacements = [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id,
                        '{top_users_list}' => $top_users_list
                    ];
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $this->replacePlaceholders($top_users_message, $replacements),
                        'reply_markup' => $this->getMainButtons($settings, $bot)
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
                } elseif ($text == '/panel' and in_array($chat_id, [$settings->admin_id, config('env.DEV_ID')])) {
                    $admin_dashboard_url = config('app.url') . "/admin";
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => "Quyidagi ssilka orqali panelga kirishingiz mumkin:\n\n$admin_dashboard_url",
                        'reply_markup' => json_encode([
                            'inline_keyboard' => [
                                [['text' => 'Panelga kirish', 'url' => $admin_dashboard_url]]
                            ]
                        ])
                    ]);
                    return response()->json(['ok' => true], 200);
                } else {
                    $findButton = Button::where(['name' => $text, 'status' => true])->first();
                    if ($findButton) {
                        $messages = $findButton->getMessages();
                        $replacements = [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id,
                            '{phone_number}' => $user->phone_number,
                            '{balance}' => Number::format($user->balance)
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
                    if (!$settings->bonus_menu_status) {
                        $bot->answerCallbackQuery([
                            'callback_query_id' => $bot->Callback_ID(),
                            'text' => Text::get('bonus_menu_not_available'),
                            'show_alert' => true
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                    $boost_channels = BoostChannel::where(['status' => true])->get();
                    $boost_channels_buttons = [];
                    $boost_price_info_message = '';
                    foreach ($boost_channels as $channel) {
                        $boost_price_info_message .= $this->replacePlaceholders(Text::get('boost_price_info_message'), [
                            '{price}' => $channel->bonus_each_boost,
                            '{name}' => $channel->name
                        ]);
                        $boost_channels_buttons[] = [['text' => $channel->name, 'url' => $channel->boost_link]];
                    }
                    $boost_channels_message = $this->replacePlaceholders(Text::get('boost_channels_message'), [
                        '{boost_price_info}' => $boost_price_info_message
                    ]);
                    $bot->editMessageText([
                        'chat_id' => $chat_id,
                        'message_id' => $bot->MessageID(),
                        'text' => $boost_channels_message,
                        'reply_markup' => $bot->buildInlineKeyboard($boost_channels_buttons)
                    ]);
                    return response()->json(['ok' => true], 200);
                } elseif ($callback_data == 'daily_bonus') {
                    if ($settings->daily_bonus_status) {
                        if ($user->daily_bonus_status) {
                            $bot->answerCallbackQuery([
                                'callback_query_id' => $bot->Callback_ID(),
                                'text' => Text::get('daily_bonus_already_received'),
                                'show_alert' => true
                            ]);
                        } else {
                            $boost_channels = BoostChannel::where(['status' => true])->get();
                            $boosts_message_text = Text::get('boosts_message');
                            $total_bonus = 0;
                            $boosted = false;
                            foreach ($boost_channels as $channel) {
                                $boosts = $bot->getUserChatBoosts([
                                    'chat_id' => $channel->channel_id,
                                    'user_id' => $chat_id
                                ]);
                                $boost_count = count($boosts['result']['boosts']) ?? 0;
                                if ($boost_count > 0) {
                                    $boosted = true;
                                    if ($channel->daily_bonus_type == 'simple') {
                                        $user->balance += $channel->daily_bonus;
                                        $boosts_message_text .= $this->replacePlaceholders(Text::get('boosts_message_each'), [
                                            '{channel_name}' => $channel->name,
                                            '{boosts_count}' => $boost_count,
                                            '{bonus}' => $channel->daily_bonus
                                        ]) . PHP_EOL;
                                        $total_bonus += $channel->daily_bonus;
                                    } else {
                                        $user->balance += $boost_count * $channel->daily_bonus_each_boost;
                                        $boosts_message_text .= $this->replacePlaceholders(Text::get('boosts_message_each'), [
                                            '{channel_name}' => $channel->name,
                                            '{boosts_count}' => $boost_count,
                                            '{bonus}' => $boost_count * $channel->daily_bonus_each_boost
                                        ]) . PHP_EOL;
                                        $total_bonus += $boost_count * $channel->daily_bonus_each_boost;
                                    }
                                }
                                if ($settings->bonus_type == 'only_first_channel') {
                                    break;
                                }
                            }
                            if (!$boosted) {
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => Text::get('daily_bonus_not_received'),
                                    'show_alert' => true
                                ]);
                                return response()->json(['ok' => true], 200);
                            } else {
                                $user->daily_bonus_status = true;
                                $user->save();
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => Text::get('daily_bonus_received'),
                                    'show_alert' => true
                                ]);
                                $bot->deleteThisMessage();
                                $replacements = [
                                    '{first_name}' => $bot->FirstName(),
                                    '{last_name}' => $bot->LastName(),
                                    '{username}' => $bot->Username(),
                                    '{user_id}' => $chat_id,
                                    '{boosts_message}' => $boosts_message_text,
                                    '{total_bonus}' => $total_bonus
                                ];
                                $info_message = $this->replacePlaceholders(Text::get('daily_bonus_received_info'), $replacements);
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => $info_message,
                                    'reply_markup' => $this->getMainButtons($settings, $bot)
                                ]);
                            }
                        }
                        return response()->json(['ok' => true], 200);
                    } else {
                        $bot->answerCallbackQuery([
                            'callback_query_id' => $bot->Callback_ID(),
                            'text' => Text::get('daily_bonus_not_available'),
                            'show_alert' => true
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                } elseif ($callback_data == 'withdraw_request') {
                    $minimum_withdraw_amount = PremiumCategory::where(['status' => true])->min('price');
                    if ($user->balance < $minimum_withdraw_amount) {
                        $bot->answerCallbackQuery([
                            'callback_query_id' => $bot->Callback_ID(),
                            'text' => $this->replacePlaceholders(Text::get('minimum_withdraw_amount'), [
                                '{amount}' => $minimum_withdraw_amount
                            ]),
                            'show_alert' => true
                        ]);
                        return response()->json(['ok' => true], 200);
                    } else {
                        $premium_categories = PremiumCategory::where(['status' => true])->get();
                        $premium_categories_buttons = [];
                        $premium_categories_message = '';
                        foreach ($premium_categories as $category) {
                            $premium_categories_buttons[] = [
                                [
                                    'text' => $category->name . " - " . Number::format($category->price) . " so'm " . ($minimum_withdraw_amount > $category->price ? "❌" : "✅"),
                                    'callback_data' => 'premium_category_' . $category->id
                                ]
                            ];
                            $premium_categories_message .= $this->replacePlaceholders(Text::get('premium_categories_message'), [
                                '{price}' => Number::format($category->price),
                                '{name}' => $category->name
                            ]) . PHP_EOL;
                        }
                        $info_message = $this->replacePlaceholders(Text::get(key: 'withdraw_request_info'), [
                            '{balance}' => Number::format($user->balance),
                            '{minimum_withdraw_amount}' => $minimum_withdraw_amount,
                            '{premium_categories}' => $premium_categories_message
                        ]);
                        $bot->editMessageText([
                            'chat_id' => $chat_id,
                            'message_id' => $bot->MessageID(),
                            'text' => $info_message,
                            'reply_markup' => $bot->buildInlineKeyBoard($premium_categories_buttons)
                        ]);
                        return response()->json(['ok' => true], 200);
                    }
                } else {
                    if (stripos($callback_data, 'premium_category_') !== false) {
                        $category_id = explode('_', $callback_data)[2];
                        $category = PremiumCategory::find($category_id);
                        if ($category) {
                            if ($user->balance < $category->price) {
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => $this->replacePlaceholders(Text::get('not_enough_balance'), [
                                        '{balance}' => Number::format($user->balance),
                                        '{price}' => Number::format($category->price)
                                    ]),
                                    'show_alert' => true
                                ]);
                                return response()->json(['ok' => true], 200);
                            } else {
                                $user->balance -= $category->price;
                                $user->save();
                                $promo_code = Str::random(8);
                                $expire_date = ($settings->promo_code_expire_days > 0) ? now()->addDays($settings->promo_code_expire_days) : null;
                                $promo_code = PromoCode::create([
                                    'code' => $promo_code,
                                    'user_id' => $chat_id,
                                    'premium_category_id' => $category->id,
                                    'price' => Number::format($category->price),
                                    'expired_at' => $expire_date
                                ]);
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => $this->replacePlaceholders(Text::get('withdraw_request_success'), [
                                        '{balance}' => Number::format($user->balance),
                                        '{price}' => Number::format($category->price),
                                        '{category_name}' => $category->name,
                                        '{promo_code}' => $promo_code->code,
                                        '{expired_at}' => (is_null($expire_date) ? 'Cheksiz' : $expire_date->format('Y-m-d H:i:s'))
                                    ]),
                                    'show_alert' => true
                                ]);
                                $bot->sendMessage([
                                    'chat_id' => $chat_id,
                                    'text' => $this->replacePlaceholders(Text::get('withdraw_request_success_message'), [
                                        '{balance}' => Number::format($user->balance),
                                        '{price}' => Number::format($category->price),
                                        '{category_name}' => $category->name,
                                        '{promo_code}' => $promo_code->code,
                                        '{expired_at}' => (is_null($expire_date) ? 'Cheksiz' : $expire_date->format('Y-m-d H:i:s'))
                                    ]),
                                    'reply_markup' => $this->getMainButtons($settings, $bot)
                                ]);
                                $bot->deleteThisMessage();
                                $bot->sendMessage([
                                    'chat_id' => $settings->admin_id,
                                    'text' => "Yangi promo code ro'yhatdan o'tdi:\n\nPromo code: {$promo_code->code}\nYaroqlilik muddati: " . (is_null($expire_date) ? 'Cheksiz' : $expire_date->format('Y-m-d H:i:s')) . "\nKategoriya: " . $category->name . "\nNarxi: " . Number::format($category->price) . " so'm",
                                    'reply_markup' => $bot->buildInlineKeyboard([
                                        [['text' => "Tasdiqlash ✅", 'callback_data' => 'promo_code_accepted_' . $promo_code->id]],
                                        [['text' => "Rad etish ❌", 'callback_data' => 'promo_code_rejected_' . $promo_code->id]]
                                    ])
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        }
                    }

                    if ($chat_id == $settings->admin_id) {
                        if (stripos($callback_data, 'promo_code_accepted_') !== false) {
                            $promo_code_id = explode('_', $callback_data)[3];
                            $promo_code = PromoCode::with(['user', 'category'])->find($promo_code_id);
                            if ($promo_code) {
                                $promo_code->status = 'completed';
                                $promo_code->save();
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => 'Promo code tasdiqlandi va kanalga isbot yuborildi ✅',
                                    'show_alert' => true
                                ]);
                                $bot->editMessageReplyMarkup([
                                    'chat_id' => $chat_id,
                                    'message_id' => $bot->MessageID(),
                                    'reply_markup' => $bot->buildInlineKeyboard([
                                        [['text' => '✅ Tasdiqlangan', 'callback_data' => 'accepted_' . $promo_code->id]]
                                    ])
                                ]);
                                $proof_message = $this->replacePlaceholders(Text::get('proof_message'), [
                                    '{promo_code}' => $promo_code->code,
                                    '{category_name}' => $promo_code->category->name,
                                    '{price}' => Number::format($promo_code->price),
                                    '{user_id}' => $promo_code->user_id,
                                    '{first_name}' => $promo_code->user->name,
                                    '{username}' => $promo_code->user->username,
                                    '{now}' => now()->format('Y-m-d H:i:s')
                                ]);
                                $bot->sendMessage([
                                    'chat_id' => $settings->proof_channel_id,
                                    'text' => $proof_message
                                ]);
                                return response()->json(['ok' => true], 200);
                            } else {
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => 'Bu promo code bazadan topilmadi!',
                                    'show_alert' => true
                                ]);
                                $bot->editMessageReplyMarkup([
                                    'chat_id' => $chat_id,
                                    'message_id' => $bot->MessageID(),
                                    'reply_markup' => $bot->buildInlineKeyboard([
                                        [['text' => 'Bazadan topilmadi 🔍', 'callback_data' => 'notfound_' . $promo_code_id]]
                                    ])
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        } elseif (stripos($callback_data, 'promo_code_rejected_') !== false) {
                            $promo_code_id = explode('_', $callback_data)[3];
                            $promo_code = PromoCode::find($promo_code_id);
                            if ($promo_code) {
                                Cache::set($chat_id . '.step', 'reject_promo_code_' . $promo_code_id);
                                $bot->answerCallbackQuery([
                                    'callback_query_id' => $bot->Callback_ID(),
                                    'text' => $promo_code->code . ' promo code rad etildi. Rad etish sababini kiriting:',
                                    'show_alert' => true
                                ]);
                                $bot->editMessageText([
                                    'chat_id' => $chat_id,
                                    'message_id' => $bot->MessageID(),
                                    'text' => 'Rad etish sababini kiriting:'
                                ]);
                                return response()->json(['ok' => true], 200);
                            }
                        }
                    }
                }
            }
        }
        if (in_array($update_type, ['photo', 'video']) and in_array($chat_id, [$settings->admin_id, config('env.DEV_ID')])) {
            if ($update_type === 'photo') {
                $file_id = $bot->getPhotoFileId();
            } else {
                $file_id = $bot->getData()['message']['video']['file_id'];
            }
            $bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => "File ID: <code>$file_id</code>"
            ]);
            return response()->json(['ok' => true], 200);
        }
        if ($update_type == 'chat_boost') {
            if ($settings->bonus_menu_status) {
                $chat_id = $bot->ChatID();
                $boost_channel = BoostChannel::where(['channel_id' => $chat_id, 'status' => true])->first();
                if ($boost_channel) {
                    $user_id = $bot->UserID();
                    $user = BotUser::where('user_id', $user_id)->first();
                    if ($user) {
                        $user->balance += $boost_channel->bonus_each_boost;
                        $user->save();
                        $bot->getUserChatBoosts([
                            'chat_id' => $chat_id,
                            'user_id' => $user_id,
                        ]);
                        $boosts_count = count($boosts['result']['boosts'] ?? []);
                        $bot->sendMessage([
                            'chat_id' => $user_id,
                            'text' => $this->replacePlaceholders(Text::get('boost_received'), [
                                '{bonus}' => Number::format($boost_channel->bonus_each_boost),
                                '{channel_name}' => $boost_channel->name,
                                '{boosts_count}' => $boosts_count
                            ])
                        ]);
                    }
                }
            }
            return response()->json(['ok' => true], 200);
        } elseif ($update_type == 'removed_chat_boost') {
            if (!$settings->bonus_menu_status) {
                return response()->json(['ok' => true], 200);
            }
            $chat_id = $bot->ChatID();
            $boost_channel = BoostChannel::where(['channel_id' => $chat_id, 'status' => true])->first();
            if ($boost_channel) {
                $user_id = $bot->UserID();
                $user = BotUser::where('user_id', $user_id)->first();
                if ($user) {
                    $user->balance -= $boost_channel->bonus_each_boost;
                    $user->save();
                    $boosts = $bot->getUserChatBoosts([
                        'chat_id' => $chat_id,
                        'user_id' => $user_id
                    ]);
                    $boosts_count = count($boosts['result']['boosts'] ?? []);
                    $bot->sendMessage([
                        'chat_id' => $user_id,
                        'text' => $this->replacePlaceholders(Text::get('boost_removed'), [
                            '{channel_name}' => $boost_channel->name,
                            '{boosts_count}' => $boosts_count,
                            '{balance}' => Number::format($user->balance),
                            '{minus}' => Number::format($boost_channel->bonus_each_boost)
                        ])
                    ]);
                }
            }
            return response()->json(['ok' => true], 200);
        } elseif ($update_type == 'web_app_data') {
            $web_app_data = $bot->webAppData();
            $bot->sendMessage([
                'chat_id' => $chat_id,
                'text' => "web app data: " . json_encode($web_app_data, 128)
            ]);
            $button_text = $web_app_data['button_text'];
            $data = json_decode($web_app_data['data'], true)['info'];
            $data['user_id'] = $chat_id;
            if ($button_text == "Tasdiqlash ✅\n") {
                $fingerprint = $data['fingerprint'];
                $check_fingerprint = UserIdentityData::where('fingerprint', $fingerprint)->first();
                if ($check_fingerprint) {
                    if ($settings->multi_account_action == 'warn') {
                        $warn_message = $this->replacePlaceholders(Text::get('multi_account_warn_message'), [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id
                        ]);
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $warn_message,
                            'reply_markup' => $this->getMainButtons($settings, $bot)
                        ]);
                        $user = BotUser::where(['user_id' => $chat_id])->first();
                        $user->is_banned = false;
                        $user->is_verified = true;
                        $user->save();
                        return response()->json(['ok' => true], 200);
                    } elseif ($settings->multi_account_action == 'ban') {
                        $ban_message = $this->replacePlaceholders(Text::get('multi_account_ban_message'), [
                            '{first_name}' => $bot->FirstName(),
                            '{last_name}' => $bot->LastName(),
                            '{username}' => $bot->Username(),
                            '{user_id}' => $chat_id
                        ]);
                        $bot->sendMessage([
                            'chat_id' => $chat_id,
                            'text' => $ban_message,
                            'reply_markup' => null
                        ]);
                        $user = BotUser::where(['user_id' => $chat_id])->first();
                        $user->is_banned = true;
                        $user->is_verified = false;
                        $user->save();
                        return response()->json(['ok' => true], 200);
                    } else {
                        return response()->json(['ok' => true], 200);
                    }
                } else {
                    UserIdentityData::create($data);
                    $start_message = $this->replacePlaceholders(Text::get('start_message'), [
                        '{first_name}' => $bot->FirstName(),
                        '{last_name}' => $bot->LastName(),
                        '{username}' => $bot->Username(),
                        '{user_id}' => $chat_id
                    ]);
                    $bot->sendMessage([
                        'chat_id' => $chat_id,
                        'text' => $start_message,
                        'reply_markup' => $this->getMainButtons($settings, $bot)
                    ]);
                    $user = BotUser::where(['user_id' => $chat_id])->first();
                    $user->is_banned = false;
                    $user->is_verified = true;
                    $user->save();
                    return response()->json(['ok' => true], 200);
                }
            }
        }
    }
}
