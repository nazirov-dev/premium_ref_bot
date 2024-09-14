<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'channel_id',
        'username',
        'name',
        'invite_link',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean'
    ];
}
