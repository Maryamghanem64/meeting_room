<?php

namespace App\Http\Controllers;

use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    /**
     * Get all roles
     */
    public function index()
    {
        try {
            $roles = Role::all();

            return response()->json([
                'success' => true,
                'roles' => $roles
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created role
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name'
            ]);

            $role = Role::create([
                'name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role created successfully',
                'role' => $role
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

    /**
     * Update the specified role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            $request->validate([
                'name' => 'required|string|max:255|unique:roles,name,' . $role->id
            ]);

            $role->update([
                'name' => $request->name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Role updated successfully',
                'role' => $role
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Remove the specified role
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role is being used by any users
            if ($role->users()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete role. It is assigned to one or more users.'
                ], 422);
            }

            $role->delete();

            return response()->json([
                'success' => true,
                'message' => 'Role deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 404);
        }
    }
}
