<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttendeeRequest;
use App\Models\MeetingAttendee;
use Illuminate\Http\Request;

class MeetingAttendeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $attendees = MeetingAttendee::with(['meeting', 'user'])->get();
            return response()->json($attendees,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendeeRequest $request)
    {
        try {
            $attendee = MeetingAttendee::create($request->validated());
            return response()->json($attendee,201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $attendee = MeetingAttendee::with(['meeting', 'user'])->findOrFail($id);
            return response()->json($attendee,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $attendee = MeetingAttendee::findOrFail($id);
            $attendee->update($request->validated());
            return response()->json($attendee,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $attendee = MeetingAttendee::findOrFail($id);
            $attendee->delete();
            return response()->json(['message' => 'Attendee deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
