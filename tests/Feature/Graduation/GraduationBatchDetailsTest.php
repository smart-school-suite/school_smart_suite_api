<?php

namespace Tests\Feature\Graduation;

use App\Exceptions\AppException;
use App\Services\Graduation\GraduationBatchService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GraduationBatchDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2026-11-18 10:00:00');

        DB::table('system_academic_years')->insert(['id' => 'sys', 'name' => '2025/2026', 'year_start' => 2025, 'year_end' => 2026]);
        DB::table('levels')->insert(['id' => 'lvl-3', 'name' => 'Level 300', 'level' => 300, 'program_name' => 'l3', 'level_type_id' => 't']);
        foreach (['swe' => 'Software Engineering', 'net' => 'Networking'] as $id => $name) {
            DB::table('specialties')->insert(['id' => $id, 'specialty_name' => $name, 'registration_fee' => 0, 'school_fee' => 0, 'status' => 'active', 'school_branch_id' => 'branch-1', 'department_id' => 'd', 'level_id' => 'lvl-3']);
        }
        DB::table('graduation_batches')->insert(['id' => 'batch-1', 'name' => 'Class of 2026', 'description' => 'Main ceremony', 'location' => 'Main hall', 'graduation_start_date' => '2026-12-10 09:00:00', 'graduation_end_date' => '2026-12-10 14:00:00', 'school_year_id' => 'sys', 'school_branch_id' => 'branch-1']);

        $records = [
            ['swe', 'alumni', 'eligible', 'cleared'],
            ['swe', 'alumni', 'eligible', 'cleared'],
            ['swe', 'pending', 'pending_review', 'blocked'],
            ['net', 'pending', 'ineligible', 'pending'],
        ];
        foreach ($records as $i => [$specialty, $status, $eligibility, $clearance]) {
            DB::table('graduation_records')->insert(['id' => "rec-$i", 'status' => $status, 'eligibility_status' => $eligibility, 'clearance_status' => $clearance, 'specialty_id' => $specialty, 'school_branch_id' => 'branch-1', 'batch_id' => 'batch-1', 'promotion_run_id' => 'run', 'student_id' => "stu-$i"]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_the_details_summarise_the_batch_and_its_graduates(): void
    {
        $details = app(GraduationBatchService::class)->getGraduationBatchDetails((object) ['id' => 'branch-1'], 'batch-1');

        $this->assertSame(['Class of 2026', 'Main hall', 'upcoming', '2025/2026'], [$details['name'], $details['location'], $details['ceremony_status'], $details['academic_year']['name']]);
        $this->assertSame(4, $details['total_graduates']);
        $this->assertSame(['pending' => 2, 'continuing' => 0, 'alumni' => 2], $details['status_counts']);
        $this->assertSame(['eligible' => 2, 'pending_review' => 1, 'ineligible' => 1], $details['eligibility_counts']);
        $this->assertSame(['cleared' => 2, 'pending' => 1, 'blocked' => 1], $details['clearance_counts']);
        $this->assertSame([['Software Engineering', 3], ['Networking', 1]], array_map(fn($s) => [$s['specialty_name'], $s['graduates']], $details['specialties']));
        $this->assertSame('Level 300', $details['specialties'][0]['level_name']);
    }

    public function test_a_batch_from_another_branch_is_not_found(): void
    {
        $this->expectException(AppException::class);
        app(GraduationBatchService::class)->getGraduationBatchDetails((object) ['id' => 'branch-2'], 'batch-1');
    }
}
