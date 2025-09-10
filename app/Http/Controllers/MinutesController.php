<?php

namespace App\Http\Controllers;

use App\Models\MeetingMinute;
use App\Models\Meeting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MinutesController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $minutes = MeetingMinute::with(['meeting', 'actionItems', 'attachments'])->get();
            return response()->json($minutes);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve minutes: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve minutes'], 500);
        }
    }

    public function store(Request $request): JsonResponse
    {
        Log::info('Store Minute Request Data:', $request->all());

        // Handle action_items if sent as JSON string
        if ($request->has('action_items') && is_string($request->input('action_items'))) {
            $request->merge(['action_items' => json_decode($request->input('action_items'), true)]);
        }

        try {
            $validatedData = $request->validate([
                'meeting_id' => 'required|exists:meetings,Id',
                'notes' => 'nullable|string',
                'decisions' => 'nullable|string',
                'action_items' => 'nullable|array',
                'action_items.*.task' => 'nullable|string',
                'action_items.*.assigned_to' => 'nullable|exists:users,Id',
                'action_items.*.status' => 'nullable|string',
                'action_items.*.due_date' => 'nullable|date',
            ]);

            $minute = MeetingMinute::create($validatedData);

            // Handle action items
            if (isset($validatedData['action_items'])) {
                foreach ($validatedData['action_items'] as $actionItemData) {
                    // Map 'task' to 'description' if present
                    if (isset($actionItemData['task'])) {
                        $actionItemData['description'] = $actionItemData['task'];
                        unset($actionItemData['task']);
                    }
                    Log::info('Creating action item:', $actionItemData);
                    $minute->actionItems()->create($actionItemData);
                }
            }

            // Handle attachments
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');
                foreach ($attachments as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $path = $file->storeAs('meeting_attachments', $filename, 'public');

                        $minute->attachments()->create([
                            'filePath' => $path,
                            'fileType' => $file->getMimeType(),
                        ]);
                    }
                }
            }

            return response()->json($minute->load(['actionItems', 'attachments']), 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed for minute creation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'errors' => $e->errors()
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during minute creation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'sql_error' => $e->getMessage()
            ]);
            return response()->json([
                'error' => 'Database error occurred',
                'details' => 'Please check if the meeting exists and try again'
            ], 400);
        } catch (\Exception $e) {
            Log::error('Unexpected error during minute creation: ' . $e->getMessage(), [
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Failed to create minute',
                'details' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }

    public function show($id): JsonResponse
    {
        try {
            $minute = MeetingMinute::with(['meeting', 'actionItems', 'attachments'])->findOrFail($id);
            return response()->json($minute);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Minute not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to retrieve minute: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to retrieve minute'], 500);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        // Handle action_items if sent as JSON string
        if ($request->has('action_items') && is_string($request->input('action_items'))) {
            $request->merge(['action_items' => json_decode($request->input('action_items'), true)]);
        }

        try {
            $validatedData = $request->validate([
                'notes' => 'sometimes|string',
                'decisions' => 'sometimes|string',
                'action_items' => 'nullable|array',
                'action_items.*.id' => 'nullable|exists:action_items,id',
                'action_items.*.description' => 'nullable|string',
                'action_items.*.assigned_to' => 'nullable|integer',
                'action_items.*.status' => 'nullable|string',
                'action_items.*.due_date' => 'nullable|date',
                'existing_attachments' => 'nullable|array',
                'existing_attachments.*' => 'exists:attachments,Id',
            ]);

            $minute = MeetingMinute::findOrFail($id);
            $minute->update($validatedData);

            // Handle action items
            if (isset($validatedData['action_items'])) {
                $existingIds = [];
                foreach ($validatedData['action_items'] as $actionItemData) {
                    // Map 'task' to 'description' if present
                    if (isset($actionItemData['task'])) {
                        $actionItemData['description'] = $actionItemData['task'];
                        unset($actionItemData['task']);
                    }
                    if (isset($actionItemData['id'])) {
                        // Update existing
                        $actionItem = $minute->actionItems()->find($actionItemData['id']);
                        if ($actionItem) {
                            $actionItem->update($actionItemData);
                            $existingIds[] = $actionItemData['id'];
                        }
                    } else {
                        // Create new
                        $newActionItem = $minute->actionItems()->create($actionItemData);
                        $existingIds[] = $newActionItem->id;
                    }
                }
                // Delete removed action items
                $minute->actionItems()->whereNotIn('id', $existingIds)->delete();
            }

            // Handle existing attachments (delete removed ones)
            if (isset($validatedData['existing_attachments'])) {
                $minute->attachments()->whereNotIn('Id', $validatedData['existing_attachments'])->delete();
            }

            // Handle new attachments
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');
                foreach ($attachments as $file) {
                    if ($file->isValid()) {
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;
                        $path = $file->storeAs('meeting_attachments', $filename, 'public');

                        $minute->attachments()->create([
                            'filePath' => $path,
                            'fileType' => $file->getMimeType(),
                        ]);
                    }
                }
            }

            return response()->json($minute->load(['actionItems', 'attachments']));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Minute not found'], 404);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Failed to update minute: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to update minute'], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $minute = MeetingMinute::findOrFail($id);
            $minute->delete();
            return response()->json(['message' => 'Minute deleted successfully']);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['error' => 'Minute not found'], 404);
        } catch (\Exception $e) {
            Log::error('Failed to delete minute: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to delete minute'], 500);
        }
    }
}
