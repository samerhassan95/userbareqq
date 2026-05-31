<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostFeedback extends Model
{
    use HasFactory;

    protected $table = 'post_feedbacks';

    protected $fillable = [
        'post_id',
        'user_id',
        'user_type',
        'comment',
    ];

    /**
     * Get the post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who gave feedback (polymorphic - Admin, Client, Marketer, Designer)
     */
    public function user()
    {
        return $this->morphTo();
    }

    /**
     * Backward compatibility: Get the client who gave feedback
     */
    public function client()
    {
        if ($this->user_type === 'App\Models\Client') {
            return $this->belongsTo(Client::class, 'user_id');
        }
        return null;
    }
}
