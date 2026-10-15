<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SemesterSeeder extends Seeder
{
    public function run(): void
    {
        $timestamp = now();

        $semesters = [
            ['name' => 'first semester',  'program_name' => 'FIS', 'count' => 1],
            ['name' => 'second semester', 'program_name' => 'SES', 'count' => 2],
            ['name' => 'third semester',  'program_name' => 'THS', 'count' => 3],
            ['name' => 'fourth semester', 'program_name' => 'FOS', 'count' => 4],
            ['name' => 'fifth semester',  'program_name' => 'FIS', 'count' => 5],
        ];

        foreach ($semesters as $semester) {
            $exists = DB::table('semesters')->where('name', $semester['name'])->exists();

            if ($exists) {
                DB::table('semesters')->where('name', $semester['name'])->update([
                    'program_name' => $semester['program_name'],
                    'count'        => $semester['count'],
                    'updated_at'   => $timestamp,
                ]);
                continue;
            }

            DB::table('semesters')->insert([
                'id'           => Str::uuid()->toString(),
                'name'         => $semester['name'],
                'program_name' => $semester['program_name'],
                'count'        => $semester['count'],
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
            ]);
        }
    }
}
