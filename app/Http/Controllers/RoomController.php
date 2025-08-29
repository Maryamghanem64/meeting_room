<?php

namespace App\Http\Controllers;

use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the rooms with their features.
     */
    public function index()
    {
        // Get all rooms with their related features
        $rooms = Room::with('features')->get();

        return response()->json([
            'success' => true,
            'rooms' => $rooms
        ], 200);
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request)
    {
        // Validate input data
        $request->validate([
            'name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'features' => 'array' // optional features (IDs)
        ]);

        // Create a new room
        $room = Room::create($request->only(['name', 'location', 'capacity']));

        // If features are provided, sync them with pivot table
        if ($request->has('features')) {
            $room->features()->sync($request->features);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room created successfully',
            'room' => $room->load('features')
        ], 201);
    }

    /**
     * Display the specified room.
     */
    public function show($id)
    {
        $room = Room::with('features')->find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'room' => $room
        ], 200);
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, $id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        // Validate input
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'location' => 'sometimes|string|max:255',
            'capacity' => 'sometimes|integer|min:1',
            'features' => 'array'
        ]);

        // Update room data
        $room->update($request->only(['name', 'location', 'capacity']));

        // Update features if provided
        if ($request->has('features')) {
            $room->features()->sync($request->features);
        }

        return response()->json([
            'success' => true,
            'message' => 'Room updated successfully',
            'room' => $room->load('features')
        ], 200);
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy($id)
    {
        $room = Room::find($id);

        if (!$room) {
            return response()->json([
                'success' => false,
                'message' => 'Room not found'
            ], 404);
        }

        // Detach all features before deleting (cleanup pivot table)
        $room->features()->detach();
        $room->delete();

        return response()->json([
            'success' => true,
            'message' => 'Room deleted successfully'
        ], 200);
    }
}
