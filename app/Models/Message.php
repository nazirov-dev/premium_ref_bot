<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'text',
        'type',
        'buttons',
        'reply_markup',
        'file_id'
    ];
    protected $casts = [
        'buttons' => 'array',
        'reply_markup' => 'array'
    ];
}
