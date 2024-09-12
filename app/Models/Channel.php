<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    protected $fillable = [
        'channel_id', 'username', 'name', 'invite_link', 'status', 'created_at', 'updated_at'
    ];
}
