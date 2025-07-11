<?php

namespace Database\Seeders;

use App\Models\Schooladmin;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolAdmins = Schooladmin::where("email", "chongongprecious@gmail.com")->get();
        foreach($schoolAdmins as $schoolAdmin){
            $schoolAdmin->assignRole("schoolSuperAdmin");
        }
    }
}
