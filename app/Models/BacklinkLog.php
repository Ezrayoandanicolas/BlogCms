<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BacklinkLog extends Model
{
    protected $fillable = ['backlink_id', 'response_code', 'is_live', 'checked_at'];
    
    protected $casts = [
        'is_live' => 'boolean',
        'checked_at' => 'datetime',
    ];

    public function backlink()
    {
        return $this->belongsTo(Backlink::class);
    }
}
