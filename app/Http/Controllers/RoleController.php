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

        $role=Role::create($request->validated());
        return response()->json(['message'=>'Role created successfully','role'=>$role],201);
   }
   //read method
   public function index(){
    $roles=Role::all();
    return response()->json(['message'=>'Roles retrieved successfully','roles'=>$roles],200);
    }
    //update method
    public function update(UpdateRoleRequest $request,$id){
         $role=Role::find($id);

         $role->update($request->validated());
        return response()->json(['message'=>'Role updated successfully','role'=>$role],200);

}
//delete method
public function destroy($id){
    $role=Role::find($id);
    $role->delete();
    return response()->json(['message'=>'Role deleted successfully'],200);
    }
    //show method
    public function show($id){
        $role=Role::find($id);
        return response()->json(['message'=>'Role retrieved successfully','role'=>$role],200);
        }

}
