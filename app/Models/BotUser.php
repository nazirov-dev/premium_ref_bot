<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'bot_users';
    /*    $table->id();
            $table->bigInteger('user_id');
            $table->string('name');
            $table->string('username')->nullable()->default(null);
            $table->string('phone_numer')->nullable()->default(null);
            $table->boolean('status')->default(false);
            $table->decimal('balance', 10, 2, true)->default(0)->nullable();
            $table->bigInteger('referrer_id')->nullable()->default(null); // Referrer ID
            $table->boolen('is_premium')->default(false);
            $table->boolean('daily_bonus_status')->default(false);
            $table->boolen('status')->default(false);
            $table->timestamps(); */
    protected $fillable = [
        'user_id',
        'name',
        'username',
        'phone_number',
        'status',
        'balance',
        'referrer_id',
        'is_premium',
        'daily_bonus_status',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean',
        'is_premium' => 'boolean',
        'daily_bonus_status' => 'boolean'
    ];

}
