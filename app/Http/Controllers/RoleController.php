<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
//create method
   public function store(StoreRoleRequest $request){
        try {
            $role = Role::create($request->validated());
            return response()->json(['message'=>'Role created successfully','role'=>$role],201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
   }
   //read method
   public function index(){
        try {
            $roles = Role::with('users')->get();
            return response()->json(['message'=>'Roles retrieved successfully','roles'=>$roles],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    //update method
    public function update(UpdateRoleRequest $request,$id){
        try {
            $role = Role::findOrFail($id);
            $role->update($request->validated());
            return response()->json(['message'=>'Role updated successfully','role'=>$role],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
//delete method
public function destroy($id){
    try {
        $role = Role::findOrFail($id);
        $role->delete();
        return response()->json(['message'=>'Role deleted successfully'],200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 404);
    }
}
    //show method
    public function show($id){
        try {
            $role = Role::with('users')->findOrFail($id);
            return response()->json(['message'=>'Role retrieved successfully','role'=>$role],200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

}
