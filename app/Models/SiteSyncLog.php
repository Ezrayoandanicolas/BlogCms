<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteSyncLog extends Model
{
    protected $fillable = ['site_id', 'post_id', 'sync_status', 'synced_at'];
    
    protected $casts = [
        'synced_at' => 'datetime',
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
