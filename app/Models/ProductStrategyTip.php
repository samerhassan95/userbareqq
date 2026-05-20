<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductStrategyTip extends Model
{
    use HasFactory;

    protected $table = 'product_strategy_tips';

    protected $fillable = [
        'product_id',
        'text',
        'platforms',
        'sort_order',
    ];

    protected $casts = [
        'platforms' => 'array',
        'sort_order' => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
