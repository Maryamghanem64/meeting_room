<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\MeetingMinute;
use Illuminate\Http\Request;

// Test updating a meeting minute
try {
    // Get the first meeting minute
    $minute = MeetingMinute::first();

    if (!$minute) {
        echo "No meeting minutes found. Please create one first.\n";
        exit(1);
    }

    echo "Testing update of meeting minute ID: {$minute->Id}\n";
    echo "Current data:\n";
    print_r($minute->toArray());

    // Test data for update
    $updateData = [
        'notes' => 'Updated meeting notes from test script',
        'decisions' => 'Updated decisions from test script'
    ];

    echo "\nUpdate data:\n";
    print_r($updateData);

    // Create a mock request
    $request = new Request();
    $request->merge($updateData);

    // Validate using the same rules as MinutesController
    $validatedData = $request->validate([
        'notes' => 'sometimes|string',
        'decisions' => 'sometimes|string',
    ]);

    echo "\nValidation passed! Validated data:\n";
    print_r($validatedData);

    // Update the minute
    $minute->update($validatedData);

    echo "\nUpdate successful! New data:\n";
    $freshMinute = $minute->fresh();
    if ($freshMinute) {
        print_r($freshMinute->toArray());
    } else {
        echo "Failed to refresh the minute model.\n";
    }

} catch (\Illuminate\Validation\ValidationException $e) {
    echo "Validation failed:\n";
    print_r($e->errors());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
