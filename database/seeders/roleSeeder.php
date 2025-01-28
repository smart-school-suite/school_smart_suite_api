<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Seeder;

class roleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Role::create(["name"=> "teacher"]);
        Role::create(["name" => "parent"]);
        Role::create(["name"=> "schoolAdmin"]);
        Role::create(["name" => "student"]);
        Role::Create(["name" => "schoolSuperAdmin"]);
    }
}
