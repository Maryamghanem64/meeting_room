<?php

// Test script for Action Items API endpoints

$baseUrl = 'http://127.0.0.1:8000/api';

// Test login to get token
$loginData = [
    'email' => 'admin@example.com',
    'password' => 'password123'
];

$ch = curl_init($baseUrl . '/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($loginData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json'
]);

$loginResponse = curl_exec($ch);
$loginData = json_decode($loginResponse, true);

if (isset($loginData['token'])) {
    $token = $loginData['token'];
    echo "Login successful, token: " . substr($token, 0, 20) . "...\n";

    // Test GET /api/actionItems
    $ch = curl_init($baseUrl . '/actionItems');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    echo "GET /api/actionItems response:\n";
    print_r($data);

    // Test POST /api/actionItems
    $actionItemData = [
        'minute_id' => 1, // Assuming minute exists
        'assigned_to' => 1, // Assuming user exists
        'description' => 'Test action item',
        'status' => 'pending',
        'due_date' => date('Y-m-d', strtotime('+1 week'))
    ];

    $ch = curl_init($baseUrl . '/actionItems');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($actionItemData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json',
        'Accept: application/json'
    ]);

    $response = curl_exec($ch);
    $data = json_decode($response, true);
    echo "POST /api/actionItems response:\n";
    print_r($data);

    if (isset($data['id'])) {
        $id = $data['id'];

        // Test PUT /api/actionItems/{id}
        $updateData = [
            'description' => 'Updated test action item',
            'status' => 'done'
        ];

        $ch = curl_init($baseUrl . '/actionItems/' . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        echo "PUT /api/actionItems/{$id} response:\n";
        print_r($data);

        // Test DELETE /api/actionItems/{id}
        $ch = curl_init($baseUrl . '/actionItems/' . $id);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Accept: application/json'
        ]);

        $response = curl_exec($ch);
        $data = json_decode($response, true);
        echo "DELETE /api/actionItems/{$id} response:\n";
        print_r($data);
    }

} else {
    echo "Login failed: " . $loginResponse . "\n";
}

curl_close($ch);
