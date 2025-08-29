<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    /**
     * Get all users (for admin) with search and filtering
     */
    public function index(Request $request)
    {
        try {
            $query = User::with('roles');

            // Search by name/email
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            }

            // Filter by role name
            if ($request->filled('role')) {
                $role = $request->role;
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            }

            $users = $query->get()->map(function ($user) {
                $firstRole = $user->roles->first();
                return [
                    'id'      => $user->Id,
                    'name'    => $user->name,
                    'email'   => $user->email,
                    'role'    => $firstRole ? $firstRole->name : 'No role assigned',
                    'role_id' => $firstRole ? $firstRole->Id : null
                ];
            });

            return response()->json([
                'success' => true,
                'users'   => $users,
                'total'   => $users->count()
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
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
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|max:255|unique:users',
                'role_id'  => 'required|integer|exists:roles,id'
            ]);

            // Create user with default password
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make('password123') // default password
            ]);

            // Assign role
            $role = Role::findOrFail($request->role_id);
            $user->roles()->attach($role->Id);

            Log::info('User created successfully', ['user_id' => $user->Id, 'email' => $user->email]);

            return response()->json([
                'success' => true,
                'message' => 'User created successfully',
                'user'    => [
                    'id'    => $user->Id,
                    'name'  => $user->name,
                    'email' => $user->email,
                    'role'  => $role->name
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Register (public)
     */
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password)
        ]);

        Log::info('User registered successfully', ['user_id' => $user->Id, 'email' => $user->email]);

        return response()->json([
            'message' => 'User created successfully',
            'user'    => $user
        ], 201);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid email or password'], 401);
        }

        $user  = User::with('roles')->where('email', $request->email)->first();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'    => 'Logged in successfully',
            'user'       => [
                'id'    => $user->Id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->roles->pluck('name')->first()
            ],
            'token'      => $token,
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

    public function profile(Request $request)
    {
        try {
            $user = $request->user()->load(['roles']);
            return response()->json([
                'id'    => $user->Id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->roles->pluck('name')->first()
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update user profile or another user's profile by admin
     */
    public function update(Request $request, $id)
    {
        try {
            if ($id === null || $id == $request->user()->Id) {
                $user = $request->user();
            } else {
                if (!$request->user()->hasRole('Admin')) {
                    return response()->json([
                        'message' => 'Unauthorized. Only administrators can update other users.'
                    ], 403);
                }
                $user = User::findOrFail($id);
            }

            $request->validate([
                'name'  => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:users,email,' . $user->Id,
                'role_id' => 'sometimes|integer|exists:roles,id'
            ]);

            // Update all fields from request
            $user->update($request->all());

            if ($request->filled('role_id')) {
                $user->roles()->sync([$request->role_id]);
            }

            // Always load the roles relationship to ensure it's included in response
            $user->load('roles');

            // Format user data consistently with index method
            $firstRole = $user->roles->first();
            $formattedUser = [
                'id'      => $user->Id,
                'name'    => $user->name,
                'email'   => $user->email,
                'role'    => $firstRole ? $firstRole->name : 'No role assigned',
                'role_id' => $firstRole ? $firstRole->Id : null
            ];

            return response()->json([
                'success' => true,
                'message' => 'User updated successfully',
                'user'    => $formattedUser
            ], 200);
        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->validator->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function show($id)
    {
        try {
            $user = User::with(['roles', 'meetings', 'meetingAttendees'])->findOrFail($id);
            return response()->json($user, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    public function destroy($id)
    {
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
                'error'   => $e->getMessage()
            ], 404);
        }
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $request->validate([
                'is_active' => 'required|boolean'
            ]);

            $user = User::findOrFail($id);
            $user->update([
                'is_active' => $request->is_active
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User status updated successfully',
                'user'    => $user
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors'  => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage()
            ], 404);
        }
    }
}
