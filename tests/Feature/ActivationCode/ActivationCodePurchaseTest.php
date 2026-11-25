<?php

namespace Tests\Feature\ActivationCode;

use App\Models\Schooladmin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * Buying activation codes and redeeming them. Purchases failed with
 * "Unknown column 'code_type'": codes are linked to their type through
 * activation_code_type_id, and redeeming read status/used columns that do
 * not exist either.
 */
class ActivationCodePurchaseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();

        DB::table('countries')->insert(['id' => 'cm', 'country' => 'Cameroon', 'code' => 'CM', 'currency' => 'XAF', 'official_language' => 'en']);
        DB::table('schools')->insert(['id' => 'school-1', 'name' => 'School', 'type' => 'private', 'country_id' => 'cm']);
        DB::table('school_branches')->insert(['id' => 'branch-1', 'school_id' => 'school-1', 'name' => 'Main', 'abbreviation' => 'MN']);
        DB::table('school_admins')->insert([
            'id' => 'admin-1', 'first_name' => 'Fon', 'last_name' => 'Nathalie', 'name' => 'Fon Nathalie',
            'email' => 'fon@example.com', 'password' => 'x', 'username' => 'fnathalie', 'school_branch_id' => 'branch-1',
        ]);
        DB::table('payment_method')->insert([
            ['id' => 'pm-1', 'name' => 'MTN MoMo', 'max_deposit' => 1000000, 'max_withdraw' => 1000000, 'status' => 'active', 'country_id' => 'cm', 'category_id' => 'cat'],
            ['id' => 'pm-off', 'name' => 'Old', 'max_deposit' => 1, 'max_withdraw' => 1, 'status' => 'inactive', 'country_id' => 'cm', 'category_id' => 'cat'],
        ]);
        DB::table('activation_code_types')->insert([
            ['id' => 'type-t', 'name' => 'Teacher', 'price' => 2000, 'status' => 'active', 'type' => 'teacher', 'country_id' => 'cm'],
            ['id' => 'type-s', 'name' => 'Student', 'price' => 1000, 'status' => 'active', 'type' => 'student', 'country_id' => 'cm'],
        ]);

        $admin = Schooladmin::find('admin-1');
        Sanctum::actingAs($admin);
        $this->actingAs($admin, 'schooladmin');
    }

    private function purchase(array $overrides = [])
    {
        return $this->postJson('/api/v1/activation-code/purchase', $overrides + [
            'payment_method_id' => 'pm-1',
            'teacher_code_count' => 2,
            'student_code_count' => 3,
        ]);
    }

    public function test_purchasing_creates_codes_linked_to_their_type(): void
    {
        $this->purchase()->assertOk()->assertJsonPath('data.total_amount', 7000);

        $this->assertSame(2, DB::table('activation_codes')->where('activation_code_type_id', 'type-t')->count());
        $this->assertSame(3, DB::table('activation_codes')->where('activation_code_type_id', 'type-s')->count());
        $this->assertSame(1000.0, (float) DB::table('activation_codes')->where('activation_code_type_id', 'type-s')->value('price'));

        $types = collect($this->getJson('/api/v1/activation-code/school')->assertOk()->json('data'))->countBy('code_type')->all();
        $this->assertEquals(['teacher' => 2, 'student' => 3], $types);
    }

    public function test_an_inactive_payment_method_is_refused(): void
    {
        $this->purchase(['payment_method_id' => 'pm-off'])->assertStatus(400);
        $this->assertSame(0, DB::table('activation_codes')->count());
    }

    public function test_a_purchased_code_activates_a_student_once_and_only_a_student(): void
    {
        $this->purchase();
        $studentCode = DB::table('activation_codes')->where('activation_code_type_id', 'type-s')->value('code');
        $teacherCode = DB::table('activation_codes')->where('activation_code_type_id', 'type-t')->value('code');

        DB::table('parents')->insert(['id' => 'p-1', 'name' => 'P', 'address' => 'A', 'phone' => '1', 'school_branch_id' => 'branch-1']);
        foreach (['s-1', 's-2'] as $id) {
            DB::table('students')->insert([
                'id' => $id, 'name' => "Student {$id}", 'first_name' => 'S', 'last_name' => $id, 'email' => "{$id}@x.com",
                'password' => 'x', 'username' => $id, 'school_branch_id' => 'branch-1', 'specialty_id' => 'x', 'guardian_id' => 'p-1',
                'student_batch_id' => 'b', 'relationship_id' => 'r', 'student_source_id' => 'src', 'gender_id' => 'g',
            ]);
        }

        $this->postJson('/api/v1/activation-code/student/activate', ['student_id' => 's-1', 'activation_code' => $teacherCode])
            ->assertStatus(422)
            ->assertJsonPath('errors.title', 'Wrong Code Type');

        $this->postJson('/api/v1/activation-code/student/activate', ['student_id' => 's-1', 'activation_code' => $studentCode])->assertOk();
        $this->assertSame('subscribed', DB::table('students')->where('id', 's-1')->value('sub_status'));
        $this->assertSame(1, DB::table('activation_code_usages')->where('actorable_id', 's-1')->count());

        $this->postJson('/api/v1/activation-code/student/activate', ['student_id' => 's-2', 'activation_code' => $studentCode])
            ->assertStatus(409)
            ->assertJsonPath('errors.title', 'Code Already Used');
    }

    public function test_a_purchased_code_activates_a_teacher(): void
    {
        $this->purchase();
        $teacherCode = DB::table('activation_codes')->where('activation_code_type_id', 'type-t')->value('code');
        DB::table('teachers')->insert([
            'id' => 't-1', 'name' => 'Ada Lovelace', 'first_name' => 'Ada', 'last_name' => 'Lovelace', 'email' => 'ada@x.com',
            'password' => 'x', 'username' => 'alovelace', 'school_branch_id' => 'branch-1', 'gender_id' => 'g',
        ]);

        $this->postJson('/api/v1/activation-code/teacher/activate', ['teacher_id' => 't-1', 'activation_code' => $teacherCode])->assertOk();
        $this->assertSame(1, DB::table('activation_code_usages')->where('actorable_id', 't-1')->count());
    }
}
