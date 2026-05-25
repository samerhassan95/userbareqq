<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrder extends Model
{
    use HasFactory;

    protected $table = 'product_orders';

    protected $fillable = [
        'client_id',
        'product_id',
        'product_role',
        'feature_id',
        'feature_name',
        'duration',
        'total_price',
        'status',
        'invoice_id',
        'subscription_id',
        'admin_notes',
        'deliverable_url',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function feature()
    {
        return $this->belongsTo(Addon::class, 'feature_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    /**
     * Get the strategy team members
     */
    public function teamMembers()
    {
        return $this->hasMany(StrategyTeamMember::class);
    }

    /**
     * Get the product order team members (new system)
     */
    public function orderTeamMembers()
    {
        return $this->hasMany(ProductOrderTeamMember::class);
    }

    /**
     * Get the strategy works/posts
     */
    public function strategyWorks()
    {
        return $this->hasMany(StrategyWork::class);
    }

    /**
     * Get all posts for this order
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
