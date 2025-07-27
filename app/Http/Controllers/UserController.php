<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function store(Request $request){
        $user=User::create([
            'name'=>$request->name
        ,'email'=>$request->email
        ,'pwd'=>$request->pwd
        ,'role'=>$request->role
    ]);
    return response()->json($user,201);
    }
    public function index(){
        $user=User::all();
        return response()->json($user,200);
    }
    public function update(Request $request,$id){
        $user=User::findOrFail($id);
        $user->update($request->all());
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
