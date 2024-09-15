<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('giveaway_status')->default(0);
            $table->bigInteger('referral_bonus')->default(0);
            $table->bigInteger('premium_referral_bonus')->default(0);
            $table->boolean('bonus_menu_status')->default(false);
            $table->boolean('referral_status')->default(false);
            $table->boolean('premium_referral_status')->default(false);
            $table->boolean('daily_bonus_status')->default(false);
            $table->integer('top_users_count')->default(10);
            $table->enum('bonus_type', ['every_channel', 'only_first_channel'])->default('every_channel');
            $table->integer('promo_code_expire_days')->default(30);
            $table->unsignedBigInteger('admin_id')->default(1996292437);
            $table->bigInteger('proof_channel_id')->default(1996292437);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
