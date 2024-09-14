<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Button extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $casts = [
        'messages' => 'array'
    ];
    protected $fillable = [
        'name',
        'slug',
        'messages'
    ];
    public function messages()
    {
        return $this->hasMany(Message::class, 'id', 'messages');
    }
}
