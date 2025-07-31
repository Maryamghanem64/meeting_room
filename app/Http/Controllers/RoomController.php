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
    $room=Room::create($request->validated());
    return response()->json(['message'=>'room created successfully','room'=>$room],201);
        }
    //read method
public function index()
        {
            $rooms=Room::all();
            return response()->json(['message'=>'rooms retrieved successfully','rooms'=>$rooms],200);
        }
//update methode
public function update(UpdateRoomRequest $request,$id)
{ $room=Room::find($id);

    $room->update($request->validated());
    return response()->json(['message'=>'room updated successfully','room'=>$room],200);

}
//delete method
public function destroy($id)
{
    $room=Room::find($id);
    $room->delete();
    return response()->json(['message'=>'room deleted successfully'],200);}
//show method
public function show($id){
        $room=Room::findOrFail($id);
        return response()->json($room,200);

    }
}
