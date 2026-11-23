<?php

namespace Tests\Feature\School;

use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SchoolDetailsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('countries')->insert(['id' => 'cm', 'country' => 'Cameroon', 'code' => 'CM', 'currency' => 'XAF', 'official_language' => 'English']);
        DB::table('schools')->insert(['id' => 'school-1', 'name' => 'Hobbish', 'type' => 'private', 'country_id' => 'cm']);
        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert(['id' => 'admin-1', 'first_name' => 'A', 'last_name' => 'B', 'name' => 'A B', 'email' => 'a@example.com', 'password' => 'x', 'username' => 'ab', 'school_branch_id' => 'branch-1']);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function logo(string $id, bool $current, string $createdAt): array
    {
        return ['id' => $id, 'object_key' => "schools/school-1/logo/{$id}.png", 'mime_type' => 'image/png', 'size_bytes' => 10, 'is_current' => $current, 'created_at' => $createdAt, 'replaced_at' => $current ? null : $createdAt, 'school_id' => 'school-1'];
    }

    public function test_school_logos_are_linked_to_schools_by_school_id(): void
    {
        $this->assertTrue(Schema::hasColumn('school_logos', 'school_id'));
        $this->assertFalse(Schema::hasColumn('school_logos', 'school'));
    }

    public function test_school_details_load_with_the_current_logo(): void
    {
        DB::table('school_logos')->insert([
            $this->logo('00000000-0000-0000-0000-000000000001', false, '2026-11-01 10:00:00'),
            $this->logo('00000000-0000-0000-0000-000000000002', true, '2026-11-20 10:00:00'),
        ]);

        $this->getJson('/api/v1/school')
            ->assertOk()
            ->assertJsonPath('data.name', 'Hobbish')
            ->assertJsonPath('data.current_logo.id', '00000000-0000-0000-0000-000000000002');
    }

    public function test_school_details_load_without_a_logo(): void
    {
        $this->getJson('/api/v1/school')
            ->assertOk()
            ->assertJsonPath('data.current_logo', null);
    }
}
