<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Layout extends Model
{
    protected $fillable = ['name', 'slug'];

    public function sections()
    {
        return $this->hasMany(Section::class);
    }
}
