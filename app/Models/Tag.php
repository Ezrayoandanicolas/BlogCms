<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['name', 'slug', 'domain_id'];

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags');
    }
}
