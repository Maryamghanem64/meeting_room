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
        try {
            $feature = Feature::create($request->validated());
            return response()->json(['message'=>'Feature created successfully','feature'=>$feature],201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
   }
   //read method
   public function index(){
        try {
            $features = Feature::with('rooms')->get();
            return response()->json(['message'=>'Features retrieved successfully','features'=>$features],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //update method
    public function update(UpdateFeatureRequest $request,$id){
        try {
            $feature = Feature::findOrFail($id);
            $feature->update($request->validated());
            return response()->json(['message'=>'Feature updated successfully','feature'=>$feature],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
//delete method
public function destroy($id){
    try {
        $feature = Feature::findOrFail($id);
        $feature->delete();
        return response()->json(['message'=>'Feature deleted successfully'],200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 404);
    }
}
    //show method
    public function show($id){
        try {
            $feature = Feature::with('rooms')->findOrFail($id);
            return response()->json(['message'=>'Feature retrieved successfully','feature'=>$feature],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
