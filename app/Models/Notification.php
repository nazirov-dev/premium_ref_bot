<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;
    protected $fillable = [
        'status',
        'admin_chat_id', //
        'message_id', //
        'filter_by_language', //
        'keyboard',
        'admin_info_message_id',
        'sent',
        'not_sent',
        'sending_type', //
        'sending_end_time',
    ];
}
