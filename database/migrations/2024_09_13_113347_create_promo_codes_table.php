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
        Schema::create('promo_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('premium_category_id');
            $table->foreign('premium_category_id')->references('id')->on('premium_categories')->onDelete('cascade');
            $table->unsignedBigInteger('price')->default(0);
            $table->dateTime('expired_at')->nullable();
            $table->enum('status', [
                'active',
                'expired',
                'completed',
                'canceled'
            ])->default('active');
            $table->text('reject_reason');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promo_codes');
    }
};
