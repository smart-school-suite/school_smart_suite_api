<?php

namespace Tests\Feature\School;

use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

    private function useFakeR2(): void
    {
        Storage::fake('r2');
        config([
            'filesystems.disks.r2.key' => 'test-key',
            'filesystems.disks.r2.secret' => 'test-secret',
            'filesystems.disks.r2.bucket' => 'test-bucket',
            'filesystems.disks.r2.endpoint' => 'https://account.r2.cloudflarestorage.com',
        ]);
    }

    public function test_a_png_or_webp_logo_uploads_and_becomes_the_current_logo(): void
    {
        $this->useFakeR2();

        foreach (['logo.png', 'logo.webp'] as $name) {
            $upload = $this->post('/api/v1/school-asset/school-logo/upload', [
                'school_logo' => UploadedFile::fake()->image($name, 256, 256),
            ], ['Accept' => 'application/json'])->assertOk();

            Storage::disk('r2')->assertExists($upload->json('data.object_key'));
            $this->assertStringStartsWith('schools/school-1/logo/', $upload->json('data.object_key'));
            $this->getJson('/api/v1/school')->assertJsonPath('data.current_logo.id', $upload->json('data.id'));
        }
    }

    public function test_a_gif_logo_is_rejected_before_upload(): void
    {
        $this->useFakeR2();

        $this->post('/api/v1/school-asset/school-logo/upload', [
            'school_logo' => UploadedFile::fake()->image('logo.gif', 64, 64),
        ], ['Accept' => 'application/json'])->assertStatus(422);
    }

    public function test_removing_the_logo_clears_it(): void
    {
        $this->useFakeR2();
        $upload = $this->post('/api/v1/school-asset/school-logo/upload', [
            'school_logo' => UploadedFile::fake()->image('logo.png', 128, 128),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->deleteJson('/api/v1/school-asset/school-logo/remove')->assertOk();

        Storage::disk('r2')->assertMissing($upload->json('data.object_key'));
        $this->getJson('/api/v1/school')->assertJsonPath('data.current_logo', null);
    }
}
