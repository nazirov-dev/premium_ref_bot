<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Message;

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
    public function getMessages()
    {
        return Message::whereIn('id', $this->messages)->get();
    }
}
