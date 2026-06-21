<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class ExternalPost extends Model
{
    use HasTenant;

    protected $fillable = ['source', 'external_id', 'post_id', 'last_sync', 'domain_id'];

    protected $casts = [
        'last_sync' => 'datetime',
    ];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
