<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    protected $fillable = ['user_id', 'action', 'description', 'domain_id', 'subject_type', 'subject_id'];

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public static function log(string $action, string $description = '', ?Model $subject = null): self
    {
        return static::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'domain_id' => config('app.domain_id'),
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
        ]);
    }
}
