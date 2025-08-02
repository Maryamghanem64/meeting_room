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
        $attendee=MeetingAttendee::all();
        return response()->json($attendee,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttendeeRequest $request)
    { $attendee=MeetingAttendee::create($request->validated());
        return response()->json($attendee,201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
       $attendee=MeetingAttendee::find($id);
       return response()->json($attendee,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
         $attendee=MeetingAttendee::find($id);
         $attendee->update($request->validated());
         return response()->json($attendee,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
      $attendee=MeetingAttendee::find($id);
      $attendee->delete();

    }
}
