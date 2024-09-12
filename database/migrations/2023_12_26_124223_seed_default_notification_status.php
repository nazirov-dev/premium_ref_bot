<?php

use App\Models\NotificationStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        NotificationStatus::create([
            'notification_id' => 0,
            'status' => false,
            'sent' => 0,
            'not_sent' => 0,
            'last_user_index' => 0,
            'telegram_retry_after_seconds' => 0
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        NotificationStatus::find(1)->delete();
    }
};
