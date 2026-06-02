<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Designer;
use App\Models\Marketer;
use App\Models\Meeting;
use App\Models\MeetingTeamMember;
use App\Models\Post;
use App\Models\PostTeamMember;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class AdminMeetingController extends Controller
{
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
    // ---------------------------------------------------------------
    public function index(Request $request)
    {
        $query = Meeting::with(['strategy.product', 'client', 'teamMembers'])
            ->orderByDesc('date')
            ->orderByDesc('start_time');

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

        $member->delete();

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
            if (!$client || !$client->device_token) return;

            $template = \App\Models\NotificationTemplate::where('type', 'meeting_status_updated')->first();
            if (!$template) return;

            $title   = $template->title;
            $message = str_replace(
                ['{meeting_name}', '{status}'],
                [$meeting->meeting_name, ucfirst($meeting->status)],
                $template->message
            );

            app(\App\Services\FirebaseService::class)
                ->sendNotification($client->device_token, $title, $message, [
                    'meeting_id'        => (string) $meeting->id,
                    'notification_type' => 'meeting_status_updated',
                ]);

            app(\App\Repositories\NotificationRepository::class)
                ->createNotification($client, $title, $message, $client->device_token, 'meeting_status_updated');
        } catch (\Exception $e) {
            Log::error('AdminMeetingController: Firebase notification failed: ' . $e->getMessage());
        }
    }
}
