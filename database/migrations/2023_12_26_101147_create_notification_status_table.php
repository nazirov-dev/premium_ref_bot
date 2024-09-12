<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notification_status', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('notification_id')->nullable()->default(null);
            $table->boolean('status')->default(false);
            $table->integer('sent')->default(0);
            $table->integer('not_sent')->default(0);
            $table->integer('last_user_index')->default(0);
            $table->integer('telegram_retry_after_seconds')->nullable()->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_status');
    }
};
