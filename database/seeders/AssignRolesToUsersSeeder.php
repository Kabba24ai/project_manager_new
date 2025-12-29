<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AssignRolesToUsersSeeder extends Seeder
{
    public function run(): void
    {
        // Get all users
        $users = User::all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found in database.');
            return;
        }

        // Assign roles to users
        foreach ($users as $index => $user) {
            // First user gets admin role
            if ($index === 0) {
                $user->assignRole('admin');
                $this->command->info("Assigned 'admin' role to: {$user->name} ({$user->email})");
            }
            // Next 2 users get manager role
            elseif ($index <= 2) {
                $user->assignRole('manager');
                $this->command->info("Assigned 'manager' role to: {$user->name} ({$user->email})");
            }
            // Remaining users get developer role
            else {
                $user->assignRole('developer');
                $this->command->info("Assigned 'developer' role to: {$user->name} ({$user->email})");
            }
        }

        $this->command->info('Roles assigned successfully!');
    }
}

