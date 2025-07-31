<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function store(StoreUserRequest $request){

        $user = User::create($request->validated());
        return response()->json($user, 201);
    }

    public function index(){
        $users = User::all();
        return response()->json($users, 200);
    }

    public function update(UpdateUserRequest $request, $id){
        $user = User::findOrFail($id);

        $user->update($request->validated());
        return response()->json($user, 200);
    }

    public function show($id){
        $user = User::findOrFail($id);
        return response()->json($user, 200);
    }

    public function destroy($id){
        $user = User::findOrFail($id);
        $user->delete();
        return response()->json(['message' => 'User deleted'], 200);
    }}
