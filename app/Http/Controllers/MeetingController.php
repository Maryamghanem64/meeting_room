<?php

namespace App\Http\Controllers;

use App\Models\Meeting;
use App\Models\MeetingAttendee;
use App\Models\Attachment;
use App\Models\MeetingMinute;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\UpdateMeetingRequest;
use App\Http\Requests\StoreMeetingRequest;

class MeetingController extends Controller
{
    /**
     * Display all meetings with optional status filtering
     */
    public function index(Request $request)
    {
        try {
            $query = Meeting::with(['user', 'room', 'meetingAttendees.user']);

            // Filter by status if provided
            if ($request->has('status') && !empty($request->status)) {
                $query->where('status', $request->status);
            }

            $meetings = $query->get();
            return response()->json($meetings, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Join a meeting - update status from pending to ongoing
     */
    public function join($id)
    {
        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::findOrFail($meetingId);

            // Check if meeting is in pending status
            if ($meeting->status !== 'pending') {
                return response()->json([
                    'error' => 'Meeting is not in pending status'
                ], 400);
            }

            // Update status to ongoing
            $meeting->update(['status' => 'ongoing']);

            // Load relationships for response
            $meeting->load(['user', 'room', 'meetingAttendees.user', 'attachments', 'minutes']);

            return response()->json([
                'success' => true,
                'message' => 'Successfully joined the meeting',
                'meeting' => $meeting
            ], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }

    /**
     * Store a new meeting with all related data (agenda, attendees, attachments)
     */
    public function store(StoreMeetingRequest $request)
    {
        // Log the incoming request data for debugging
        Log::info('Store Meeting Request Data:', $request->all());

        $validated = $request->validated();

        DB::beginTransaction();

        try {
            // Check room availability
            $conflict = Meeting::where('roomId', $validated['roomId'])
                ->where('startTime', '<', $validated['endTime'])
                ->where('endTime', '>', $validated['startTime'])
                ->exists();

            if ($conflict) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => [
                        'roomId' => ['Room already booked in this time slot']
                    ]
                ], 422);
            }

            // Create the meeting
            $meeting = Meeting::create([
                'title' => $validated['title'],
                'agenda' => $validated['agenda'],
                'startTime' => $validated['startTime'],
                'endTime' => $validated['endTime'],
                'roomId' => $validated['roomId'],
                'type' => $validated['type'] ?? 'onsite', // Default to onsite if not provided
                'status' => $validated['status'],
                'userId' => Auth::id() ?? 1, // Default to user 1 if not authenticated
            ]);

            // Handle attendees
            if (isset($validated['attendees']) && is_array($validated['attendees']) && !empty($validated['attendees'])) {
                $attendeeData = [];
                foreach ($validated['attendees'] as $userId) {
                    $attendeeData[] = [
                        'meetingId' => $meeting->Id,
                        'userId' => $userId,
                        'isPresent' => false, // Default to not present
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }

                MeetingAttendee::insert($attendeeData);
            }

            // Handle file attachments
            if ($request->hasFile('attachments')) {
                $attachments = $request->file('attachments');

                foreach ($attachments as $file) {
                    if ($file->isValid()) {
                        // Generate unique filename
                        $originalName = $file->getClientOriginalName();
                        $extension = $file->getClientOriginalExtension();
                        $filename = time() . '_' . uniqid() . '.' . $extension;

                        // Store file
                        $path = $file->storeAs('meeting_attachments', $filename, 'public');

                        // Save attachment record
                        Attachment::create([
                            'meetingId' => $meeting->Id,
                            'filePath' => $path,
                            'fileType' => $file->getMimeType(),
                        ]);
                    }
                }
            }

            // Create initial meeting minutes entry (optional)
            $minute = MeetingMinute::create([
                'meeting_id' => $meeting->Id,
                'notes' => '', // Empty initially
                'decisions' => 'pending',
            ]);

            // Handle action items if provided
            if ($request->has('action_items') && is_array($request->input('action_items'))) {
                foreach ($request->input('action_items') as $actionItemData) {
                    $minute->actionItems()->create($actionItemData);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Meeting created successfully',
                'data' => $meeting->load(['meetingAttendees', 'attachments', 'minutes.actionItems'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            // Clean up any uploaded files if transaction failed
            if (isset($attachments) && is_array($attachments)) {
                foreach ($attachments as $file) {
                    if ($file->isValid() && Storage::disk('public')->exists('meeting_attachments/' . $file->hashName())) {
                        Storage::disk('public')->delete('meeting_attachments/' . $file->hashName());
                    }
                }
            }

            Log::error('Meeting creation failed:', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to create meeting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show one meeting with formatted datetime, room data, and all available rooms
     */
    public function show($id)
    {
        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::with(['user', 'room', 'meetingAttendees', 'attachments', 'minutes'])->findOrFail($meetingId);

            // Format datetime fields for frontend (e.g., ISO 8601)
            if ($meeting->startTime && $meeting->startTime instanceof \DateTimeInterface) {
                $meeting->startTime = $meeting->startTime->format('Y-m-d\TH:i');
            }
            if ($meeting->endTime && $meeting->endTime instanceof \DateTimeInterface) {
                $meeting->endTime = $meeting->endTime->format('Y-m-d\TH:i');
            }

            // Get all rooms for dropdown
            $rooms = \App\Models\Room::select('Id as id', 'name')->get();

            return response()->json([
                'booking' => $meeting,
                'rooms' => $rooms
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }

    /**
     * Update meeting
     */
    public function update(UpdateMeetingRequest $request, $id)
    {
        // Log the incoming request data for debugging
        Log::info('Update Meeting Request Data:', $request->all());

        $validated = $request->validated();

        // Filter out null values to avoid updating fields to null
        $validated = array_filter($validated, function ($value) {
            return $value !== null;
        });

        DB::beginTransaction();

        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::findOrFail($meetingId);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Role restriction → Employee can only update his meetings
            if (!$user->hasRole('Admin') && Auth::id() !== $meeting->userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Check room availability if room or time changed
            if (isset($validated['roomId']) || isset($validated['startTime']) || isset($validated['endTime'])) {
                $conflict = Meeting::where('roomId', $validated['roomId'] ?? $meeting->roomId)
                    ->where('Id', '!=', $meeting->Id)
                    ->where('startTime', '<', $validated['endTime'] ?? $meeting->endTime)
                    ->where('endTime', '>', $validated['startTime'] ?? $meeting->startTime)
                    ->exists();

                if ($conflict) {
                    return response()->json([
                        'message' => 'The given data was invalid.',
                        'errors' => [
                            'roomId' => ['Room already booked in this time slot']
                        ]
                    ], 422);
                }
            }

            $meeting->update($validated);
            $meeting->refresh(); // Refresh to get updated datetime as Carbon instances
            $meeting->load(['room']); // Load room relationship for response

            // Format datetime fields for frontend
            if ($meeting->startTime && $meeting->startTime instanceof \DateTimeInterface) {
                $meeting->startTime = $meeting->startTime->format('Y-m-d\TH:i');
            }
            if ($meeting->endTime && $meeting->endTime instanceof \DateTimeInterface) {
                $meeting->endTime = $meeting->endTime->format('Y-m-d\TH:i');
            }

            // Handle minutes update or create if not exists
            $minuteData = $request->input('minutes', []);
            $minute = $meeting->minutes;
            if ($minute) {
                $minute->update($minuteData);
            } else {
                $minute = $meeting->minutes()->create($minuteData);
            }

            // Handle action items for the minute
            if ($request->has('action_items') && is_array($request->input('action_items'))) {
                $existingIds = [];
                foreach ($request->input('action_items') as $actionItemData) {
                    if (isset($actionItemData['id'])) {
                        $actionItem = $minute->actionItems()->find($actionItemData['id']);
                        if ($actionItem) {
                            $actionItem->update($actionItemData);
                            $existingIds[] = $actionItemData['id'];
                        }
                    } else {
                        $newActionItem = $minute->actionItems()->create($actionItemData);
                        $existingIds[] = $newActionItem->id;
                    }
                }
                // Delete removed action items
                $minute->actionItems()->whereNotIn('id', $existingIds)->delete();
            }

            // Handle attachments for the minute
            $existingAttachments = $request->input('existing_attachments', []);
            if (is_array($existingAttachments)) {
                $minute->attachments()->whereNotIn('Id', $existingAttachments)->delete();
            }

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

            DB::commit();

            // Reload meeting with related data
            $meeting->load(['room', 'minutes.actionItems', 'minutes.attachments']);

            // Get all rooms for dropdown
            $rooms = \App\Models\Room::select('Id as id', 'name')->get();

            return response()->json([
                'booking' => $meeting,
                'rooms' => $rooms
            ], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Meeting not found or failed to update'], 404);
        }
    }

    /**
     * Delete meeting
     */
    public function destroy($id)
    {
        try {
            $meetingId = explode(':', $id)[0];
            $meeting = Meeting::findOrFail($meetingId);

            /** @var \App\Models\User $user */
            $user = Auth::user();

            // Role restriction → Employee can only delete his meetings
            if (!$user->hasRole('Admin') && Auth::id() !== $meeting->userId) {
                return response()->json(['error' => 'Unauthorized'], 403);
            }

            // Delete associated files
            foreach ($meeting->attachments ?? [] as $attachment) {
                Storage::disk('public')->delete($attachment->path);
            }

            $meeting->delete();
            return response()->json(['message' => 'Meeting cancelled successfully'], 200);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }
    }
}
