<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class GuestPostOrder extends Model
{
    use HasTenant;

    protected $fillable = ['name', 'email', 'article_title', 'target_url', 'anchor_text', 'status', 'domain_id'];
}
