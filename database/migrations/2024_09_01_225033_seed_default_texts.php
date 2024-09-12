<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Text;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $texts = [
            'join_channels' => "Botdan foydalanish uchun kanallarimizga obuna bo'ling!",
            'check_button_lebel' => "Tekshirish",
            'you_are_still_not_member'=> "Siz hali kanallarimizga a'zo emassiz. Iltimos, quyidagi tugmalarga bosib kanallarimizga obuna bo'ling. Undan so'ng 'Tekshirish' tugmasini bosing.",
        ];

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
