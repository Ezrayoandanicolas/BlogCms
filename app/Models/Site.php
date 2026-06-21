<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Site extends Model
{
    protected $fillable = ['name', 'domain', 'api_key', 'status'];

    public function syncLogs()
    {
        return $this->hasMany(SiteSyncLog::class);
    }
}
