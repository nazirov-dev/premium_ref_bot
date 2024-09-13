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
        Schema::create('boost_channels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('channel_id');
            $table->decimal('bonus_each_boost', 10, 2, true)->default(0)->nullable();
            $table->decimal('daily_bonus_each_boost', 10, 2, true)->default(0)->nullable();
            $table->decimal('daily_bonus', 10, 2, true)->default(0)->nullable();
            $table->enum('daily_bonus_type', ['simple', 'bonus_each_boost'])->default('simple');
            $table->boolean('status')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boost_channels');
    }
};
