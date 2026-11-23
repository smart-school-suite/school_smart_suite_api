<?php

namespace Tests\Feature\SchoolAdmin;

use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SchoolAdminProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            ['id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie', 'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1'],
            ['id' => 'admin-2', 'first_name' => 'Ade', 'last_name' => 'Bello', 'name' => 'Ade Bello', 'email' => 'ade@example.com', 'password' => 'x', 'username' => 'Taken.Name', 'school_branch_id' => 'branch-1'],
        ]);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function checks(string $username): array
    {
        return $this->getJson('/api/v1/school-admin/username-availability?username=' . urlencode($username))
            ->assertOk()
            ->json('data');
    }

    private function failedKeys(array $result): array
    {
        return collect($result['checks'])->where('passed', false)->pluck('key')->values()->all();
    }

    public function test_a_free_well_formed_username_passes_every_check(): void
    {
        $result = $this->checks('New.Name ');

        $this->assertSame('new.name', $result['username']);
        $this->assertTrue($result['valid']);
        $this->assertSame([], $this->failedKeys($result));
        $this->assertSame(['length', 'characters', 'edges', 'separators', 'available'], array_column($result['checks'], 'key'));
    }

    public function test_each_format_rule_is_reported_separately(): void
    {
        $this->assertSame(['length', 'available'], $this->failedKeys($this->checks('ab')));
        $this->assertSame(['characters', 'available'], $this->failedKeys($this->checks('fon-nathalie')));
        $this->assertSame(['edges', 'available'], $this->failedKeys($this->checks('_fon')));
        $this->assertSame(['separators', 'available'], $this->failedKeys($this->checks('fon..n')));
    }

    public function test_a_username_used_by_another_admin_is_taken_regardless_of_case(): void
    {
        $this->assertSame(['available'], $this->failedKeys($this->checks('taken.name')));
    }

    public function test_your_own_username_counts_as_available(): void
    {
        $this->assertTrue($this->checks('FNathalie')['valid']);
    }

    public function test_profile_update_saves_username_and_phone(): void
    {
        $this->putJson('/api/v1/school-admin/update-profile', ['username' => ' New.Name', 'phone' => '677123456'])
            ->assertOk();

        $admin = Schooladmin::find('admin-1');
        $this->assertSame('new.name', $admin->username);
        $this->assertSame('677123456', $admin->phone);
    }

    public function test_profile_update_rejects_a_badly_formed_or_taken_username(): void
    {
        $this->putJson('/api/v1/school-admin/update-profile', ['username' => 'a_'])->assertStatus(422);
        $this->putJson('/api/v1/school-admin/update-profile', ['username' => 'taken.name'])->assertStatus(422);

        $this->assertSame('fnathalie', Schooladmin::find('admin-1')->username);
    }

    public function test_me_returns_only_the_current_avatar_with_a_url(): void
    {
        Storage::fake('r2');
        config(['filesystems.disks.r2.url' => 'https://cdn.example.com']);

        DB::table('school_admin_avatars')->insert([
            ['id' => '00000000-0000-0000-0000-000000000001', 'object_key' => 'schools/branch-1/school-admins/admin-1/profile/old.webp', 'mime_type' => 'image/webp', 'size_bytes' => 10, 'is_current' => false, 'created_at' => now()->subDay(), 'replaced_at' => now(), 'school_branch_id' => 'branch-1', 'school_admin_id' => 'admin-1'],
            ['id' => '00000000-0000-0000-0000-000000000002', 'object_key' => 'schools/branch-1/school-admins/admin-1/profile/new.webp', 'mime_type' => 'image/webp', 'size_bytes' => 10, 'is_current' => true, 'created_at' => now(), 'replaced_at' => null, 'school_branch_id' => 'branch-1', 'school_admin_id' => 'admin-1'],
        ]);

        $avatar = $this->getJson('/api/v1/auth/school-admin/me')
            ->assertOk()
            ->json('data.authSchoolAdmin.current_avatar');

        $this->assertSame('00000000-0000-0000-0000-000000000002', $avatar['id']);
        $this->assertStringEndsWith('new.webp', $avatar['url']);
    }

    public function test_me_returns_no_avatar_after_it_was_removed(): void
    {
        DB::table('school_admin_avatars')->insert([
            'id' => '00000000-0000-0000-0000-000000000001', 'object_key' => 'k.webp', 'mime_type' => 'image/webp', 'size_bytes' => 10, 'is_current' => false, 'created_at' => now(), 'replaced_at' => now(), 'school_branch_id' => 'branch-1', 'school_admin_id' => 'admin-1',
        ]);

        $this->getJson('/api/v1/auth/school-admin/me')
            ->assertOk()
            ->assertJsonPath('data.authSchoolAdmin.current_avatar', null);
    }

    public function test_uploading_then_removing_a_photo_updates_what_me_returns(): void
    {
        Storage::fake('r2');
        config(['filesystems.disks.r2.url' => 'https://cdn.example.com']);
        DB::table('asset_upload_policies')->insert([
            'id' => '00000000-0000-0000-0000-0000000000aa',
            'asset_type' => 'school_admin_profile_picture',
            'max_file_size_bytes' => 2 * 1024 * 1024,
            'allowed_mime_types' => json_encode(['image/jpeg', 'image/png', 'image/webp']),
            'is_enabled' => true,
        ]);

        $upload = $this->post('/api/v1/school-asset/school-admin/upload', [
            'picture' => UploadedFile::fake()->image('profile-photo.png', 512, 512),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->assertNotNull($upload->json('data.url'));
        Storage::disk('r2')->assertExists($upload->json('data.object_key'));
        $this->assertSame(
            $upload->json('data.id'),
            $this->getJson('/api/v1/auth/school-admin/me')->json('data.authSchoolAdmin.current_avatar.id'),
        );

        $this->deleteJson('/api/v1/school-asset/school-admin/remove')->assertOk();

        Storage::disk('r2')->assertMissing($upload->json('data.object_key'));
        $this->getJson('/api/v1/auth/school-admin/me')->assertJsonPath('data.authSchoolAdmin.current_avatar', null);
    }
}
