<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['name', 'slug', 'description', 'seo_title', 'seo_description', 'seo_keywords', 'domain_id'];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
