<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['id' => 1, 'name' => 'admin', 'guard_name' => 'web', 'unique_id' => 'ROLE001'],
            ['id' => 2, 'name' => 'manager', 'guard_name' => 'web', 'unique_id' => 'ROLE002'],
            ['id' => 3, 'name' => 'developer', 'guard_name' => 'web', 'unique_id' => 'ROLE003'],
            ['id' => 4, 'name' => 'designer', 'guard_name' => 'web', 'unique_id' => 'ROLE004'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->insertOrIgnore([
                'id' => $role['id'],
                'name' => $role['name'],
                'guard_name' => $role['guard_name'],
                'unique_id' => $role['unique_id'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Roles seeded successfully!');
    }
}

