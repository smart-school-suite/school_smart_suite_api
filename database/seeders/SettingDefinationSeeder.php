<?php

namespace Database\Seeders;

use App\Models\Educationlevels;
use Illuminate\Database\Seeder;
use App\Models\SettingCategory;
use App\Models\SettingDefination;
use App\Models\SchoolBranchSetting;
use Illuminate\Support\Facades\DB;
use App\Models\LetterGrade;

class SettingDefinationSeeder extends Seeder
{
    /** Every key defined in this seeder; anything else in the table is removed. */
    private array $definedKeys = [];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->studentAdditionalFeeSetting();
        $this->studentPromotionSettingDefination();
        $this->timetableSettingDefination();
        $this->resitSettingDefination();
        $this->examSettingDefination();
        $this->gradeSettingDefination();
        $this->electionTieBreakerSettingDefination();
        $this->levelSetting();
        $this->graduationSetting();
        $this->semesterSetting();
        $this->academicYearSetting();

        app(AutoCreationSettingsUpgrade::class)->run();
        $this->removeUndefinedSettings();
    }

    /**
     * Delete setting definitions that are no longer listed here, together
     * with every school's value for them. school_branch_settings has no
     * cascade on setting_defination_id, so its rows go first.
     */
    private function removeUndefinedSettings(): void
    {
        if (empty($this->definedKeys)) {
            return;
        }

        $removed = SettingDefination::whereNotIn('key', $this->definedKeys)->pluck('key', 'id');

        if ($removed->isEmpty()) {
            return;
        }

        DB::transaction(function () use ($removed) {
            SchoolBranchSetting::whereIn('setting_defination_id', $removed->keys())->delete();
            SettingDefination::whereIn('id', $removed->keys())->delete();
        });

        $this->command?->info('Removed settings no longer defined: ' . $removed->implode(', '));
    }


    public function studentAdditionalFeeSetting(): void
    {
        $settingDefs = [
            [
                'key' => 'additionalFee.default_due_days',
                'label' => 'Default Due Period (Days)',
                'data_type' => 'integer',
                'default_value' => 30,
                'description' => 'The number of days from the day a student is billed an additional fee to its due date, used whenever no due date is entered. For example, 10 makes a fee billed today due in 10 days. A due date entered when billing always takes precedence.',
            ],
        ];

        $this->seedDefinitions('Student Additional Fee Settings', $settingDefs);
    }
    public function studentPromotionSettingDefination(): void
    {
        $promotionSettingDefs = [
            [
                'key' => 'promotion.min_gpa',
                'label' => 'Minimum Average GPA for Promotion',
                'data_type' => 'decimal',
                'default_value' => 2.00,
                'description' => 'The minimum Grade Point Average (GPA) a student must attain to qualify for promotion to the next academic level.',
            ],
            [
                'key' => 'promotion.max_tuition_fee_debt',
                'label' => 'Maximum Outstanding Tuition Fees',
                'data_type' => 'decimal',
                'default_value' => 300000.00,
                'description' => 'The maximum outstanding tuition fee balance a student may owe and still qualify for promotion. Students whose outstanding tuition fees exceed this amount will not meet this promotion criterion.',
            ],
            [
                'key' => 'promotion.max_additional_fee_debt',
                'label' => 'Maximum Outstanding Additional Fees',
                'data_type' => 'decimal',
                'default_value' => 50000.00,
                'description' => 'The maximum outstanding balance a student may owe for additional school fees, such as library, laboratory, or other non-tuition charges, and still qualify for promotion.',
            ],
            [
                'key' => 'promotion.max_carry_overs',
                'label' => 'Maximum Carry-Over Courses',
                'data_type' => 'integer',
                'default_value' => 5,
                'description' => 'The maximum number of failed courses from a previous academic level that a student may carry over and still qualify for promotion to the next level.',
            ],
            [
                'key' => 'promotion.max_resits',
                'label' => 'Maximum Resits',
                'data_type' => 'integer',
                'default_value' => 2,
                'description' => 'The maximum number of resit examinations a student may have and still qualify for promotion to the next academic level.',
            ],
            [
                'key' => 'promotion.bill_student_registration_fee',
                'label' => 'Charge Registration Fee Upon Promotion',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, students are charged the applicable registration fee when they are promoted to the next academic level. Disable this option if students should not be charged a registration fee upon promotion.',
            ],
        ];

        $this->seedDefinitions('Student Promotion Setting', $promotionSettingDefs);
    }
    public function timetableSettingDefination(): void
    {
        $settingDefs = [
            [
                'key' => 'timetable.ignore_teacher_preference',
                'label' => 'Ignore Teacher Scheduling Preferences',
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => 'When enabled, the timetable generator may disregard teachers’ preferred teaching times and scheduling restrictions when assigning lessons. This gives the system greater scheduling flexibility but may result in lessons being scheduled outside teachers’ stated preferences or availability.',
            ],
            [
                'key' => 'timetable.respect_teacher_preference',
                'label' => 'Respect Teacher Scheduling Preferences',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, the timetable generator considers teachers’ preferred teaching times and scheduling restrictions when assigning lessons. This helps produce timetables that accommodate teacher availability and scheduling preferences wherever possible.',
            ],
            [
                'key' => 'timetable.period_duration',
                'label' => 'Teaching Period Duration',
                'data_type' => 'integer',
                'default_value' => 60,
                'description' => 'The duration of each teaching period, measured in minutes. This value determines the length of an individual lesson period when generating the timetable.',
            ],
            [
                'key' => 'timetable.school_day_start',
                'label' => 'School Day Opening Time',
                'data_type' => 'string',
                'default_value' => '08:00',
                'description' => 'The time the school day begins, in 24-hour HH:MM format. Lessons are only scheduled from this time onwards.',
            ],
            [
                'key' => 'timetable.school_day_end',
                'label' => 'School Day Closing Time',
                'data_type' => 'string',
                'default_value' => '17:00',
                'description' => 'The time the school day ends, in 24-hour HH:MM format. Lessons are only scheduled to finish by this time.',
            ],
        ];

        $this->seedDefinitions('Timetable Settings', $settingDefs);
    }
    public function resitSettingDefination(): void
    {
        $settingDefs = [
            [
                'key' => 'resitFee.generalBilling',
                'label' => 'Use a Standard Resit Fee',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'Charge the same resit examination fee for every course, regardless of the student’s academic level.',
            ],
            [
                'key' => 'resitFee.levelBilling',
                'label' => 'Use Different Resit Fees by Academic Level',
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => 'Set different resit examination fees for each academic level. Choose this option if students at different levels should pay different amounts.',
            ],
            [
                'key' => 'resitFee.generalBillingFee',
                'label' => 'Standard Resit Fee Amount',
                'data_type' => 'decimal',
                'default_value' => 3000.00,
                'description' => 'The amount students must pay for each course they resit when the standard resit fee option is selected.',
            ],
            [
                'key' => 'resitFee.levelBillingFee',
                'label' => 'Resit Fees by Academic Level',
                'data_type' => 'json',
                'default_value' => '[]',
                'description' => 'Set the resit examination fee for each academic level, such as Level 100 or Level 200. These amounts apply when different fees by academic level are enabled.',
            ]
        ];

        $this->seedDefinitions('Resit Settings', $settingDefs);
    }
    public function examSettingDefination(): void
    {
        $settingDefs = [
            [
                'key' => 'exam.auto_create',
                'label' => 'Automatically Create Exams Each Semester',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, exams are automatically created when a new semester is created, reducing the need to set them up manually.',
            ],
            [
                'key' => 'exam.ca_default_max_score',
                'label' => 'Maximum Continuous Assessment Score',
                'data_type' => 'decimal',
                'default_value' => 30,
                'description' => 'The maximum score students can earn in Continuous Assessment (CA). This score contributes to the overall course result.',
            ],
            [
                'key' => 'exam.exam_default_max_score',
                'label' => 'Maximum Final Exam Score',
                'data_type' => 'decimal',
                'default_value' => 70,
                'description' => 'The maximum score students can earn in the final examination. This score contributes to the overall course result.',
            ],
            [
                'key' => 'exam.resit_exam_default_max_score',
                'label' => 'Maximum Resit Exam Score',
                'data_type' => 'decimal',
                'default_value' => 100,
                'description' => 'The maximum score a student can earn in a resit examination.',
            ],
        ];

        $this->seedDefinitions('Exam Settings', $settingDefs);
    }
    public function gradeSettingDefination(): void
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

        $this->seedDefinitions('Grade Settings', $settingDefs);
    }
    public function electionTieBreakerSettingDefination(): void
    {
        $settingDefs = [
            [
                'key' => 'election_tie_breaker.highest_gpa_winner',
                'label' => 'Use Highest GPA to Resolve Ties',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, the candidate with the highest cumulative Grade Point Average (GPA) wins if two or more candidates receive the same number of votes.',
            ],
            [
                'key' => 'election_tie_breaker.highest_admin_votes',
                'label' => 'Allow Administrative Votes to Resolve Ties',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, designated election administrators or committee members can cast additional votes to resolve a tie when the GPA method is disabled or does not determine a winner.',
            ],
        ];

        $this->seedDefinitions('Election Setting', $settingDefs);
    }
    public function levelSetting(): void
    {
        $levels = Educationlevels::whereHas("levelType", function ($query) {
            $query->where("program_name", "level_start_100");
        })->get();

        $settingDefs = [
            [
                'key' => 'level.start',
                'label' => 'Starting Academic Level',
                'data_type' => 'string',
                'default_value' => "level_start_100",
                'description' => 'The academic level at which students begin their studies at your institution, such as Level 100, Level 200, or Level 300.',
            ],
            [
                'key' => 'specialties.auto_generate_levels',
                'label' => 'Automatically Create Specialties Across All Levels',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, a new specialty is automatically created for every active academic level. Disable this option if you prefer to create specialties for each level individually.',
            ],
            [
                'key' => 'level.registration_fees',
                'label' => 'Registration Fees by Academic Level',
                'data_type' => 'json',
                'default_value' => json_encode($levels->map(fn($l) => [
                    'level_id' => $l->id,
                    'fee' => 50000
                ])),
                'description' => 'Set the registration fee for each academic level. Students are charged the fee configured for their level when registration fees apply.',
            ],
            [
                'key' => 'level.tuition_fees',
                'label' => 'Tuition Fees by Academic Level',
                'data_type' => 'json',
                'default_value' => json_encode($levels->map(fn($l) => [
                    'level_id' => $l->id,
                    'fee' => 350000
                ])),
                'description' => 'Set the tuition fee for each academic level. Students are charged the tuition fee configured for their level.',
            ],
        ];

        $this->seedDefinitions('Level Settings', $settingDefs);
    }
    public function graduationSetting(): void
    {
        $settingDefs = [
            [
                'key' => 'graduation.fee_enabled',
                'label' => 'Charge a Graduation Fee',
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => 'When enabled, students are required to pay the applicable graduation fee before completing graduation clearance.',
            ],
            [
                'key' => 'graduation.fee',
                'label' => 'Standard Graduation Fee Amount',
                'data_type' => 'decimal',
                'default_value' => 50000,
                'description' => 'The graduation fee charged to eligible students when the standard fee option is used, regardless of their academic level.',
            ],
            [
                'key' => 'graduation.level_graduation_fee_enabled',
                'label' => 'Use Different Graduation Fees by Academic Level',
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => 'When enabled, graduation fees can be set separately for each academic level instead of using one standard amount for all eligible students.',
            ],
            [
                'key' => 'graduation.level_graduation_fee',
                'label' => 'Graduation Fees by Academic Level',
                'data_type' => 'json',
                'default_value' => '[]',
                'description' => 'Set the graduation fee for each academic level. These amounts apply when different fees by academic level are enabled.',
            ],
        ];

        $this->seedDefinitions('Graduation Settings', $settingDefs);
    }
    public function semesterSetting(): void
    {
        $levelIds = Educationlevels::whereHas("levelType", function ($query) {
            $query->where("program_name", "level_start_100");
        })->pluck('id')->values()->all();

        $settingDefs = [
            [
                'key' => 'semester.max_per_academic_year',
                'label' => 'Number of Semesters',
                'data_type' => 'integer',
                'default_value' => 2,
                'description' => 'The maximum number of semesters in an academic year. Semesters beyond this number cannot be created.',
            ],
            ...$this->autoCreationDefinitions('semester.auto_create_', 'semesters', 'Semester', '', $levelIds),
        ];

        $this->seedDefinitions('Semester Settings', $settingDefs);
    }
    public function academicYearSetting(): void
    {
        $levelIds = Educationlevels::whereHas("levelType", function ($query) {
            $query->where("program_name", "level_start_100");
        })->pluck('id')->values()->all();

        $settingDefs = [
            ...$this->autoCreationDefinitions('academic_year.auto_create_', 'academic years', 'Academic Year', 'Academic Years ', $levelIds),
        ];

        $this->seedDefinitions('Academic Year Settings', $settingDefs);
    }
    private function autoCreationDefinitions(string $prefix, string $noun, string $title, string $labelPrefix, array $levelIds): array
    {
        $only = "Only one of the four options can be on. Only applies when Automatic {$title} Creation is on.";

        return [
            [
                'key' => $prefix . 'enabled',
                'label' => "Automatic {$title} Creation",
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => "When on, creating one of the {$noun} also creates it for other specialty levels, as chosen below.",
            ],
            [
                'key' => $prefix . 'levels',
                'label' => $labelPrefix === '' ? 'Applicable Academic Levels' : "Applicable Academic Levels for " . trim($labelPrefix),
                'data_type' => 'json',
                'default_value' => $levelIds,
                'description' => "Only the selected levels receive automatically created {$noun}. The level a record is created for always receives it. Only applies when Automatic {$title} Creation is on.",
            ],
            [
                'key' => $prefix . 'across_levels',
                'label' => "{$labelPrefix}Across All Levels",
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => "Creates {$noun} for every specialty in the school at the applicable levels, whatever the specialty or department. {$only}",
            ],
            [
                'key' => $prefix . 'across_specialty',
                'label' => "{$labelPrefix}Across the Same Specialty",
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => "Creates {$noun} for the other applicable levels of the same specialty, in any department. {$only}",
            ],
            [
                'key' => $prefix . 'across_department_levels',
                'label' => "{$labelPrefix}Across All Levels in the Department",
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => "Creates {$noun} for every specialty in the same department at the applicable levels. {$only}",
            ],
            [
                'key' => $prefix . 'across_department_specialty',
                'label' => "{$labelPrefix}Across the Same Specialty in the Department",
                'data_type' => 'boolean',
                'default_value' => true,
                'description' => "Creates {$noun} for the other applicable levels of the same specialty in the same department. {$only}",
            ],
            [
                'key' => $prefix . 'require_students',
                'label' => $labelPrefix === '' ? 'Only Include Specialties with Enrolled Students' : "{$labelPrefix}Only for Specialties with Enrolled Students",
                'data_type' => 'boolean',
                'default_value' => false,
                'description' => "When on, {$noun} are only created automatically for specialty levels with at least one enrolled student. The level a record is created for always receives it. Only applies when Automatic {$title} Creation is on.",
            ],
        ];
    }

    /**
     * Names are unique. When a definition takes over a name still held by
     * another key (one being retired), that one is renamed first; it is
     * removed with the other undefined settings at the end of the run.
     */
    private function freeName(string $name, string $key): void
    {
        SettingDefination::where('name', $name)
            ->where('key', '!=', $key)
            ->get()
            ->each(fn (SettingDefination $holder) => $holder->update(['name' => "{$holder->name} ({$holder->key})"]));
    }

    private function seedDefinitions(string $categoryName, array $settingDefs): void
    {
        // Recorded before the category check so a missing category never
        // gets its existing settings deleted as "undefined".
        array_push($this->definedKeys, ...array_column($settingDefs, 'key'));

        $settingCategory = SettingCategory::where('name', $categoryName)->first();

        if (!$settingCategory) {
            return;
        }

        foreach ($settingDefs as $settingDef) {
            $this->freeName($settingDef['label'], $settingDef['key']);
            SettingDefination::updateOrCreate(
                ['key' => $settingDef['key']],
                [
                    'name' => $settingDef['label'],
                    'data_type' => $settingDef['data_type'],
                    'default_value' => $settingDef['default_value'],
                    'description' => $settingDef['description'] ?? null,
                    'setting_category_id' => $settingCategory->id,
                ]
            );
        }
    }
}
