<?php

namespace Tests\Feature\Promotion;

use App\Models\Promotion\StudentPromotionRecord;
use App\Models\RegistrationFee;
use App\Models\SchoolBranchSetting;
use App\Models\Specialty;
use App\Models\TuitionFees;
use App\Services\Promotion\StudentPromotionRecordService;
use App\Services\Specialty\SpecialtyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PromotionBillingTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();
        $this->school = (object) ['id' => 'branch-1'];

        DB::table('setting_categories')->insert(['id' => 'cat', 'name' => 'Settings', 'key' => 'setting.category.test']);
        foreach ([
            'level.start' => ['string', 'level_start_100'],
            'promotion.bill_student_registration_fee' => ['boolean', true],
            'level.registration_fees' => ['json', [['level_id' => 'lvl-1', 'fee' => 10000], ['level_id' => 'lvl-2', 'fee' => 20000]]],
            'level.tuition_fees' => ['json', json_encode([['level_id' => 'lvl-2', 'fee' => 300000]])],
        ] as $key => [$type, $value]) {
            DB::table('setting_definations')->insert(['id' => "def-$key", 'name' => $key, 'key' => $key, 'data_type' => $type, 'setting_category_id' => 'cat']);
            SchoolBranchSetting::create(['school_branch_id' => 'branch-1', 'setting_defination_id' => "def-$key", 'value' => $value]);
        }
        DB::table('level_types')->insert(['id' => 'type-100', 'name' => '100-Level Start', 'program_name' => 'level_start_100', 'description' => '-']);
        foreach (['lvl-1' => 100, 'lvl-2' => 200] as $id => $level) {
            DB::table('levels')->insert(['id' => $id, 'name' => "Level $level", 'level' => $level, 'program_name' => $id, 'level_type_id' => 'type-100']);
        }
        foreach ([['swe-1', 'lvl-1', 50000, 250000], ['swe-2', 'lvl-2', 60000, 400000]] as [$id, $level, $registration, $tuition]) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => 'Software Engineering', 'registration_fee' => $registration, 'school_fee' => $tuition, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'computing', 'level_id' => $level]);
        }
        DB::table('students')->insert(['id' => 'stu-1', 'name' => 'S', 'first_name' => 'S', 'last_name' => 'S', 'email' => 's@example.com', 'password' => 'x', 'username' => 's', 'school_branch_id' => 'branch-1', 'specialty_id' => 'swe-1', 'guardian_id' => 'g', 'student_batch_id' => 'b', 'gender_id' => 'g', 'relationship_id' => 'r', 'student_source_id' => 's']);
        DB::table('student_promotion_records')->insert(['id' => 'rec-1', 'school_branch_id' => 'branch-1', 'student_id' => 'stu-1', 'from_specialty_id' => 'swe-1', 'promotion_run_id' => 'run-1', 'outcome' => 'pending_review', 'status' => 'pending']);
    }

    private function promote(): void
    {
        app(StudentPromotionRecordService::class)->promoteStudent($this->school, ['record_id' => 'rec-1'], (object) ['id' => 'admin-1']);
    }

    public function test_promotion_bills_the_new_levels_registration_fee_and_tuition(): void
    {
        $this->promote();

        $this->assertSame('swe-2', DB::table('students')->value('specialty_id'));
        $this->assertSame(60000.0, RegistrationFee::where('student_id', 'stu-1')->where('specialty_id', 'swe-2')->value('amount'));
        $this->assertEquals(400000, TuitionFees::where('student_id', 'stu-1')->where('specialty_id', 'swe-2')->value('tution_fee_total'));
    }

    public function test_no_registration_fee_is_charged_when_the_setting_is_off(): void
    {
        SchoolBranchSetting::where('setting_defination_id', 'def-promotion.bill_student_registration_fee')->first()->update(['value' => false]);

        $this->promote();

        $this->assertSame(0, RegistrationFee::count());
        $this->assertSame(1, TuitionFees::where('specialty_id', 'swe-2')->count());
    }

    public function test_existing_fee_records_for_the_new_level_are_not_duplicated(): void
    {
        RegistrationFee::create(['specialty_id' => 'swe-2', 'school_branch_id' => 'branch-1', 'amount' => 1, 'status' => 'not paid', 'student_id' => 'stu-1']);

        $this->promote();

        $this->assertSame(1, RegistrationFee::where('specialty_id', 'swe-2')->count());
    }

    public function test_a_new_specialty_without_fees_takes_its_levels_fees(): void
    {
        DB::table('departments')->insert(['id' => 'computing', 'school_branch_id' => 'branch-1', 'department_name' => 'Computing']);
        $service = app(SpecialtyService::class);

        $defaulted = $service->createSpecialty(['specialty_name' => 'Networking', 'department_id' => 'computing', 'level_id' => 'lvl-2'], $this->school, null);
        $this->assertEquals([20000, 300000], [(float) $defaulted->registration_fee, (float) $defaulted->school_fee]);

        $given = $service->createSpecialty(['specialty_name' => 'Networking', 'department_id' => 'computing', 'level_id' => 'lvl-1', 'registration_fee' => 5, 'school_fee' => 0], $this->school, null);
        $this->assertEquals([5, 0], [(float) $given->registration_fee, (float) $given->school_fee], 'Given fees, including 0, are kept.');

        $this->assertSame('active', Specialty::find($given->id)->status);
    }
}
