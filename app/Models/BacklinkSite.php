<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class BacklinkSite extends Model
{
    use HasTenant;

    protected $fillable = ['domain', 'status', 'domain_id'];

    public function backlinks()
    {
        return $this->hasMany(Backlink::class, 'site_id');
    }
}
