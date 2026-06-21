<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Media extends Model
{
    protected $fillable = ['domain_id', 'filename', 'path', 'webp_path', 'mime_type', 'size', 'user_id'];
}
