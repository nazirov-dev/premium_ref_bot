<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationStatus extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'notification_status';
    protected $fillable = [
        'notification_id',
        'status',
        'sent',
        'not_sent',
        'last_user_index',
        'telegram_retry_after_seconds'
    ];
}
