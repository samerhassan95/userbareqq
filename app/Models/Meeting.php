<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meeting extends Model
{
    protected $fillable = [
        'slot_id', 'start_time', 'end_time', 'jitsi_url',
        'meeting_name', 'strategy_id', 'status', 'description',
        'client_id', 'date', 'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function slot()
    {
        return $this->belongsTo(AvailableSlot::class, 'slot_id');
    }

    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }

    public function strategy()
    {
        return $this->belongsTo(ProductOrder::class, 'strategy_id');
    }

    public function logs()
    {
        return $this->hasMany(MeetingLog::class);
    }

    /**
     * Team members assigned to this meeting (designers or marketers).
     * Uses the polymorphic `meeting_team_members` table.
     */
    public function teamMembers()
    {
        return $this->hasMany(MeetingTeamMember::class, 'meeting_id');
    }
}

