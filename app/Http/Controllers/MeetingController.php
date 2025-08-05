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
      $meeting=Meeting::with(['user', 'room', 'attachments', 'attendees', 'minutes'])->get();
      return response()->json($meeting,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMeetingRequest $request)
    { $meeting=Meeting::create($request->validated());
        return response()->json($meeting,201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $meeting=Meeting::with(['user', 'room', 'attachments', 'attendees', 'minutes'])->get()->find($id);
     return response()->json($meeting,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMeetingRequest $request, string $id)

    {
        $meeting=Meeting::find($id);
        $meeting->update($request->validated());
     return response()->json($meeting,200);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $meeting=Meeting::find($id);
        $meeting->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }
}
