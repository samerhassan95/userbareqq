<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title',
        'title_ar',
        'description',
        'description_ar',
        'image',
        'status',
        'is_approved',
        'approved_at',
        'client_id',
        'created_by_id',
        'created_by_type',
        'updated_by_id',
        'updated_by_type',
    ];

    protected $casts = [
        'is_approved' => 'boolean',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the client who will approve the post
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the creator (Admin or Marketer)
     */
    public function createdBy()
    {
        return $this->morphTo('created_by');
    }

    /**
     * Get the last editor
     */
    public function updatedBy()
    {
        return $this->morphTo('updated_by');
    }

    /**
     * Get all feedbacks for this post
     */
    public function feedbacks()
    {
        return $this->hasMany(PostFeedback::class)->orderBy('created_at', 'desc');
    }

    /**
     * Check if post can be edited
     */
    public function canBeEdited()
    {
        return !$this->is_approved;
    }

    /**
     * Check if feedbacks can be added
     */
    public function canReceiveFeedback()
    {
        return !$this->is_approved;
    }
}
