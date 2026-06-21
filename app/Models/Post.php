<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['user_id', 'title', 'slug', 'excerpt', 'content', 'featured_image', 'status', 'published_at', 'category_id', 'seo_title', 'seo_description', 'seo_keywords', 'domain_id'];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags');
    }

    public function backlinks()
    {
        return $this->hasMany(Backlink::class);
    }
}
