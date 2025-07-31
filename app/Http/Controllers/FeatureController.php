<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreFeatureRequest;
use App\Http\Requests\UpdateFeatureRequest;
use App\Models\Feature;
use Illuminate\Http\Request;

class FeatureController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//create method
   public function store(StoreFeatureRequest $request){

        $role=Feature::create($request->validated());
        return response()->json(['message'=>'Feature created successfully','role'=>$role],201);
   }
   //read method
   public function index(){
    $roles=Feature::all();
    return response()->json(['message'=>'Features retrieved successfully','roles'=>$roles],200);
    }
    //update method
    public function update(UpdateFeatureRequest $request,$id){
         $role=Feature::find($id);

         $role->update($request->validated());
        return response()->json(['message'=>'Feature updated successfully','role'=>$role],200);

}
//delete method
public function destroy($id){
    $role=Feature::find($id);
    $role->delete();
    return response()->json(['message'=>'Feature deleted successfully'],200);
    }
    //show method
    public function show($id){
        $role=Feature::find($id);
        return response()->json(['message'=>'Feature retrieved successfully','role'=>$role],200);
        }
}
