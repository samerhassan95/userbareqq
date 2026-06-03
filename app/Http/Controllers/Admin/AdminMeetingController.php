<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\Marketer;
use App\Models\Meeting;
use App\Models\MeetingTeamMember;
use App\Models\Post;
use App\Models\PostTeamMember;
use App\Traits\SendsNotificationsV2;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminMeetingController extends Controller
{
    use SendsNotificationsV2;
    // ---------------------------------------------------------------
    // Allowed status values and their transitions
    // ---------------------------------------------------------------
    //  waiting / request_sent  →  confirmed  →  completed
    //                          ↘  canceled
    // ---------------------------------------------------------------
    private const STATUSES = ['waiting', 'request_sent', 'confirmed', 'completed', 'canceled'];

    // ---------------------------------------------------------------
    // Helper: format a single meeting for the admin response
    // ---------------------------------------------------------------
    private function format(Meeting $meeting): array
    {
        $teamMembers = $meeting->relationLoaded('teamMembers') ? $meeting->teamMembers : collect();

        // Batch-load designers / marketers to avoid N+1
        $designerIds = $teamMembers->where('employee_type', 'designer')->pluck('employee_id');
        $marketerIds  = $teamMembers->where('employee_type', 'marketer')->pluck('employee_id');

        $designers = $designerIds->isNotEmpty()
            ? Designer::whereIn('id', $designerIds)->get()->keyBy('id')
            : collect();

        $marketers = $marketerIds->isNotEmpty()
            ? Marketer::whereIn('id', $marketerIds)->get()->keyBy('id')
            : collect();

        $team = $teamMembers->map(function ($m) use ($designers, $marketers) {
            $person = $m->employee_type === 'designer'
                ? $designers->get($m->employee_id)
                : $marketers->get($m->employee_id);

            if (!$person) return null;

            return [
                'team_member_id' => $m->id,
                'id'             => $person->id,
                'name'           => $person->username ?? $person->name ?? '',
                'image'          => $person->photo ?? null,
                'type'           => $m->employee_type,
            ];
        })->filter()->values();

        return [
            'id'           => $meeting->id,
            'meeting_name' => $meeting->meeting_name,
            'description'  => $meeting->description ?? '',
            'meeting_date' => $meeting->date ? $meeting->date->format('Y-m-d') : null,
            'start_time'   => Carbon::parse($meeting->start_time)->format('H:i'),
            'end_time'     => Carbon::parse($meeting->end_time)->format('H:i'),
            'jitsi_url'    => $meeting->jitsi_url ?? '',
            'status'       => strtolower($meeting->status ?? 'waiting'),
            'notes'        => $meeting->notes ?? null,
            'client'       => $meeting->relationLoaded('client') && $meeting->client ? [
                'id'    => $meeting->client->id,
                'name'  => $meeting->client->name ?? '',
                'email' => $meeting->client->email ?? '',
            ] : null,
            'strategy'     => $meeting->relationLoaded('strategy') && $meeting->strategy ? [
                'id'   => $meeting->strategy->id,
                'name' => $meeting->strategy->product ? $meeting->strategy->product->name : 'Unknown',
            ] : null,
            'team'         => $team,
            'created_at'   => $meeting->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $meeting->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    // ---------------------------------------------------------------
    // GET admin/meetings
    // List all meetings with optional filters
    // Admin: sees all meetings
    // Client: sees only their own meetings
    // ---------------------------------------------------------------
    public function index(Request $request)
    {
        $user = auth()->user();
        
        $query = Meeting::with(['strategy.product', 'client', 'teamMembers'])
            ->orderByDesc('date')
            ->orderByDesc('start_time');

        // If user is a client, filter by their meetings only
        if ($user && $user instanceof \App\Models\Client) {
            $query->where('client_id', $user->id);
        }

        if ($request->filled('status')) {
            $query->where('status', 'like', '%' . $request->status . '%');
        }

        if ($request->filled('strategy_id')) {
            $query->where('strategy_id', $request->strategy_id);
        }

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        $meetings = $query->get();

        return response()->json([
            'status' => true,
            'data'   => $meetings->map(fn($m) => $this->format($m)),
        ]);
    }

    // ---------------------------------------------------------------
    // GET admin/meetings/{id}
    // ---------------------------------------------------------------
    public function show($id)
    {
        $meeting = Meeting::with(['strategy.product', 'client', 'teamMembers'])->find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => $this->format($meeting),
        ]);
    }

    // ---------------------------------------------------------------
    // PUT admin/meetings/{id}/status
    // Update meeting status (confirmed / completed / canceled)
    // ---------------------------------------------------------------
    public function updateStatus(Request $request, $id)
    {
        $meeting = Meeting::with('client')->find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:' . implode(',', self::STATUSES),
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'message' => $validator->errors()->first(),
                'allowed' => self::STATUSES,
            ], 422);
        }

        $oldStatus = $meeting->status;
        $newStatus = $request->status;

        // Prevent re-opening completed/canceled meetings
        if (in_array(strtolower($oldStatus), ['completed', 'canceled']) && $newStatus !== $oldStatus) {
            return response()->json([
                'status'  => false,
                'message' => "Cannot change status of a {$oldStatus} meeting.",
            ], 422);
        }

        $meeting->update(['status' => $newStatus]);

        // Notify the client via Firebase if status changed
        if ($oldStatus !== $newStatus) {
            $this->notifyClientStatusChange($meeting);
        }

        return response()->json([
            'status'  => true,
            'message' => "Meeting status updated to '{$newStatus}' successfully.",
            'data'    => ['id' => $meeting->id, 'status' => $newStatus],
        ]);
    }

    // ---------------------------------------------------------------
    // POST admin/meetings/{id}/team
    // Add team members to a meeting (manually by admin)
    // Body: { "members": [{"type": "designer", "id": 3}, {"type": "marketer", "id": 7}] }
    // ---------------------------------------------------------------
    public function addTeamMembers(Request $request, $id)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'members'               => 'required|array|min:1',
            'members.*.type'        => 'required|in:designer,marketer',
            'members.*.id'          => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 422);
        }

        $added   = [];
        $skipped = [];

        DB::beginTransaction();
        try {
            foreach ($request->members as $member) {
                $type = $member['type'];
                $empId = $member['id'];

                // Verify the designer/marketer exists
                $person = $type === 'designer'
                    ? Designer::find($empId)
                    : Marketer::find($empId);

                if (!$person) {
                    $skipped[] = ['type' => $type, 'id' => $empId, 'reason' => 'Not found'];
                    continue;
                }

                // Avoid duplicates
                $exists = MeetingTeamMember::where('meeting_id', $meeting->id)
                    ->where('employee_type', $type)
                    ->where('employee_id', $empId)
                    ->exists();

                if ($exists) {
                    $skipped[] = ['type' => $type, 'id' => $empId, 'reason' => 'Already in team'];
                    continue;
                }

                MeetingTeamMember::create([
                    'meeting_id'    => $meeting->id,
                    'employee_type' => $type,
                    'employee_id'   => $empId,
                ]);

                $added[] = [
                    'type'  => $type,
                    'id'    => $empId,
                    'name'  => $person->username ?? $person->name ?? '',
                    'image' => $person->photo ?? null,
                ];
            }
            DB::commit();

            // Send notifications to newly added team members
            if (!empty($added)) {
                $this->notifyTeamMembersAdded($meeting, $added);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AdminMeetingController::addTeamMembers error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to add team members.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => count($added) . ' team member(s) added.',
            'added'   => $added,
            'skipped' => $skipped,
        ]);
    }

    // ---------------------------------------------------------------
    // DELETE admin/meetings/{id}/team/{teamMemberId}
    // Remove a specific team member from a meeting
    // ---------------------------------------------------------------
    public function removeTeamMember($id, $teamMemberId)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        $member = MeetingTeamMember::where('meeting_id', $meeting->id)
            ->where('id', $teamMemberId)
            ->first();

        if (!$member) {
            return response()->json(['status' => false, 'message' => 'Team member not found in this meeting'], 404);
        }

        // Get the person before deleting to send notification
        $person = $member->employee_type === 'designer'
            ? Designer::find($member->employee_id)
            : Marketer::find($member->employee_id);

        $member->delete();

        // Notify the removed team member
        if ($person) {
            $this->notifyTeamMemberRemoved($meeting, $person);
        }

        return response()->json([
            'status'  => true,
            'message' => 'Team member removed from meeting.',
        ]);
    }

    // ---------------------------------------------------------------
    // POST admin/meetings/{id}/team/sync-from-strategy
    // Auto-sync team members from the posts assigned to the meeting's
    // strategy order. Idempotent — safe to call multiple times.
    // ---------------------------------------------------------------
    public function syncTeamFromStrategy($id)
    {
        $meeting = Meeting::find($id);

        if (!$meeting) {
            return response()->json(['status' => false, 'message' => 'Meeting not found'], 404);
        }

        if (!$meeting->strategy_id) {
            return response()->json([
                'status'  => false,
                'message' => 'This meeting has no strategy order linked. Cannot auto-sync team.',
            ], 422);
        }

        $synced  = [];
        $skipped = [];

        DB::beginTransaction();
        try {
            // Fetch all distinct team members from posts of this strategy order
            $postTeamMembers = PostTeamMember::whereHas('post', function ($q) use ($meeting) {
                    $q->where('product_order_id', $meeting->strategy_id);
                })
                ->select('member_type', 'member_id')
                ->distinct()
                ->get();

            foreach ($postTeamMembers as $ptm) {
                // Resolve morph type string → 'designer' or 'marketer'
                $type = $this->resolveEmployeeType($ptm->member_type);
                if (!$type) continue;

                $exists = MeetingTeamMember::where('meeting_id', $meeting->id)
                    ->where('employee_type', $type)
                    ->where('employee_id', $ptm->member_id)
                    ->exists();

                if ($exists) {
                    $skipped[] = ['type' => $type, 'id' => $ptm->member_id];
                    continue;
                }

                MeetingTeamMember::create([
                    'meeting_id'    => $meeting->id,
                    'employee_type' => $type,
                    'employee_id'   => $ptm->member_id,
                ]);

                $synced[] = ['type' => $type, 'id' => $ptm->member_id];
            }

            DB::commit();

            // Notify all team members of auto-sync if members were synced
            if (!empty($synced)) {
                $this->notifyTeamSynced($meeting);
            }

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('AdminMeetingController::syncTeamFromStrategy error: ' . $e->getMessage());
            return response()->json(['status' => false, 'message' => 'Failed to sync team.'], 500);
        }

        return response()->json([
            'status'  => true,
            'message' => count($synced) . ' team member(s) synced from strategy posts.',
            'synced'  => $synced,
            'skipped' => $skipped,
        ]);
    }

    // ---------------------------------------------------------------
    // Helper: resolve Laravel morph class string → 'designer'|'marketer'
    // ---------------------------------------------------------------
    private function resolveEmployeeType(string $morphType): ?string
    {
        $map = [
            'App\\Models\\Designer' => 'designer',
            'designer'              => 'designer',
            'App\\Models\\Marketer' => 'marketer',
            'marketer'              => 'marketer',
        ];
        return $map[$morphType] ?? null;
    }

    // ---------------------------------------------------------------
    // Helper: send Firebase push notification to client on status change
    // ---------------------------------------------------------------
    private function notifyClientStatusChange(Meeting $meeting): void
    {
        try {
            $client = $meeting->client;
            if (!$client) return;

            // Get the appropriate template based on status
            $templateType = 'meeting_status_updated';
            if ($meeting->status === 'confirmed') {
                $templateType = 'meeting_confirmed';
            } elseif ($meeting->status === 'completed') {
                $templateType = 'meeting_completed';
            } elseif ($meeting->status === 'canceled') {
                $templateType = 'meeting_canceled';
            }

            // Prepare replacements for template
            $replacements = [
                'meeting_name' => $meeting->meeting_name,
                'status' => ucfirst($meeting->status),
                'date' => $meeting->date ? Carbon::parse($meeting->date)->format('Y-m-d') : 'TBD',
                'time' => $meeting->start_time ? Carbon::parse($meeting->start_time)->format('H:i') : 'TBD',
            ];

            // Send notification using language-aware system
            $this->sendNotificationV2(
                $client,
                $templateType,
                $replacements,
                [
                    'meeting_id'        => (string) $meeting->id,
                    'notification_type' => $templateType,
                    'status'            => $meeting->status,
                ]
            );

        } catch (\Exception $e) {
            Log::error('AdminMeetingController: Firebase notification failed: ' . $e->getMessage());
        }
    }

    /**
     * Notify team members when they are added to a meeting
     */
    private function notifyTeamMembersAdded(Meeting $meeting, array $addedMembers): void
    {
        foreach ($addedMembers as $member) {
            try {
                $type = $member['type'];
                $empId = $member['id'];

                $person = $type === 'designer'
                    ? Designer::find($empId)
                    : Marketer::find($empId);

                if (!$person) continue;

                $replacements = [
                    'meeting_name' => $meeting->meeting_name,
                    'date' => $meeting->date ? Carbon::parse($meeting->date)->format('Y-m-d') : 'TBD',
                ];

                $this->sendNotificationV2(
                    $person,
                    'meeting_team_member_added',
                    $replacements,
                    [
                        'meeting_id'        => (string) $meeting->id,
                        'notification_type' => 'meeting_team_member_added',
                    ]
                );
            } catch (\Exception $e) {
                Log::error('Failed to notify team member of addition to meeting', [
                    'meeting_id' => $meeting->id,
                    'member_id' => $member['id'] ?? null,
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    /**
     * Notify team members when they are removed from a meeting
     */
    private function notifyTeamMemberRemoved(Meeting $meeting, Designer|Marketer $person): void
    {
        try {
            $replacements = [
                'meeting_name' => $meeting->meeting_name,
            ];

            $this->sendNotificationV2(
                $person,
                'meeting_team_member_removed',
                $replacements,
                [
                    'meeting_id'        => (string) $meeting->id,
                    'notification_type' => 'meeting_team_member_removed',
                ]
            );
        } catch (\Exception $e) {
            Log::error('Failed to notify team member of removal from meeting', [
                'meeting_id' => $meeting->id,
                'person_id' => $person->id ?? null,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Notify all team members when auto-sync happens
     */
    private function notifyTeamSynced(Meeting $meeting): void
    {
        try {
            $teamMembers = MeetingTeamMember::where('meeting_id', $meeting->id)->get();

            $designerIds = $teamMembers->where('employee_type', 'designer')->pluck('employee_id');
            $marketerIds = $teamMembers->where('employee_type', 'marketer')->pluck('employee_id');

            $designers = $designerIds->isNotEmpty() ? Designer::whereIn('id', $designerIds)->get() : collect();
            $marketers = $marketerIds->isNotEmpty() ? Marketer::whereIn('id', $marketerIds)->get() : collect();

            $allMembers = $designers->merge($marketers);

            $replacements = [
                'meeting_name' => $meeting->meeting_name,
            ];

            if ($allMembers->isNotEmpty()) {
                $this->sendNotificationV2(
                    $allMembers->toArray(),
                    'meeting_team_synced',
                    $replacements,
                    [
                        'meeting_id'        => (string) $meeting->id,
                        'notification_type' => 'meeting_team_synced',
                    ]
                );
            }
        } catch (\Exception $e) {
            Log::error('Failed to notify team of sync', [
                'meeting_id' => $meeting->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
