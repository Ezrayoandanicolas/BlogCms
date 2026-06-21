<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasTenant;

    protected $fillable = ['key', 'value', 'domain_id'];
}
