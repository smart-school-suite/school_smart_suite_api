<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DropSchoolExpensesRecords extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('school_expenses')->orderBy('id')->limit(5000)->delete();
        $this->command->info("School Expenses Deleted Succesfully");
    }
}
