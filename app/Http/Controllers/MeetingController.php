<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMeetingRequest;
use App\Http\Requests\UpdateMeetingRequest;
use App\Models\Meeting;
use Illuminate\Http\Request;

class MeetingController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $meetings = Meeting::with(['user', 'room', 'attachments', 'meetingAttendees', 'meetingMinutes'])->get();
            return response()->json($meetings, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMeetingRequest $request)
    {
        try {
            $meeting = Meeting::create($request->validated());
            return response()->json($meeting, 201);
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
            $meeting = Meeting::with(['user', 'room', 'attachments', 'meetingAttendees', 'meetingMinutes'])->findOrFail($id);
            return response()->json($meeting, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeetingRequest $request, string $id)
    {
        try {
            $meeting = Meeting::findOrFail($id);
            $meeting->update($request->validated());
            return response()->json($meeting, 200);
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
            $meeting = Meeting::findOrFail($id);
            $meeting->delete();
            return response()->json(['message' => 'Meeting deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
