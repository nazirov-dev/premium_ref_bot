<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BotUser extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'bot_users';
    protected $fillable = [
        'user_id',
        'name',
        'username',
        'phone_number',
        'status',
        'balance',
        'referrer_id',
        'is_premium',
        'is_banned',
        'is_verified',
        'daily_bonus_status',
        'status',
    ];
    protected $casts = [
        'status' => 'boolean',
        'is_premium' => 'boolean',
        'daily_bonus_status' => 'boolean',
        'is_banned' => 'boolean',       
        'is_verified' => 'boolean',
    ];
}
