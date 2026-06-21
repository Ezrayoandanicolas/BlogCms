<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DomainTheme extends Model
{
    protected $fillable = ['domain', 'theme_slug', 'settings', 'active'];

    protected $casts = [
        'settings' => 'array',
        'active' => 'boolean',
    ];
}
