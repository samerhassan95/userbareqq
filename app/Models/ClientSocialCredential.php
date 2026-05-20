<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientSocialCredential extends Model
{
    use HasFactory;

    protected $table = 'client_social_credentials';

    protected $fillable = [
        'client_id',
        'platform',
        'username',
        'password',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Automatically encrypt password when setting
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = encrypt($value);
    }

    // Automatically decrypt password when getting
    public function getPasswordAttribute($value)
    {
        try {
            return decrypt($value);
        } catch (\Exception $e) {
            return $value; // Return as-is if decryption fails
        }
    }

    // Relationship with Client
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
