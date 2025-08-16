<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{

     public function register(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
             'password' => 'required|string|min:8|confirmed',
        ]
        );
        $user =User::create(
            [
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password)

            ]
            );
            return response()->json(['message' => 'User created successfully',
        'user' => $user], 201);
    }
   public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string|min:6',
    ]);

    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid email or password'], 401);
    }


    $user = User::with('roles')->where('email', $request->email)->first();


    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'message' => 'Logged in successfully',
        'user' => ['id'=>$user->Id,
    'name'=>$user->name,
'email'=>$user->email],
   'role'=>$user->roles->pluck('name'),
        'token' => $token,
        'token_type' => 'Bearer'
    ], 200);
}

public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return response()->json([
        'message' => 'Logged out successfully'
    ], 200);
}
    /**
     * Display a listing of the resource.
     */
     public function store(StoreUserRequest $request){

        $user = User::create($request->validated());
        return response()->json($user, 201);
    }

    public function index(){
        try {
            $users = User::with(['roles', 'meetings', 'meetingAttendees'])->get();
            return response()->json($users,200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(UpdateUserRequest $request, $id){
        try {
            $user = User::findOrFail($id);
            $user->update($request->validated());
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function show($id){
        try {
            $user = User::with(['roles', 'meetings', 'meetingAttendees'])->findOrFail($id);
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id){
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
