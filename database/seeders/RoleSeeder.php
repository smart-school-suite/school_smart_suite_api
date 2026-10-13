<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'teacher', 'guard_name' => 'teacher'],
            ['name' => 'schoolAdmin', 'guard_name' => 'schooladmin'],
            ['name' => 'student', 'guard_name' => 'student'],
            ['name' => 'appSuperAdmin', 'guard_name' => 'appAdmin'],
            ['name' => 'appAdmin', 'guard_name' => 'appAdmin'],
            ['name' => 'schoolSuperAdmin', 'guard_name' => 'schooladmin'],
        ];

        foreach ($roles as $role) {
            Role::firstOrCreate(
                [
                    'name' => $role['name'],
                    'guard_name' => $role['guard_name'],
                ],
                [
                    'uuid' => Str::uuid()->toString(),
                ]
            );
        }
    }
}
