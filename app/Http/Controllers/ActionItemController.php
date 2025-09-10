<?php

namespace App\Http\Controllers;

use App\Models\ActionItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActionItemController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $actionItems = ActionItem::with(['user', 'minute'])->get();
            return response()->json($actionItems);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve action items'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'minute_id' => 'required|exists:meeting_minutes,id',
                'assigned_to' => 'required|exists:users,id',
                'description' => 'required|string',
                'status' => 'sometimes|in:pending,in_progress,done',
                'due_date' => 'nullable|date',
            ]);

            $actionItem = ActionItem::create($request->all());
            return response()->json($actionItem->load(['user', 'minute']), 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create action item'], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'description' => 'sometimes|required|string',
                'status' => 'sometimes|in:pending,in_progress,done',
                'due_date' => 'nullable|date',
            ]);

            $actionItem = ActionItem::findOrFail($id);
            $actionItem->update($request->only(['description', 'status', 'due_date']));
            return response()->json($actionItem->load(['user', 'minute']));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update action item'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $actionItem = ActionItem::findOrFail($id);
            $actionItem->delete();
            return response()->json(['message' => 'Action item deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete action item'], 500);
        }
    }
}
