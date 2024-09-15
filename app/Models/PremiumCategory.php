<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
