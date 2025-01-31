<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class roleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create(['uuid' =>  Str::uuid(), "name"=> "teacher", "guard_name" => "api"]);
        Role::create(['uuid' =>  Str::uuid(), "name" => "parent", "guard_name" => "api"]);
        Role::create(['uuid' =>  Str::uuid(), "name"=> "schoolAdmin", "guard_name" => "api"]);
        Role::create(['uuid' =>  Str::uuid(),"name" => "student", "guard_name" => "api"]);
        Role::Create(['uuid' =>  Str::uuid(), "name" => "schoolSuperAdmin", "guard_name" => "api"]);
    }
}
