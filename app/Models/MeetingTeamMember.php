<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MeetingTeamMember extends Model
{
    protected $table = 'meeting_team_members';

    protected $fillable = [
        'meeting_id',
        'employee_type',  // 'designer' or 'marketer'
        'employee_id',
    ];

    /**
     * Get the parent meeting.
     */
    public function meeting()
    {
        return $this->belongsTo(Meeting::class);
    }

    /**
     * Resolve the actual designer or marketer record.
     */
    public function getEmployeeAttribute()
    {
        if ($this->employee_type === 'designer') {
            return Designer::find($this->employee_id);
        }
        if ($this->employee_type === 'marketer') {
            return Marketer::find($this->employee_id);
        }
        return null;
    }
}
