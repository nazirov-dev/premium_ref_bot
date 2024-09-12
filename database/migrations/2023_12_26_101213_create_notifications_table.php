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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('waiting');
            $table->string('admin_chat_id')->nullable();
            $table->integer('message_id')->nullable();
            $table->string('keyboard', 15000)->default(null)->nullable();
            $table->integer('admin_info_message_id')->nullable();
            $table->integer('sent')->default(0);
            $table->integer('not_sent')->default(0);
            $table->string('sending_type')->default('copymessage');
            $table->timestamp('sending_end_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
