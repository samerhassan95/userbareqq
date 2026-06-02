<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Meeting;
use App\Models\ProductOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ClientMeetingController extends Controller
{
    /**
     * Helper to format a meeting item according to API contract
     */
    private function formatMeeting($meeting)
    {
        return [
            'id' => $meeting->id,
            'slot_id' => $meeting->slot_id ?? 0,
            'client_id' => $meeting->client_id,
            'description' => $meeting->description ?? '',
            'meeting_date' => $meeting->date ? $meeting->date->format('Y-m-d') : null,
            'meeting_name' => $meeting->meeting_name,
            'start_time' => Carbon::parse($meeting->start_time)->format('H:i'),
            'end_time' => Carbon::parse($meeting->end_time)->format('H:i'),
            'jitsi_url' => $meeting->jitsi_url ?? '',
            'status' => strtolower($meeting->status ?? 'waiting'),
            'strategy' => $meeting->strategy ? [
                'id' => $meeting->strategy->id,
                'name' => $meeting->strategy->product ? $meeting->strategy->product->name : 'Unknown Product'
            ] : null,
            'team' => $meeting->employees ? $meeting->employees->map(function ($emp) {
                return [
                    'id' => $emp->id,
                    'name' => $emp->name,
                    'image' => $emp->image ? asset('uploads/employees/' . $emp->image) : null,
                ];
            })->toArray() : [],
            'created_at' => $meeting->created_at ? $meeting->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $meeting->updated_at ? $meeting->updated_at->format('Y-m-d H:i:s') : null,
        ];
    }

    /**
     * 1) List meetings (GET client/meetings)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        $meetings = Meeting::with(['strategy.product', 'employees'])
            ->where('client_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('start_time')
            ->get();

        return response()->json([
            'data' => $meetings->map(fn($m) => $this->formatMeeting($m))
        ]);
    }

    /**
     * 1) Filter meetings (GET client/meetings/filter?status={status})
     */
    public function filter(Request $request)
    {
        $user = auth()->user();
        $status = $request->query('status');

        $query = Meeting::with(['strategy.product', 'employees'])
            ->where('client_id', $user->id)
            ->orderByDesc('date')
            ->orderByDesc('start_time');

        if ($status) {
            // Map common statuses if needed, though exact string matching is usually fine
            $query->where('status', 'like', "%$status%");
        }

        $meetings = $query->get();

        return response()->json([
            'data' => $meetings->map(fn($m) => $this->formatMeeting($m))
        ]);
    }

    /**
     * 2) Join meeting (GET client/meetings/{meetingId}/join)
     */
    public function join(Request $request, $meetingId)
    {
        $user = auth()->user();
        $meeting = Meeting::with('strategy.product')->where('client_id', $user->id)->find($meetingId);

        if (!$meeting) {
            return response()->json([
                'status' => false,
                'message' => 'Meeting not found',
                'data' => null
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Joined successfully',
            'data' => [
                'meeting_id' => $meeting->id,
                'meeting_name' => $meeting->meeting_name,
                'jitsi_url' => $meeting->jitsi_url ?? '',
                'strategy_name' => $meeting->strategy && $meeting->strategy->product ? $meeting->strategy->product->name : 'Unknown Product',
                'start_time' => Carbon::parse($meeting->start_time)->format('H:i'),
                'end_time' => Carbon::parse($meeting->end_time)->format('H:i'),
                'status' => strtolower($meeting->status)
            ]
        ]);
    }

    /**
     * 3) Create meeting (POST client/meetings)
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        $validator = Validator::make($request->all(), [
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time',
            'meeting_name' => 'required|string',
            'description' => 'required|string',
            'strategy_id' => 'nullable|exists:product_orders,id',
            'date' => 'required|date|after_or_equal:today' // The app might send slot_id, but since we use static slots we need date. Let's assume the client passes date or we derive it.
        ]);

        // Note: The API doc says the app expects slot_id. But since we bypass AvailableSlot, 
        // the client might not send date directly if it relies on slot_id. 
        // We will assume 'date' is provided or we can extract it if needed.
        
        // Wait, the API doc for POST client/meetings says:
        // { "slot_id": 7, "client_id": 33, "start_time": "10:00", "project_id": "99", "meeting_name": "Project Kickoff", "description": "Kickoff call", "end_time": "10:30" }
        // We don't have 'date' in the POST body. How do we get the date? 
        // In the static slots implementation, we can encode the date in the slot_id (e.g., timestamp) or just require 'date'. 
        // Since we are replacing the old system, we will use 'date' if available, otherwise we use the slot_id as a unix timestamp.
        $dateStr = $request->input('date');
        $slotId = $request->input('slot_id');
        if (!$dateStr && $slotId) {
            // Let's assume slot_id is the unix timestamp for the start of the slot
            try {
                $dateStr = Carbon::createFromTimestamp($slotId)->format('Y-m-d');
            } catch (\Exception $e) {
                $dateStr = Carbon::today()->format('Y-m-d');
            }
        } elseif (!$dateStr) {
            $dateStr = Carbon::today()->format('Y-m-d');
        }

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        // Check if there is already a meeting at this time
        $overlapping = Meeting::where('date', $dateStr)
            ->where(function ($q) use ($request) {
                $q->where(function ($query) use ($request) {
                    $query->where('start_time', '<', $request->end_time)
                          ->where('end_time', '>', $request->start_time);
                });
            })->exists();

        if ($overlapping) {
            return response()->json([
                'status' => false,
                'message' => 'This time slot is already booked. Please choose another time.'
            ], 409);
        }

        $meeting = Meeting::create([
            'client_id' => $user->id,
            'strategy_id' => $request->strategy_id,
            'slot_id' => $slotId,
            'meeting_name' => $request->meeting_name,
            'description' => $request->description,
            'date' => $dateStr,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
            'jitsi_url' => config('services.jitsi.base_url', 'https://meet.jit.si') . '/meeting-' . uniqid(),
            'status' => 'waiting' // As per app requirement "waiting"
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Meeting created successfully',
            'data' => $this->formatMeeting($meeting)
        ]);
    }

    /**
     * 4) Delete meeting (DELETE client/meetings/{meetingId})
     */
    public function destroy($meetingId)
    {
        $user = auth()->user();
        $meeting = Meeting::where('client_id', $user->id)->find($meetingId);

        if (!$meeting) {
            return response()->json([
                'status' => false,
                'message' => 'Meeting not found'
            ], 404);
        }

        $meeting->delete();

        return response()->json([
            'status' => true,
            'message' => 'Meeting deleted successfully'
        ]);
    }

    /**
     * Helper to generate slots for a day
     */
    private function generateSlotsForDate($dateStr)
    {
        $date = Carbon::parse($dateStr);
        $slots = [];
        $startTime = Carbon::parse($dateStr . ' 10:00:00');
        $endTime = Carbon::parse($dateStr . ' 18:00:00'); // Slots up to 17:00 -> 18:00
        
        while ($startTime < $endTime) {
            $slotEnd = $startTime->copy()->addHour();
            $slots[] = [
                // We use unix timestamp of the start time as a static slot_id
                'slot_id' => $startTime->timestamp, 
                'date' => $dateStr,
                'start_time' => $startTime->format('H:i'),
                'end_time' => $slotEnd->format('H:i'),
            ];
            $startTime->addHour();
        }
        return $slots;
    }

    /**
     * 5) Available slots (GET client/available-slots?date={yyyy-mm-dd})
     */
    public function availableSlots(Request $request)
    {
        $dateStr = $request->query('date');
        if (!$dateStr) {
            $dateStr = Carbon::today()->format('Y-m-d');
        }

        $slots = $this->generateSlotsForDate($dateStr);
        $bookedMeetings = Meeting::where('date', $dateStr)
            ->whereNotIn('status', ['canceled', 'cancelled'])
            ->get();

        $availableSlots = [];
        foreach ($slots as $slot) {
            $isBooked = false;
            $sStart = Carbon::parse($dateStr . ' ' . $slot['start_time']);
            $sEnd = Carbon::parse($dateStr . ' ' . $slot['end_time']);

            foreach ($bookedMeetings as $meeting) {
                $mStart = Carbon::parse($dateStr . ' ' . $meeting->start_time);
                $mEnd = Carbon::parse($dateStr . ' ' . $meeting->end_time);

                if ($sStart < $mEnd && $sEnd > $mStart) {
                    $isBooked = true;
                    break;
                }
            }

            if (!$isBooked) {
                $slot['status'] = true;
                $availableSlots[] = $slot;
            }
        }

        return response()->json([
            'status' => true,
            'data' => $availableSlots
        ]);
    }

    /**
     * 6) Unbooked slots (GET client/unbooked-slots)
     */
    public function unbookedSlots(Request $request)
    {
        // By default, let's return unbooked slots for the next 7 days
        $unbooked = [];
        for ($i = 0; $i < 7; $i++) {
            $dateStr = Carbon::today()->addDays($i)->format('Y-m-d');
            
            $slots = $this->generateSlotsForDate($dateStr);
            $bookedMeetings = Meeting::where('date', $dateStr)
                ->whereNotIn('status', ['canceled', 'cancelled'])
                ->get();

            foreach ($slots as $slot) {
                $isBooked = false;
                $sStart = Carbon::parse($dateStr . ' ' . $slot['start_time']);
                $sEnd = Carbon::parse($dateStr . ' ' . $slot['end_time']);

                foreach ($bookedMeetings as $meeting) {
                    $mStart = Carbon::parse($dateStr . ' ' . $meeting->start_time);
                    $mEnd = Carbon::parse($dateStr . ' ' . $meeting->end_time);

                    if ($sStart < $mEnd && $sEnd > $mStart) {
                        $isBooked = true;
                        break;
                    }
                }

                if (!$isBooked) {
                    $unbooked[] = $slot;
                }
            }
        }

        return response()->json([
            'status' => true,
            'message' => 'Unbooked slots retrieved',
            'data' => $unbooked
        ]);
    }
}
