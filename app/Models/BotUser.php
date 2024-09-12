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
        'user_id', 'lang_code', 'name', 'username', 'status', 'created_at', 'updated_at'
    ];

}
