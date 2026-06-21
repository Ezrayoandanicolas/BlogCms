<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory, HasTenant;

    protected $fillable = ['title', 'slug', 'content', 'seo_title', 'seo_description', 'seo_keywords', 'domain_id'];
}
