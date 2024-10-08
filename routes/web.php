<?php


use App\Services\TelegramService;
// use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Models\BotUser;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('https://t.me/jasurpremiumbot');
});
Route::get('/dev', function () {
    return redirect('https://Nazirov-Dev.uz');
});

Route::get('/check-not-robot', function () {
    return view('webapp');
});

Route::get('user_ids_as_file', function(){
    // Fetch all user_ids from the BotUser model
    $userIds = BotUser::pluck('user_id');

    // Create a string from the user IDs, each on a new line
    $userIdsText = $userIds->implode("\n");

    // Define the file name
    $fileName = 'user_ids.txt';

    // Store the user IDs temporarily in a text file
    Storage::put($fileName, $userIdsText);

    // Get the file path
    $filePath = Storage::path($fileName);

    // Return the file as a download response
    return response()->download($filePath)->deleteFileAfterSend(true);
});

Route::get('/stop-sending-notification/{notification_id}', function ($notification_id, Request $request) {
    $notificationStatus = App\Models\NotificationStatus::find(1);
    $notification = App\Models\Notification::find($notification_id);

    $statuses = [
        'waiting' => "Navbatda kutilmoqda",
        'sending' => "Xozirda jo'natilmoqda",
        'terminated' => "Yakunlanmasdan, to'xtatilgan",
        'completed' => "Yuborish yakunlangan"
    ];

    if (!$notification) {
        return $request->has('json') ?
            response()->json(['ok' => false, 'message' => 'Notification not found.'], 404) :
            redirect()->route('filament.admin.pages.dashboard')->withErrors('Notification not found.');
    }

    $isNotificationSendingNow = $notification->status === 'sending';
    $isNotificationIdEqualsToNowSendingId = $notificationStatus->status && $notificationStatus->notification_id === $notification_id;

    if ($isNotificationIdEqualsToNowSendingId) {
        $notificationStatus->update(['status' => false, 'end_sending_time' => now()]);
    }

    if ($isNotificationSendingNow) {
        $notification->update(['status' => 'terminated']);
    }

    if (!$request->has('json')) {
        return redirect()->route('filament.admin.pages.dashboard');
    }

    $response = [
        'ok' => true,
        'color' => $isNotificationSendingNow ? 'success' : 'warning',
        'method' => $isNotificationSendingNow ? 'success' : 'warning',
        'message' => $isNotificationSendingNow ?
            "Xabar yuborish to'xtatildi" :
            "Xozirda ushbu xabar yuborilmayapti, xolati: " . ($statuses[$notification->status] ?? 'Noma\'lum holat'),
    ];

    return response()->json($response);
})->name('stop-sending-notification');


Route::post('/sendMedia', function (Request $request) {
    $fileId = $request->input('file_id');
    $type = $request->input('type');
    $caption = $request->input('description');

    $bot = new TelegramService();
    if ($type == 'photo') {
        $sent = $bot->sendPhoto(['chat_id' => config('env.ADMIN_ID'), 'photo' => $fileId, 'caption' => $caption]);
    } elseif ($type == 'video') {
        $sent = $bot->sendVideo(['chat_id' => config('env.ADMIN_ID'), 'video' => $fileId, 'caption' => $caption]);
    }
    return response()->json(['ok' => $sent['ok']]);
})->name('sendMedia');
