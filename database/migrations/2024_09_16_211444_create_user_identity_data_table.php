<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_identity_data', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('timeOpened')->nullable();
            $table->string('timezone')->nullable();
            $table->string('browserLanguage')->nullable();
            $table->string('browserPlatform')->nullable();
            $table->string('sizeScreenW')->nullable();
            $table->string('sizeScreenH')->nullable();
            $table->string('sizeAvailW')->nullable();
            $table->string('sizeAvailH')->nullable();
            $table->string('ipAddress')->nullable();
            $table->string('userAgent')->nullable();
            $table->string('fingerprint')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_identity_data');
    }
};
