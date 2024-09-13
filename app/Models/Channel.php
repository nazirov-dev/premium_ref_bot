<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;
    /*  $table->id();
            $table->bigInteger('channel_id');
            $table->string('username')->nullable();
            $table->string('name');
            $table->string('invite_link');
            $table->boolean('status');
            $table->timestamps(); */
    protected $guarded = ['id'];
    protected $fillable = [
        'channel_id',
        'username',
        'name',
        'invite_link',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean'
    ];
}
