<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMinuteRequest;
use App\Models\MeetingMinute;
use Illuminate\Http\Request;

class MeetingMinuteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $minute=MeetingMinute::with('meeting')->get();
        return response()->json($minute,200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMinuteRequest $request)
    { $minute=MeetingMinute::create($request->validated());
        return response()->json($minute,201);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $minute=MeetingMinute::with('meeting')->get()->find($id);
        return response()->json($minute,200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
      $minute=MeetingMinute::find($id);
      $minute->update($request->validated());
      return response()->json($minute,200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $minute=MeetingMinute::find($id);
        $minute->delete();
        return response()->json(null,204);
    }
}
