<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
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
        $this->command->info("Creating Roles..............................");
        Role::create(['uuid' =>  Str::uuid(), "name" => "teacher", "guard_name" => "teacher"]);
        Role::create(['uuid' =>  Str::uuid(), "name" => "schoolAdmin", "guard_name" => "schooladmin"]);
        Role::create(['uuid' =>  Str::uuid(), "name" => "student", "guard_name" => "student"]);
        Role::create(['uuid' => Str::uuid(), "name" => "appSuperAdmin", "guard_name" => "appAdmin"]);
        Role::create(['uuid' => Str::uuid(), "name" => "appAdmin", "guard_name" => "appAdmin"]);
        Role::Create(['uuid' =>  Str::uuid(), "name" => "schoolSuperAdmin", "guard_name" => "schooladmin"]);
        $this->command->info("Roles Created Successfully");
    }
}
