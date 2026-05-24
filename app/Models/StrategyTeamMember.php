<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StrategyTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'member_id',
        'member_type',
        'role',
    ];

    /**
     * Get the product order
     */
    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class);
    }

    /**
     * Get the team member (polymorphic)
     */
    public function member()
    {
        return $this->morphTo();
    }
}
