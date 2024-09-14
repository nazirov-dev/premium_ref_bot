<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBotUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bot_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('username')->nullable()->default(null);
            $table->string('phone_number')->nullable()->default(null);
            $table->decimal('balance', 10, 2, true)->default(0)->nullable();
            $table->bigInteger('referrer_id')->nullable()->default(null); // Referrer ID
            $table->boolean('is_premium')->default(false);
            $table->boolean('daily_bonus_status')->default(false);
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bot_users');
    }
}
