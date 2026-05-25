<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOrderTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_order_id',
        'member_id',
        'member_type',
    ];

    /**
     * Get the product order
     */
    public function productOrder()
    {
        return $this->belongsTo(ProductOrder::class);
    }

    /**
     * Get the team member (Designer or Marketer)
     */
    public function member()
    {
        if ($this->member_type === 'designer') {
            return Designer::find($this->member_id);
        } elseif ($this->member_type === 'marketer') {
            return Marketer::find($this->member_id);
        }
        return null;
    }
}
