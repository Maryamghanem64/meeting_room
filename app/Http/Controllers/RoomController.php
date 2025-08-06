<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoomRequest;
use App\Http\Requests\UpdateRoomRequest;
use App\Models\Room;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//create method
    public function store(StoreRoomRequest $request)
    {
        try {
            $room = Room::create($request->validated());
            return response()->json(['message'=>'room created successfully','room'=>$room],201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //read method
public function index()
    {
        try {
            $rooms = Room::with(['features', 'meetings'])->get();
            return response()->json(['message'=>'rooms retrieved successfully','rooms'=>$rooms],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
//update methode
public function update(UpdateRoomRequest $request,$id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->update($request->validated());
            return response()->json(['message'=>'room updated successfully','room'=>$room],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
//delete method
public function destroy($id)
    {
        try {
            $room = Room::findOrFail($id);
            $room->delete();
            return response()->json(['message'=>'room deleted successfully'],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
//show method
public function show($id){
        try {
            $room = Room::with(['features', 'meetings'])->findOrFail($id);
            return response()->json($room,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
