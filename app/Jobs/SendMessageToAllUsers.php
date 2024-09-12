<?php

namespace App\Jobs;

use App\Models\BotUser;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Services\TelegramService;
use App\Models\NotificationStatus;
use Illuminate\Support\Facades\Log;

class SendMessageToAllUsers implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * Create a new job instance.
     */
    protected $postData;
    public function __construct(array $postData)
    {
        $this->postData = $postData;
    }

    /**
     * Execute the job.
     */

    public function updateNotificationStatus($notificationStatus, $totalSent, $totalNotSent, ?array $logEntries = null, ?string $status = null, $notification_id = null): void
    {
        if (!is_null($notification_id)) {
            $notificationStatus->notification_id = $notification_id;
        }
        $notificationStatus->sent = $totalSent;
        $notificationStatus->not_sent = $totalNotSent;
        if (!is_null($status)) {
            $notificationStatus->status = $status;
        }
        if (!is_null($logEntries)) {
            $notificationStatus->log = json_encode($logEntries);
        }

        $notificationStatus->save();
    }
    private function checkProcessStopped($NotificationStatus): void
    {
        $status = $NotificationStatus->where('id', 1)->value('status');

        if ($status === 'stopped') {
            throw new \Exception('Process stopped.');
        }
    }
    public function handle(): void
    {
        define('CHUNK_COUNT', 50);
        $admin = config('env.ADMIN_ID', 1996292437);
        $ads_channel = $this->postData['from_chat_id'] ?? config('env.ADS_TELEGRAM_CHANNEL');
        $bot = new TelegramService();
        $forwardResponse = $bot->forwardMessage([
            'chat_id' => $admin,
            'message_id' => $this->postData['message_id'],
            'from_chat_id' => $ads_channel
        ]);
        // Log::info('Response: ', $forwardResponse);
        $NotificationStatus = new NotificationStatus();
        $NotificationStatus = $NotificationStatus->where(['id' => 1])->first();
        if ($forwardResponse['ok'] == false) {
            $NotificationStatus->log = json_encode(['error' => true, 'message' => "Kanaldan xabarni olishda muammo yuzaga keldi, bot kanalda adminligiga ishonch xosil qiling, va xabar id raqami to'g'ri ekanligini tekshiring"]);
            $NotificationStatus->status = 'off';
            $NotificationStatus->sent = 0;
            $NotificationStatus->not_sent = 0;
            $NotificationStatus->save();
            return;
        }

        $limit = 20; // limit per second
        $microseconds_per_request = 1000000 / $limit;

        $numMessagesSent = 0;
        $numMessagesNotSent = 0;
        $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Xabar yuborish boshlandi", 'status' => 'is_being_sent'], 'sending', $this->postData['id']);
        if ($this->postData['sending_type'] == 'simple') {
            // Log::info('types and data: ', [
            //     'type_of_reply_markup' => gettype($forwardResponse['result']['reply_markup']),
            //     'type_of_result' => gettype($forwardResponse)
            // ]);
            $keyboard = (!empty($forwardResponse['result']['reply_markup']) and $this->postData['with_keyboard']) ? json_encode($forwardResponse['result']['reply_markup']) : json_encode(['inline_keyboard' => [[['text' => '', 'url' => "https://nazirov.uz"]]]]);
            try {
                $this->checkProcessStopped($NotificationStatus);
                BotUser::select('user_id')->chunk(CHUNK_COUNT, function ($users) use (&$bot, &$keyboard, &$microseconds_per_request, &$numMessagesNotSent, &$numMessagesSent, &$NotificationStatus) {
                    // Log::info('Users', $users->toArray());
                    foreach ($users as $user) {
                        $result_of_send = $bot->copyMessage([
                            'chat_id' => $user['user_id'],
                            'message_id' => $this->postData['message_id'],
                            'from_chat_id' => $this->postData['from_chat_id'],
                            'reply_markup' => $keyboard
                        ]);
                        $numMessagesSent += $result_of_send['ok'];
                        $numMessagesNotSent += !$result_of_send['ok'];
                        if (!$result_of_send['ok'] && $result_of_send['error_code'] == 429) {
                            $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Telegram cheklov qo'ydi, cheklovdan chiqish kutilmoqda"], 'pause');
                            sleep($result_of_send['parameters']['retry_after']);
                        }
                        usleep($microseconds_per_request);
                    }
                    $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Xabar yuborilmoqda"], "sending");
                    sleep(5);
                });
            } catch (\Exception $e) {
                if ($e->getMessage() == 'Process stopped.') {
                    $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Xabar yuborish admin tomonidan to'xtatildi"], "stopped");
                    exit;
                }
            }
        } elseif ($this->postData['sending_type'] == 'forward') {
            try {
                BotUser::select('user_id')->chunk(CHUNK_COUNT, function ($users) use (&$bot, &$microseconds_per_request, &$numMessagesNotSent, &$numMessagesSent, &$NotificationStatus) {
                    $this->checkProcessStopped($NotificationStatus);
                    foreach ($users as $user) {
                        $result_of_send = $bot->forwardMessage([
                            'chat_id' => $user['user_id'],
                            'message_id' => $this->postData['message_id'],
                            'from_chat_id' => $this->postData['from_chat_id']
                        ]);
                        $numMessagesSent += $result_of_send['ok'];
                        $numMessagesNotSent += !$result_of_send['ok'];
                        if (!$result_of_send['ok'] && $result_of_send['error_code'] == 429) {
                            $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Telegram cheklov qo'ydi, cheklovdan chiqish kutilmoqda"], "pause");
                            sleep($result_of_send['parameters']['retry_after']);
                        }
                        usleep($microseconds_per_request);
                    }
                    $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Xabar yuborilmoqda"], "sending");
                });
                sleep(5);
            } catch (\Exception $e) {
                if ($e->getMessage() == 'Process stopped.') {
                    $this->updateNotificationStatus($NotificationStatus, $numMessagesSent, $numMessagesNotSent, ['message' => "Xabar yuborish admin tomonidan to'xtatildi"], "stopped");
                    exit;
                }
            }
        }
    }
}
