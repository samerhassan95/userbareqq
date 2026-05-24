<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostFeedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'client_id',
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
     * Get the client who gave feedback
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
