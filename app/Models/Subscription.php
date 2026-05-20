<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'client_id',
        'product_id',
        'status',
        'billing_cycle',
        'starts_at',
        'expires_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the client that owns the subscription.
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the product that the subscription is for.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Check if subscription is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
               ($this->expires_at === null || $this->expires_at->isFuture());
    }
}


    // --- Helper Scopes (Optional but Recommended) ---

    /**
     * Scope a query to only include active subscriptions.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('ends_at', '>', now());
    }

    /**
     * Scope a query to filter by a specific application name.
     */
    public function scopeForApp($query, string $appName)
    {
        return $query->where('app_name', $appName);
    }
}
