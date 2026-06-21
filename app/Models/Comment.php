<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasTenant;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'name',
        'email',
        'content',
        'status',
        'ip_address',
        'user_agent',
        'approved_at',
        'domain_id',
    ];

    protected $casts = [
        'approved_at' => 'datetime',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function approve(): void
    {
        $this->update(['status' => 'approved', 'approved_at' => now()]);
    }

    public function reject(): void
    {
        $this->update(['status' => 'rejected']);
    }
}
