<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    public const TYPE_SUBSCRIPTION = 'subscription';
    public const TYPE_ONE_TIME = 'one_time';

    protected $fillable = [
        'name',
        'name_ar',
        'description',
        'description_ar',
        'price',
        'note',
        'note_ar',
        'image',
        'category_id',
        'background_image',
        'type',
        'product_role',
        'monthly_price',
        'yearly_price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'monthly_price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
    ];


    public function media()
    {
        return $this->hasMany(ProductMedia::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'product_addons');
    }

    public function attachments()
    {
        return $this->morphMany(Attachment::class, 'attachable');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function sliders()
    {
        // Adjust the class name if your model is named differently (e.g., ProductSlider)
        return $this->hasMany(Slider::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function strategyTips()
    {
        return $this->hasMany(ProductStrategyTip::class)->orderBy('sort_order');
    }

    public function orders()
    {
        return $this->hasMany(ProductOrder::class);
    }

    // Alias for addons to use as features
    public function features()
    {
        return $this->addons();
    }
}
