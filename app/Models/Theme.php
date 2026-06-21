<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Theme extends Model
{
    protected $fillable = ['name', 'slug', 'author', 'version', 'description', 'thumbnail', 'status'];
    
    protected $casts = [
        'status' => 'boolean',
    ];
}
