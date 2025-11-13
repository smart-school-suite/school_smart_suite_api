<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\SettingCategory;
use App\Models\SettingDefination;
use App\Models\LetterGrade;

class SettingDefinationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->studentPromotionSettingDefination();
        $this->timetableSettingDefination();
        $this->resitSettingDefination();
        $this->examSettingDefination();
        $this->gradeSettingDefination();
        $this->electionTieBreakerSettingDefination();
    }

    public function studentPromotionSettingDefination()
    {
        $promotionSettingDefs = [
            [
                'key' => 'promotion.min_gpa',
                'label' => 'Minimum GPA',
                'data_type' => 'decimal',
                'default_value' => 2.00,
                'description' => 'The lowest Grade Point Average (GPA) a student must achieve to be eligible for promotion to the next academic level.',
            ],
            [
                'key' => 'promotion.max_tuition_fee_debt',
                'label' => 'Max Tuition Fee Debt',
                'data_type' => 'decimal',
                'default_value' => 300000.00,
                'description' => 'The maximum amount of tuition fee debt (in local currency) a student can have and still be considered for promotion.',
            ],
            [
                'key' => 'promotion.max_additional_fee_debt',
                'label' => 'Max Additional Fee Debt',
                'data_type' => 'decimal',
                'default_value' => 50000.00,
                'description' => 'The maximum amount of debt from non-tuition fees (e.g., library fees, lab fees) a student can have and still be considered for promotion.',
            ],
            [
                'key' => 'promotion.max_carry_overs',
                'label' => 'Max Carry Overs',
                'data_type' => 'integer',
                'default_value' => 5,
                'description' => 'The maximum number of courses/modules a student is permitted to have carried over (failed and needing to be retaken) from the previous level for promotion eligibility.',
            ],
            //when the auto promotion to occur,
            //what event should happen before auto promotion
        ];

        $settingCategoryName = 'Student Promotion Setting';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();

        foreach ($promotionSettingDefs as $promotionSettingDef) {
            SettingDefination::create([
                'key' => $promotionSettingDef['key'],
                'data_type' => $promotionSettingDef['data_type'],
                'default_value' => $promotionSettingDef['default_value'],
                'name' => $promotionSettingDef['label'],
                'description' => $promotionSettingDef['description'] ?? null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
    public function timetableSettingDefination()
    {
        $settingDefs = [
            [
                'key' => 'timetable.ignore_teacher_preference',
                'label' => 'Ignore Teacher Preferences',
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => 'When set to TRUE, the system will **create the timetable without considering** or enforcing teachers’ preferred or restricted teaching times. This provides maximum flexibility for scheduling.',
            ],
            [
                'key' => 'timetable.respect_teacher_preference',
                'label' => 'Respect Teacher Preferences',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When set to TRUE, the system will **enforce teachers’ preferred teaching times** when generating the timetable. This setting considers their availability and restrictions.',
            ]
        ];

        $settingCategoryName = 'Time-table Settings';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();

        foreach ($settingDefs as $settingDef) {
            SettingDefination::create([
                'key' => $settingDef['key'],
                'data_type' => $settingDef['data_type'],
                'default_value' => $settingDef['default_value'],
                'name' => $settingDef['label'],
                'description' => $settingDef['description'] ?? null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
    public function resitSettingDefination()
    {
        $settingDefs = [
            [
                'key' => 'resitFee.generalBilling',
                'label' => 'Use General Resit Fee (Per Course)',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When TRUE, all students will be charged the single "General Resit Fee" amount per resit course, regardless of their academic level.',
            ],
            [
                'key' => 'resitFee.levelBilling',
                'label' => 'Use Level-Specific Resit Fee',
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => 'When TRUE, the system will use a different resit fee for each academic level (e.g., Level 100 fee, Level 200 fee, etc.), which must be configured in the "Level-Specific Fees" setting.',
            ],
            [
                'key' => 'resitFee.generalBillingFee',
                'label' => 'General Resit Fee Amount',
                'data_type' => 'decimal',
                'default_value' => 3000.00,
                'description' => 'The flat fee amount charged for a single resit course when "Use General Resit Fee" is enabled.',
            ],
            [
                'key' => 'resitFee.levelBillingFee',
                'label' => 'Level-Specific Fees',
                'data_type' => 'json',
                'default_value' => '[]',
                'description' => 'A JSON array/object containing the resit fee amounts, keyed by the academic level (e.g., {"100": 2500.00, "200": 3500.00}). This is used when "Use Level-Specific Resit Fee" is enabled.',
            ],
            [
                'key' => 'resit.period',
                'label' => 'Resit Examination Period',
                'data_type' => 'string',
                'default_value' => 'August',
                'description' => 'The designated month or time of year when resit examinations are generally scheduled (e.g., "August", "Summer Break").'
            ]
        ];

        $settingCategoryName = 'Resit Settings';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();

        foreach ($settingDefs as $settingDef) {
            SettingDefination::create([
                'key' => $settingDef['key'],
                'data_type' => $settingDef['data_type'],
                'default_value' => $settingDef['default_value'],
                'name' => $settingDef['label'],
                'description' => $settingDef['description'] ?? null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
    public function examSettingDefination()
    {
        $settingDefs = [
            [
                'key' => 'exam.final_exam',
                'label' => 'Final Exam',
                'data_type' => 'json',
                'default_value' => '[]',
                'description' => 'Represents the final exam to conclude the academic year'
            ],
            [
                'key' => 'exam.auto_create',
                'label' => 'Auto Create Exam',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => "Automatically generate exams when a new semester is created for seamless planning"
            ]
        ];
        $settingCategoryName = 'Exam Settings';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();
        foreach ($settingDefs as $settingDef) {
            SettingDefination::create([
                'key' => $settingDef['key'],
                'data_type' => $settingDef['data_type'],
                'default_value' => $settingDef['default_value'],
                'name' => $settingDef['label'],
                'description' => null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
    public function gradeSettingDefination()
    {
        $letterGrades = LetterGrade::all();

        $settingDefs = [
            [
                'key' => 'grade.max_gpa',
                'label' => 'Maximum Attainable GPA',
                'data_type' => 'decimal',
                'default_value' => 4.00,
                'description' => 'The highest possible Grade Point Average (GPA) a student can achieve. This defines the GPA scale for the institution (e.g., 4.0, 5.0, or 7.0).',
            ],
            [
                'key' => 'grade.passing_gpa',
                'label' => 'Minimum Passing GPA',
                'data_type' => 'decimal',
                'default_value' => 2.00,
                'description' => 'The lowest cumulative Grade Point Average (GPA) required for a student to be considered in Good Academic Standing and to be eligible for promotion or graduation.',
            ],
            [
                'key' => 'grade.allowed_letter_grades',
                'label' => 'Defined Letter Grade Scheme',
                'data_type' => 'json',
                'default_value' => json_encode($letterGrades->toArray()),
                'description' => 'Allowed Letter Grades For All Evaluation Within the institution',
            ],
        ];

        $settingCategoryName = 'Grade Settings';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();

        foreach ($settingDefs as $settingDef) {
            SettingDefination::create([
                'key' => $settingDef['key'],
                'data_type' => $settingDef['data_type'],
                'default_value' => $settingDef['default_value'],
                'name' => $settingDef['label'],
                'description' => $settingDef['description'] ?? null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
    public function electionTieBreakerSettingDefination()
    {
        $settingDefs = [
            [
                'key' => 'election_tie_breaker.highest_gpa_winner',
                'label' => 'Use Highest GPA as Tie-Breaker',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When TRUE, if two or more candidates receive the same number of votes, the candidate with the **highest cumulative Grade Point Average (GPA)** will be declared the winner.',
            ],
            [
                'key' => 'election_tie_breaker.highest_admin_votes',
                'label' => 'Allow Administrative Vote as Tie-Breaker',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When TRUE, in the event of a tie, the system will allow designated **administrative votes** (e.g., from a Dean or Electoral Committee) to break the tie, if the GPA method is insufficient or disabled.',
            ]
        ];

        $settingCategoryName = 'Election Tie Breaker Setting';
        $settingCategory = SettingCategory::where("name", $settingCategoryName)->first();

        foreach ($settingDefs as $settingDef) {
            SettingDefination::create([
                'key' => $settingDef['key'],
                'data_type' => $settingDef['data_type'],
                'default_value' => $settingDef['default_value'],
                'name' => $settingDef['label'],
                'description' => $settingDef['description'] ?? null,
                'setting_category_id' => $settingCategory->id
            ]);
        }
    }
}
