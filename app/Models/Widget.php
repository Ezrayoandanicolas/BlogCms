<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Widget extends Model
{
    protected $fillable = ['name', 'type', 'title', 'status'];
    
    protected $casts = [
        'status' => 'boolean',
    ];
}
