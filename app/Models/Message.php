<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;
    /*     $table->id();
            $table->string('name');
            $table->string('text', 10000);
            $table->string('type')->default('text');
            $table->string('buttons', 15000)->default(null)->nullable();
            $table->json('reply_markup')->default(null)->nullable();
            $table->string('file_id')->default(null)->nullable();
            $table->timestamps(); */
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
