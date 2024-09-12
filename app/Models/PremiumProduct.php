<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumProduct extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $fillable = [
        'category_id',
        'gift_code_link',
        'status' //enum ['sold', 'available']
    ];
}
