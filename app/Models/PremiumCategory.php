<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Number;

class PremiumCategory extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'slug',
        'price',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean'
    ];
    public function getPriceAttribute($value){
        return Number::format($value);
    }
}
