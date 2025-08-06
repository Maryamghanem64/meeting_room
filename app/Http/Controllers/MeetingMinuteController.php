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
        try {
            $minutes = MeetingMinute::with('meeting')->get();
            return response()->json($minutes,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMinuteRequest $request)
    {
        try {
            $minute = MeetingMinute::create($request->validated());
            return response()->json($minute,201);
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
            $minute = MeetingMinute::with('meeting')->findOrFail($id);
            return response()->json($minute,200);
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
            $minute = MeetingMinute::findOrFail($id);
            $minute->update($request->validated());
            return response()->json($minute,200);
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
            $minute = MeetingMinute::findOrFail($id);
            $minute->delete();
            return response()->json(['message' => 'Minute deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
