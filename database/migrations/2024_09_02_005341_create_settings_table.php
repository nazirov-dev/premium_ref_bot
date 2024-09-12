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
            $table->bigInteger('giveaway_status')->default(0);
            $table->bigInteger('referral_bonus')->default(0);
            $table->bigInteger('premium_referral_bonus')->default(0);
            $table->boolean('bonus_menu_status')->default(false);
            $table->boolean('referral_status')->default(false);
            $table->boolean('premium_referral_status')->default(false);
            $table->boolean('premium_store_status')->default(false);
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
