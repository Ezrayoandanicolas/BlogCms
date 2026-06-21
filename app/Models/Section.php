<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['layout_id', 'name', 'type', 'position', 'config'];
    
    protected $casts = [
        'config' => 'array',
    ];

    public function layout()
    {
        return $this->belongsTo(Layout::class);
    }
}
