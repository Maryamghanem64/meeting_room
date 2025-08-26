<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Get all users (for admin)
     */
    public function index()
    {
        try {
            $users = User::with('roles')->get()->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->roles->pluck('name')->first() // Get the first role name
                ];
            });

            return response()->json([
                'success' => true,
                'users' => $users
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created user (for admin)
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:users',
                'role' => 'required|string|in:Admin,Employee,Guest'
            ]);

            // Create user with default password
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make('password123') // Default password
            ]);

            // Find the role and attach it to the user
            $role = Role::where('name', $request->role)->first();
            if ($role) {
                $user->roles()->attach($role->id);
            }

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $request->role
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

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
        'user' => [
            'id' => $user->Id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first() // Return only the first role name
        ],
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
 * Get the authenticated user's profile
 */
public function profile(Request $request)
{
    try {
        $user = $request->user()->load(['roles']);
        return response()->json([
            'id' => $user->Id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->pluck('name')->first() // Return only the role name
        ], 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
}

/**
 * Update the user profile or another user's profile by admin
 */
    public function update(UpdateUserRequest $request, $id = null){
        try {
            // Determine which user to update
            if ($id === null || $id == $request->user()->id) {
                // Update own profile
                $user = $request->user();
            } else {
                // Admin updating another user - check if user has admin role
                if (!$request->user()->hasRole('Admin')) {
                    return response()->json([
                        'message' => 'Unauthorized. Only administrators can update other users.'
                    ], 403);
                }

                $user = User::findOrFail($id);
            }

            // Check if the email is being updated
            $emailChanged = $request->has('email') && $request->email !== $user->email;

            // Update the user with validated data
            $user->update($request->validated());

            // Apply unique validation only if the email has changed
            if ($emailChanged) {
                $request->validate([
                    'email' => 'unique:users,email,' . $user->id,
                ]);
            }

            // Load relationships for response
            $user->load('roles');

            return response()->json([
                'message' => 'User updated successfully',
                'user' => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
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
            return response()->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
