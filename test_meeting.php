<?php

// Test script to check meeting creation validation
require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\Room;
use App\Models\Meeting;

// Simulate the validation rules from MeetingController
$testData = [
    'title' => 'Test Meeting',
    'agenda' => 'Test agenda',
    'startTime' => '2025-09-07 15:00:00',
    'endTime' => '2025-09-07 16:00:00',
    'roomId' => 1, // Valid room ID
    'type' => 'onsite',
    'status' => 'pending',
    'attendees' => [1, 2], // Valid user IDs
];

echo "Testing meeting creation with data:\n";
print_r($testData);
echo "\n";

// Validate the request
$validator = Validator::make($testData, [
    'title' => 'required|string|max:255',
    'agenda' => 'nullable|string',
    'startTime' => 'required|date',
    'endTime' => 'required|date|after:startTime',
    'roomId' => 'nullable|integer|exists:rooms,Id',
    'type' => 'nullable|in:onsite,online',
    'status' => 'required|string|in:pending,ongoing,completed,cancelled,scheduled',
    'attendees' => 'nullable|array',
    'attendees.*' => 'integer|exists:users,id',
    'attachments' => 'nullable|array',
    'attachments.*' => 'file|mimes:pdf,doc,docx,txt,jpg,jpeg,png|max:10240'
]);

if ($validator->fails()) {
    echo "Validation failed with errors:\n";
    print_r($validator->errors()->toArray());
} else {
    echo "Validation passed!\n";

    // Test room availability check
    $conflict = Meeting::where('roomId', $testData['roomId'])
        ->where('startTime', '<', $testData['endTime'])
        ->where('endTime', '>', $testData['startTime'])
        ->exists();

    if ($conflict) {
        echo "Room conflict detected!\n";
    } else {
        echo "No room conflicts.\n";
    }
}
