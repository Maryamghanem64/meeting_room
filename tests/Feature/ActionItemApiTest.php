<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MeetingMinute;
use App\Models\ActionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ActionItemApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $minute;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->minute = MeetingMinute::factory()->create();
        $this->actingAs($this->user, 'sanctum');
    }

    public function test_index_returns_action_items()
    {
        $actionItem = ActionItem::factory()->create([
            'minute_id' => $this->minute->id,
            'assigned_to' => $this->user->id,
            'description' => 'Test action item',
            'status' => 'pending',
            'due_date' => now()->addWeek()->toDateString(),
        ]);

        $response = $this->getJson('/api/actionItems');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'description' => 'Test action item',
                'status' => 'pending',
            ]);
    }

    public function test_store_creates_action_item()
    {
        $payload = [
            'minute_id' => $this->minute->id,
            'assigned_to' => $this->user->id,
            'description' => 'New action item',
            'status' => 'pending',
            'due_date' => now()->addDays(5)->toDateString(),
        ];

        $response = $this->postJson('/api/actionItems', $payload);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'description' => 'New action item',
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('action_items', [
            'description' => 'New action item',
        ]);
    }

    public function test_update_modifies_action_item()
    {
        $actionItem = ActionItem::factory()->create([
            'minute_id' => $this->minute->id,
            'assigned_to' => $this->user->id,
            'description' => 'Old description',
            'status' => 'pending',
        ]);

        $payload = [
            'description' => 'Updated description',
            'status' => 'done',
        ];

        $response = $this->putJson("/api/actionItems/{$actionItem->id}", $payload);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'description' => 'Updated description',
                'status' => 'done',
            ]);

        $this->assertDatabaseHas('action_items', [
            'id' => $actionItem->id,
            'description' => 'Updated description',
            'status' => 'done',
        ]);
    }

    public function test_destroy_deletes_action_item()
    {
        $actionItem = ActionItem::factory()->create([
            'minute_id' => $this->minute->id,
            'assigned_to' => $this->user->id,
            'description' => 'To be deleted',
        ]);

        $response = $this->deleteJson("/api/actionItems/{$actionItem->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Action item deleted successfully']);

        $this->assertDatabaseMissing('action_items', [
            'id' => $actionItem->id,
        ]);
    }
}
