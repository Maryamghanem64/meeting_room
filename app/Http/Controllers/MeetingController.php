<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\UpdateMeetingRequest;
use App\Http\Requests\StoreMeetingRequest;

class MeetingController extends Controller
{
    /**
     * Display all meetings
     */
    public function index()
    {
        try {
            $meetings = Meeting::with(['user', 'room'])->get();
            return response()->json($meetings, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new meeting
     */
    public function store(StoreMeetingRequest $request)
    {
        $validated = $request->validated();

        try {
            // Check room availability
            $conflict = Meeting::where('roomId', $validated['roomId'])
                ->where('startTime', '<', $validated['endTime'])
                ->where('endTime', '>', $validated['startTime'])
                ->exists();

            if ($conflict) {
                return response()->json(['error' => 'Room already booked in this time slot'], 422);
            }

            // Create meeting
            $meeting = Meeting::create(array_merge($validated, [
                'status' => $validated['status'] ?? 'Scheduled',
            ]));

            return response()->json($meeting, 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Show one meeting
     */
    public function show($id)
    {
        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::with(['user', 'room'])->findOrFail($meetingId);
            return response()->json($meeting, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }

    /**
     * Update meeting
     */
    public function update(UpdateMeetingRequest $request, $id)
    {
        // Log the incoming request data for debugging
        Log::info('Update Meeting Request Data:', $request->all());

        $validated = $request->validated();

        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::findOrFail($meetingId);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Role restriction → Employee can only update his meetings
            if (!$user->hasRole('Admin') && Auth::id() !== $meeting->userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check room availability if room or time changed
            if (isset($validated['roomId']) || isset($validated['startTime']) || isset($validated['endTime'])) {
                $conflict = Meeting::where('roomId', $validated['roomId'] ?? $meeting->roomId)
                    ->where('Id', '!=', $meeting->Id)
                    ->where('startTime', '<', $validated['endTime'] ?? $meeting->endTime)
                    ->where('endTime', '>', $validated['startTime'] ?? $meeting->startTime)
                    ->exists();

                if ($conflict) {
                    return response()->json(['error' => 'Room already booked in this time slot'], 422);
                }
            }

            $meeting->update($validated);
            return response()->json($meeting, 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found or failed to update'], 404);
        }
    }

    /**
     * Delete meeting
     */
    public function destroy($id)
    {
        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::findOrFail($meetingId);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Role restriction → Employee can only delete his meetings
            if (!$user->hasRole('Admin') && Auth::id() !== $meeting->userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            $meeting->delete();
            return response()->json(['message' => 'Meeting cancelled successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }
}
