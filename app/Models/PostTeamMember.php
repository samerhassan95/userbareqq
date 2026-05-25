<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostTeamMember extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'member_id',
        'member_type',
        'role',
    ];

    /**
     * Get the post
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the team member (Designer or Marketer)
     */
    public function member()
    {
        return $this->morphTo();
    }
}
