<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request){
        $validateData=$request->validate([
            'name'=>'required|string'
        ,'email'=>'required'
        ,'pwd'=>'required|integer'
        ,'role'=>'required'
        ]);
        $user=User::create($validateData);
    return response()->json($user,201);
    }
    public function index(){
        $user=User::all();
        return response()->json($user,200);
    }
    public function update(Request $request,$id){
        $user=User::findOrFail($id);
        $validateData=$request->validate([
            'name'=>'somtimes|string'
        ,'email'=>'somtimes'
        ,'pwd'=>'somtimes|integer'
        ,'role'=>'somtimes'
        ]);
        $user->update($validateData);
        return response()->json($user,200);
    }
    public function show($id){
        $user=User::findOrFail($id);
        return response()->json($user,200);

    }
    public function destroy($id){
        $user=User::findOrFail($id);
        $user->delete();
        return response()->json(['message'=>'user deleted'],200);
}
}
