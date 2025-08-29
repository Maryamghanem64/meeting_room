<?php
// Simple test script to verify API endpoints
$baseUrl = 'http://127.0.0.1:8000/api';

// Login to get the authentication token
$loginData = [
    'email' => 'admin@example.com', // Replace with a valid email
    'password' => 'password123' // Replace with the correct password
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
    echo "Login request failed: " . error_get_last()['message'] . "\n";
    echo "Response headers:\n";
    print_r($http_response_header);
} else {
    $loginData = json_decode($loginResponse, true);
    if (isset($loginData['token'])) {
        $token = $loginData['token'];
        echo "Login successful, token obtained.\n";

        // Test roles endpoint
        echo "Testing roles endpoint...\n";
        $rolesResponse = file_get_contents($baseUrl . '/roles', false, stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer $token\r\n"
            ]
        ]));
        $rolesData = json_decode($rolesResponse, true);

        if ($rolesData && $rolesData['success']) {
            echo "✓ Roles endpoint working successfully\n";
            echo "Found " . count($rolesData['roles']) . " roles:\n";
            foreach ($rolesData['roles'] as $role) {
                echo "  - " . $role['name'] . "\n";
            }
        } else {
            echo "✗ Roles endpoint failed\n";
            print_r($rolesData);
        }

        echo "\n";

        // Test users endpoint
        echo "Testing users endpoint...\n";
        $usersResponse = file_get_contents($baseUrl . '/users', false, stream_context_create([
            'http' => [
                'header' => "Authorization: Bearer $token\r\n"
            ]
        ]));
        $usersData = json_decode($usersResponse, true);

        if ($usersData) {
            if (isset($usersData['success']) && $usersData['success']) {
                echo "✓ Users endpoint working successfully\n";
                echo "Found " . count($usersData['users']) . " users\n";
                // Check if users have id and role_id
                if (!empty($usersData['users'])) {
                    $user = $usersData['users'][0];
                    echo "Sample user data:\n";
                    echo "  ID: " . (isset($user['id']) ? $user['id'] : 'MISSING') . "\n";
                    echo "  Role ID: " . (isset($user['role_id']) ? $user['role_id'] : 'MISSING') . "\n";
                }
            } else {
                echo "Users endpoint response (may require authentication):\n";
                print_r($usersData);
            }
        } else {
            echo "✗ Users endpoint failed or requires authentication\n";
        }
    } else {
        echo "✗ Login failed\n";
        print_r($loginData);
        echo "Response headers:\n";
        print_r($http_response_header);
    }
}
?>
