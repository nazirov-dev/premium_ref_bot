<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PremiumCategory extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected $fillable = [
        'name',
        'slug',
        'price_in_uzs',
        'price_in_stars',
        'count',
        'status'
    ];
}
