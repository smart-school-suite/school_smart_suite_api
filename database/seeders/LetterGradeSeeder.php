<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class LetterGradeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();

        $letterGradesData = [
            'C-',
            'A+',
            'D-',
            'A',
            'D+',
            'D',
            'B-',
            'A-',
            'B',
            'C+',
            'C',
            'B+',
            'F',
        ];

        $letterGrades = array_map(function ($grade) use ($timestamp) {
            return [
                'id'           => Str::uuid()->toString(),
                'letter_grade' => $grade,
                'created_at'   => $timestamp,
                'updated_at'   => $timestamp,
            ];
        }, $letterGradesData);

        DB::table('letter_grades')->upsert(
            $letterGrades,
            ['letter_grade'],
            ['updated_at']
        );
    }
}
