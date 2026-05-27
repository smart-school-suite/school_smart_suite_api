<?php

namespace Database\Seeders;

use App\Models\PeriodDuration\PeriodDuration;
use App\Models\PeriodDuration\PeriodDurationType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodDurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Seed Duration Types
        $durationTypes = [
            [
                "name" => "Exam Timetable Durations",
                "description" => "Standard duration slots for examination periods",
                "key" => "exam_timetable_duration"
            ],
            [
                "name" => "Semester Timetable Duration",
                "description" => "Regular teaching and learning session durations",
                "key" => "semester_timetable_duration"
            ],
            [
                "name" => "Resit Timetable Duration",
                "description" => "Duration slots for resit examination periods",
                "key" => "resit_timetable_duration"
            ]
        ];

        foreach($durationTypes as $type){
            PeriodDurationType::updateOrCreate(
                ['key' => $type['key']],
                $type
            );
        }

        // Get type IDs by their keys
        $examTypeId = PeriodDurationType::where('key', 'exam_timetable_duration')->first()->id;
        $semesterTypeId = PeriodDurationType::where('key', 'semester_timetable_duration')->first()->id;
        $resitTypeId = PeriodDurationType::where('key', 'resit_timetable_duration')->first()->id;

        // Semester durations (teaching periods)
        $semesterDurations = [
            [
                'name' => 'Short Period',
                'minutes' => 30,
                'description' => 'A 30-minute slot typically used for consultations or quick practical check-ins.',
                'type_id' => $semesterTypeId,
                'key' => 'short_period'
            ],
            [
                'name' => 'Standard Half Period',
                'minutes' => 45,
                'description' => 'A 45-minute duration suitable for secondary level lessons or short workshops.',
                'type_id' => $semesterTypeId,
                'key' => 'half_period'
            ],
            [
                'name' => 'Standard Period',
                'minutes' => 60,
                'description' => 'The standard 60-minute lecture unit for most academic semester courses.',
                'type_id' => $semesterTypeId,
                'key' => 'standard_period'
            ],
            [
                'name' => 'Extended Period',
                'minutes' => 90,
                'description' => 'A 90-minute session for combined lecture and tutorial.',
                'type_id' => $semesterTypeId,
                'key' => 'extended_period'
            ],
            [
                'name' => 'Double Period',
                'minutes' => 120,
                'description' => 'A 120-minute (2 hour) extended block reserved for intensive labs, studios, or exams.',
                'type_id' => $semesterTypeId,
                'key' => 'double_period'
            ],
        ];

        // Exam durations (realistic exam scenarios)
        $examDurations = [
            [
                'name' => 'Short Exam',
                'minutes' => 60,
                'description' => '1-hour examination suitable for multiple-choice tests or short-answer assessments.',
                'type_id' => $examTypeId,
                'key' => 'exam_1hour'
            ],
            [
                'name' => 'Standard Exam',
                'minutes' => 120,
                'description' => '2-hour standard examination for most undergraduate courses.',
                'type_id' => $examTypeId,
                'key' => 'exam_2hour'
            ],
            [
                'name' => 'Extended Exam',
                'minutes' => 180,
                'description' => '3-hour examination for comprehensive final exams or professional certifications.',
                'type_id' => $examTypeId,
                'key' => 'exam_3hour'
            ],
            [
                'name' => 'Half-Day Exam',
                'minutes' => 240,
                'description' => '4-hour examination for practical assessments or project-based evaluations.',
                'type_id' => $examTypeId,
                'key' => 'exam_4hour'
            ],
            [
                'name' => 'Full-Day Exam',
                'minutes' => 360,
                'description' => '6-hour examination for portfolio reviews or comprehensive practical assessments.',
                'type_id' => $examTypeId,
                'key' => 'exam_6hour'
            ],
            [
                'name' => 'Clinical Exam',
                'minutes' => 45,
                'description' => '45-minute clinical/practical examination for medical or healthcare programs.',
                'type_id' => $examTypeId,
                'key' => 'exam_clinical'
            ],
            [
                'name' => 'Studio Exam',
                'minutes' => 300,
                'description' => '5-hour studio-based examination for architecture or design programs.',
                'type_id' => $examTypeId,
                'key' => 'exam_studio'
            ],
        ];

        // Resit durations (similar to exams but with some variations)
        $resitDurations = [
            [
                'name' => 'Standard Resit',
                'minutes' => 120,
                'description' => '2-hour resit examination for standard course retakes.',
                'type_id' => $resitTypeId,
                'key' => 'resit_2hour'
            ],
            [
                'name' => 'Extended Resit',
                'minutes' => 180,
                'description' => '3-hour resit examination for comprehensive assessment retakes.',
                'type_id' => $resitTypeId,
                'key' => 'resit_3hour'
            ],
            [
                'name' => 'Short Resit',
                'minutes' => 60,
                'description' => '1-hour resit for objective or short-form assessments.',
                'type_id' => $resitTypeId,
                'key' => 'resit_1hour'
            ],
            [
                'name' => 'Practical Resit',
                'minutes' => 150,
                'description' => '2.5-hour practical resit for laboratory or clinical assessments.',
                'type_id' => $resitTypeId,
                'key' => 'resit_practical'
            ],
            [
                'name' => 'Portfolio Resit',
                'minutes' => 240,
                'description' => '4-hour portfolio review or project resit examination.',
                'type_id' => $resitTypeId,
                'key' => 'resit_portfolio'
            ],
        ];

        // Combine all durations
        $allDurations = array_merge($semesterDurations, $examDurations, $resitDurations);

        // Use upsert with composite unique constraint on (key, type_id)
        DB::transaction(function () use ($allDurations) {
            foreach ($allDurations as $duration) {
                PeriodDuration::updateOrCreate(
                    [
                        'key' => $duration['key'],
                        'type_id' => $duration['type_id']
                    ],
                    $duration
                );
            }
        });
    }
}
