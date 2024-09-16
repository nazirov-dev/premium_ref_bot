<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserIdentityData extends Model
{
    use HasFactory;
    protected $guared = ['id'];
    protected $fillable = [
        'user_id',
        'timeOpened',
        'timezone',
        'browserLanguage',
        'browserPlatform',
        'sizeScreenW',
        'sizeScreenH',
        'sizeAvailW',
        'sizeAvailH',
        'ipAddress',
        'userAgent',
        'fingerprint',
    ];

}
