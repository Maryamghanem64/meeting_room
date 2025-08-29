<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Seed roles first
        $this->call(RoleSeeder::class);

        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123')
        ]);

        // Assign admin role
        $adminRole = \App\Models\Role::where('name', 'Admin')->first();
        if ($adminRole) {
            $admin->roles()->attach($adminRole->id);
        }

        // Create additional test users
        User::factory(5)->create()->each(function ($user) {
            // Assign random role to test users
            $roles = \App\Models\Role::all();
            if ($roles->count() > 0) {
                $user->roles()->attach($roles->random()->id);
            }
        });
    }
}
