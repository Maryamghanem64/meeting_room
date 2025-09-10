<?php

namespace Database\Seeders;

use App\Models\Meeting;
use App\Models\User;
use App\Models\Room;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MeetingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get first user and room for seeding
        $user = User::first();
        $room = Room::first();

        if ($user && $room) {
            Meeting::create([
                'userId' => $user->id,
                'roomId' => $room->id,
                'title' => 'Team Standup Meeting',
                'agenda' => 'Daily standup to discuss progress and blockers',
                'startTime' => now()->addDays(1)->setTime(9, 0),
                'endTime' => now()->addDays(1)->setTime(9, 30),
                'type' => 'standup',
                'status' => 'scheduled'
            ]);

            Meeting::create([
                'userId' => $user->id,
                'roomId' => $room->id,
                'title' => 'Project Planning Session',
                'agenda' => 'Planning the next sprint and assigning tasks',
                'startTime' => now()->addDays(2)->setTime(14, 0),
                'endTime' => now()->addDays(2)->setTime(16, 0),
                'type' => 'planning',
                'status' => 'scheduled'
            ]);

            Meeting::create([
                'userId' => $user->id,
                'roomId' => $room->id,
                'title' => 'Client Review Meeting',
                'agenda' => 'Review project deliverables with client',
                'startTime' => now()->addDays(3)->setTime(10, 0),
                'endTime' => now()->addDays(3)->setTime(11, 30),
                'type' => 'review',
                'status' => 'scheduled'
            ]);
        }
    }
}
