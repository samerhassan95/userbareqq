<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategyWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'title',
        'title_ar',
        'description',
        'description_ar',
        'scheduled_date',
        'scheduled_time',
        'platforms',
        'status',
        'post_type',
        'attachments',
        'notes',
    ];

    protected $casts = [
        'scheduled_date' => 'date',
        'platforms' => 'array',
        'attachments' => 'array',
    ];

    /**
     * Get the product order
     */
    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class);
    }

    /**
     * Get all posts for this strategy work
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
