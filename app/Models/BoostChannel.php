<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BoostChannel extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'channel_id',
        'bonus_each_boost',
        'daily_bonus_each_boost',
        'daily_bonus',
        'daily_bonus_type',
        'boost_link',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean'
    ];

}
