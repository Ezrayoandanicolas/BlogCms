<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Backlink extends Model
{
    use HasTenant;

    protected $fillable = ['site_id', 'post_id', 'anchor_text', 'target_url', 'link_type', 'status', 'domain_id'];

    public function site()
    {
        return $this->belongsTo(BacklinkSite::class, 'site_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function logs()
    {
        return $this->hasMany(BacklinkLog::class);
    }
}
