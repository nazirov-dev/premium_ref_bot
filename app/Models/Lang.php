<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'langs';
    protected $fillable = [
        'name', 'short_code', 'status', 'created_at', 'updated_at'
    ];
}
