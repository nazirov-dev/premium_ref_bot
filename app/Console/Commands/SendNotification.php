<?php

namespace App\Console\Commands;

use App\Models\BotUser;
use App\Services\TelegramService;
use Illuminate\Console\Command;
use App\Models\NotificationStatus;
use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class SendNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:send-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notification to all active users';

    public function minToReadableTime($minutes = 0)
    {
        if ($minutes == 0) {
            return '1 daqiqa';
        }

        $days = intval(floor($minutes / 1440));
        $hours = intval(floor(($minutes - $days * 1440) / 60));
        $remainingMinutes = intval($minutes % 60);

        $readableTime = '';

        if ($days > 0) {
            $readableTime .= "$days kun ";
        }

        if ($hours > 0) {
            $readableTime .= "$hours soat ";
        }

        if ($remainingMinutes > 0 || $readableTime === '') {
            $readableTime .= "$remainingMinutes daqiqa";
        }

        return trim($readableTime);
    }


    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Notification sender running...");
        $NotificationStatus = NotificationStatus::find(1);
        if ($NotificationStatus->status) {
            if ($NotificationStatus->telegram_retry_after_seconds > 0) {
                if ($NotificationStatus->telegram_retry_after_seconds <= 10) {
                    sleep($NotificationStatus->telegram_retry_after_seconds + 1);
                    $NotificationStatus->telegram_retry_after_seconds = 0;
                } else {
                    $NotificationStatus->telegram_retry_after_seconds -= 60;
                    $NotificationStatus->save();
                    exit;
                }
            } else {
                define('USLEEP', 160000); // usleep

                $bot = new TelegramService();
                $Notification = Notification::find($NotificationStatus->notification_id);

                $users = BotUser::where(['status' => true])->offset($NotificationStatus->last_user_index)->limit(config('env.SEND_PER_MINUTE'))->get();


                if ($users->isEmpty()) {
                    $number_of_attempts = $Notification->sent + $Notification->not_sent;

                    $readable_left_time_text = $this->minToReadableTime($number_of_attempts > 0 ? intval($number_of_attempts / config('env.SEND_PER_MINUTE')) : 0);


                    $send_to = 'Barcha faol foydalanuvchilarga';
                    $info_text = "✅ Yuborilganlar: {$Notification->sent} ta
❌ Yuborilmaganlar: {$Notification->not_sent} ta
⌛️ Xabar yuborish uchun ketgan vaqt: {$readable_left_time_text}.
🎗️ Xabar kimlarga yuboriladi: {$send_to}.

📝 Xabar yuborish statusi: yakunlandi.
⌛ Yuborish boshlangan: {$Notification->created_at}
⏱ Yuborish yakunlandi: " . now()->format('H:i:s d/m/Y');
                    $bot->editMessageText([
                        'chat_id' => $Notification->admin_chat_id,
                        'message_id' => $Notification->admin_info_message_id,
                        'text' => $info_text
                    ]);
                    $bot->sendMessage([
                        'chat_id' => $Notification->admin_chat_id,
                        'text' => "Xabar yuborish yakunlandi ✅",
                        'reply_to_message_id' => $Notification->admin_info_message_id
                    ]);
                    $Notification->status = 'completed';
                    $Notification->sending_end_time = now();
                    $Notification->save();
                    $new_notification = Notification::where(['status' => 'waiting'])->first();
                    if ($new_notification) {
                        $send = $bot->sendMessage([
                            'chat_id' => $Notification->admin_chat_id,
                            'text' => "<b>Keyingi xabarni yuborish 1 daqiqadan so'ng boshlanadi ✅</b>\n\n<i><b>Ushbu xabarni o'chirmang!!!</b></i>"
                        ]);
                        $new_notification->admin_info_message_id = $send['result']['message_id'];
                        $NotificationStatus->status = true;
                        $NotificationStatus->notification_id = $new_notification->id;
                        $NotificationStatus->sent = 0;
                        $NotificationStatus->not_sent = 0;
                        $NotificationStatus->last_user_index = 0;
                        $NotificationStatus->telegram_retry_after_seconds = 0;

                        $new_notification->status = 'sending';
                        $new_notification->save();
                    } else {
                        $NotificationStatus->status = false;
                    }
                    $NotificationStatus->save();
                } else {
                    $ads_channel_id = config('env.ADS_TELEGRAM_CHANNEL_ID');
                    if ($Notification->status == 'sending') {
                        if ($Notification->sending_type == 'copymessage') {
                            $keyboard = (empty($Notification->keyboard) or $Notification->keyboard == 'empty') ? $bot->buildKeyBoardHide() : base64_decode($Notification->keyboard);
                            foreach ($users as $user) {
                                $send_message = $bot->copyMessage([
                                    'chat_id' => $user->user_id,
                                    'from_chat_id' => $ads_channel_id,
                                    'message_id' => $Notification->message_id,
                                    'reply_markup' => $keyboard
                                ]);
                                if ($send_message['ok']) {
                                    $Notification->sent++;
                                    $NotificationStatus->sent++;
                                } else {
                                    if ($send_message['error_code'] != 429) {
                                        $Notification->not_sent++;
                                        $NotificationStatus->not_sent++;
                                        $user->status = false;
                                        $user->save();
                                    } else {
                                        $Notification->save();
                                        $NotificationStatus->telegram_retry_after_seconds = $send_message['parameters']['retry_after'];
                                        $NotificationStatus->save();
                                        exit;
                                    }
                                }
                                $NotificationStatus->last_user_index++;
                                usleep(USLEEP);
                            }

                            // Get the count of all active users and add the count of notifications not sent,
                            // because these users were active and should be included in the total.
                            $all_active_users = BotUser::where(['status' => true])->count() + $Notification->not_sent;

                            $number_of_attempts = $Notification->sent + $Notification->not_sent;
                            $left = $all_active_users - $number_of_attempts;

                            $readable_left_time_text = $this->minToReadableTime($number_of_attempts > 0 ? intval($number_of_attempts / config('env.SEND_PER_MINUTE')) : 0);


                            $send_to = 'Barcha faol foydalanuvchilarga';

                            $info_text = "✅ Yuborilganlar: {$Notification->sent} ta
❌ Yuborilmaganlar: {$Notification->not_sent} ta
⌛️ Xabar yuborish tugatilishigacha qolgan vaqt: {$readable_left_time_text}.
🎗️ Xabar yuborish turi: {$send_to}.

📝 Xabar yuborish statusi: yuborilmoqda.
⏱ Tahrirlangan vaqt: " . date('H:i:s d/m/Y');
                            $bot->editMessageText([
                                'chat_id' => $Notification->admin_chat_id,
                                'message_id' => $Notification->admin_info_message_id,
                                'text' => $info_text,
                                'reply_markup' => $bot->buildInlineKeyboard([
                                    [['text' => "Yuborishni to'xtatish", 'url' => route('stop-sending-notification', ['notification_id' => $Notification->id])]]
                                ])
                            ]);
                            $Notification->save();
                            $NotificationStatus->save();
                        } elseif ($Notification->sending_type == 'forwardmessage') {
                            foreach ($users as $user) {
                                $send_message = $bot->forwardMessage([
                                    'chat_id' => $user->user_id,
                                    'from_chat_id' => $ads_channel_id,
                                    'message_id' => $Notification->message_id
                                ]);
                                if ($send_message['ok']) {
                                    $Notification->sent++;
                                    $NotificationStatus->sent++;
                                } else {
                                    if ($send_message['error_code'] != 429) {
                                        $Notification->not_sent++;
                                        $NotificationStatus->not_sent++;
                                        $user->status = false;
                                        $user->save();
                                    } else {
                                        $Notification->save();
                                        $NotificationStatus->telegram_retry_after_seconds = $send_message['parameters']['retry_after'];
                                        $NotificationStatus->save();
                                        exit;
                                    }
                                }
                                $NotificationStatus->last_user_index++;
                                usleep(USLEEP);
                            }

                            // Get the count of all active users and add the count of notifications not sent,
                            // because these users were active and should be included in the total.
                            $all_active_users = BotUser::where(['status' => true])->count() + $Notification->not_sent;

                            $number_of_attempts = $Notification->sent + $Notification->not_sent;
                            $left = $all_active_users - $number_of_attempts;

                            $readable_left_time_text = $this->minToReadableTime($number_of_attempts > 0 ? intval($number_of_attempts / config('env.SEND_PER_MINUTE')) : 0);


                            $send_to = 'Barcha faol foydalanuvchilarga';


                            $info_text = "✅ Yuborilganlar: {$Notification->sent} ta
❌ Yuborilmaganlar: {$Notification->not_sent} ta
⌛️ Xabar yuborish tugatilishigacha qolgan vaqt: {$readable_left_time_text}.
🎗️ Xabar yuborish turi: {$send_to}.

📝 Xabar yuborish statusi: yuborilmoqda.
⏱ Tahrirlangan vaqt: " . date('H:i:s d/m/Y');
                            $bot->editMessageText([
                                'chat_id' => $Notification->admin_chat_id,
                                'message_id' => $Notification->admin_info_message_id,
                                'text' => $info_text,
                                'reply_markup' => $bot->buildInlineKeyboard([
                                    [['text' => "Yuborishni to'xtatish", 'url' => route('stop-sending-notification', ['notification_id' => $Notification->id])]]
                                ])
                            ]);
                            $Notification->save();
                            $NotificationStatus->save();
                        }
                    }
                }
            }
        } else {
            $bot = new TelegramService();
            $new_notification = Notification::where(['status' => 'waiting'])->first();
            if ($new_notification) {
                $send = $bot->sendMessage([
                    'chat_id' => $new_notification->admin_chat_id,
                    'text' => "<b>Xabar yuborish 1 daqiqadan so'ng boshlanadi ✅</b>\n\n<i><b>Ushbu xabarni o'chirmang!!!</b></i>"
                ]);
                $new_notification->admin_info_message_id = $send['result']['message_id'];
                $new_notification->status = 'sending';

                $NotificationStatus->status = true;
                $NotificationStatus->notification_id = $new_notification->id;
                $NotificationStatus->sent = 0;
                $NotificationStatus->not_sent = 0;
                $NotificationStatus->last_user_index = 0;
                $NotificationStatus->telegram_retry_after_seconds = 0;

                $new_notification->save();
                $NotificationStatus->save();
            }
        }
    }
}
