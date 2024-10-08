<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class PromoCode extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [
        'code',
        'user_id',
        'premium_category_id',
        'price',
        'reject_reason',
        'expired_at',
        'status'
    ];
    protected $casts = [
        'status' => 'string',
        'price' => 'integer',
        'expired_at' => 'datetime',
    ];
    public function user()
    {
        return $this->belongsTo(BotUser::class, 'user_id', 'user_id');
    }
    public function category()
    {
        return $this->belongsTo(PremiumCategory::class, 'premium_category_id');
    }
}
