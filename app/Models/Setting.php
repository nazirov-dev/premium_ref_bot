<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'referral_bonus',
        'premium_referral_bonus',
        'bonus_menu_status',
        'referral_status',
        'premium_referral_status',
        'top_users_count',
        'bonus_type',
        'promo_code_expire_days',
        'admin_id',
        'proof_channel_id',
        'daily_bonus_status',
        'multi_account_action'
    ];
    protected $casts = [
        'bonus_menu_status' => 'boolean',
        'referral_status' => 'boolean',
        'premium_referral_status' => 'boolean',
        'daily_bonus_status' => 'boolean'
    ];

}
