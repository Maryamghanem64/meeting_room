<?php
// Test script to verify minutes API endpoint
$baseUrl = 'http://127.0.0.1:8000/api';

// Login to get the authentication token
$loginData = [
    'email' => 'admin@example.com',
    'password' => 'password123'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n",
        'method' => 'POST',
        'content' => json_encode($loginData),
    ],
];

$context = stream_context_create($options);
$loginResponse = @file_get_contents($baseUrl . '/login', false, $context);

if ($loginResponse === FALSE) {
    echo "Login request failed\n";
    exit(1);
}

$loginData = json_decode($loginResponse, true);
if (!isset($loginData['token'])) {
    echo "Login failed - no token received\n";
    print_r($loginData);
    exit(1);
}

$token = $loginData['token'];
echo "Login successful, token obtained.\n";

// First, get a list of meetings to get a valid meeting_id
echo "Getting list of meetings...\n";
$meetingsResponse = file_get_contents($baseUrl . '/meetings', false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $token\r\n"
    ]
]));

$meetingsData = json_decode($meetingsResponse, true);
if (!$meetingsData || empty($meetingsData)) {
    echo "No meetings found. Please seed the database first.\n";
    exit(1);
}

$meeting = $meetingsData[0]; // Get first meeting
$meetingId = $meeting['Id']; // Use 'Id' as per the schema
echo "Using meeting ID: $meetingId\n";

// Now test creating minutes
echo "Testing minutes creation...\n";
$minutesData = [
    'meeting_id' => $meetingId,
    'notes' => 'Test meeting notes',
    'decisions' => 'Test meeting decisions'
];

$options = [
    'http' => [
        'header' => "Content-Type: application/json\r\n" .
                   "Authorization: Bearer $token\r\n",
        'method' => 'POST',
        'content' => json_encode($minutesData),
    ],
];

$context = stream_context_create($options);
$minutesResponse = @file_get_contents($baseUrl . '/minutes', false, $context);

if ($minutesResponse === FALSE) {
    echo "Minutes creation request failed\n";
    echo "HTTP Response Code: " . $http_response_header[0] . "\n";
} else {
    echo "Minutes creation response:\n";
    echo $minutesResponse . "\n";

    $responseData = json_decode($minutesResponse, true);
    if ($responseData) {
        if (isset($responseData['error'])) {
            echo "Error: " . $responseData['error'] . "\n";
            if (isset($responseData['details'])) {
                echo "Details: " . json_encode($responseData['details']) . "\n";
            }
        } else {
            echo "Success! Minutes created with ID: " . ($responseData['Id'] ?? $responseData['id'] ?? 'unknown') . "\n";
        }
    }
}
?>
