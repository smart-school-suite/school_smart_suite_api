<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Role::create(['name' => 'app_admin', 'guard_name' => 'edumanageadmin']);
        Role::create(['name' => 'school_admin', 'guard_name' => 'schooladmin']);
        Role::create(['name' => 'teacher', 'guard_name' => 'teacher']);
        Role::create(['name' => 'student', 'guard_name' => 'student']);
        Role::create(['name' => 'parent', 'guard_name' => 'parent']);
    }
}
