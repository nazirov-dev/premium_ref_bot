<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JoinRequest extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'join_requests';
    protected $fillable = ['user_id', 'chat_id', 'crated_at', 'updated_at'];
}
