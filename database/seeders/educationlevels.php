<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Educationlevels;
use Illuminate\Database\Seeder;

class educationlevel extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Educationlevels::create(['name' => 'Level one', 'level' => '200']);
        Educationlevels::create(['name' => 'Level one', 'level' => '300']);
        Educationlevels::create(['name' => 'Degree', 'level' => '400']);
        Educationlevels::create(['name' => 'Masters One', 'level' => '500']);
        Educationlevels::create(['name' => 'Masters two', 'level' => '600']);
        Educationlevels::create(['name' => 'PHD', 'level' => '700']);
    }
}
