<?php

namespace Tests\Feature\Hall;

use App\Models\HallType;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\HallTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HallTypeSeederTest extends TestCase
{
    use RefreshDatabase;

    private function seed_(): void
    {
        (new HallTypeSeeder())->run();
    }

    public function test_it_fills_the_table_with_the_three_hall_types(): void
    {
        $this->seed_();

        $types = HallType::orderBy('key')->get()->keyBy('key');
        $this->assertSame(['exam.hall', 'lecture.hall', 'practical.hall'], $types->keys()->all());
        $this->assertSame(['Exam Hall', 'Lecture Hall', 'Practical Hall'], $types->pluck('name')->values()->all());
        foreach ($types as $type) {
            $this->assertNotEmpty($type->description);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $type->text_color);
            $this->assertMatchesRegularExpression('/^#[0-9a-f]{6}$/', $type->background_color);
            $this->assertSame('active', $type->status);
        }
    }

    public function test_running_it_again_adds_nothing_and_keeps_administrator_edits(): void
    {
        $this->seed_();
        HallType::where('key', 'exam.hall')->update(['name' => 'Examination Room', 'text_color' => '#000000']);

        $this->seed_();

        $this->assertSame(3, HallType::count());
        $exam = HallType::where('key', 'exam.hall')->sole();
        $this->assertSame(['Examination Room', '#000000'], [$exam->name, $exam->text_color]);
    }

    public function test_a_type_created_through_the_api_without_the_key_is_adopted_not_duplicated(): void
    {
        DB::table('hall_types')->insert(['id' => 'api-made', 'name' => 'exam hall', 'key' => '', 'status' => 'active']);

        $this->seed_();

        $this->assertSame(1, HallType::whereRaw('LOWER(name) = ?', ['exam hall'])->count());
        $adopted = HallType::find('api-made');
        $this->assertSame('exam.hall', $adopted->key, 'Exam timetables find exam halls by this key.');
        $this->assertNotEmpty($adopted->description);
    }

    public function test_a_type_matched_by_name_takes_the_standard_key(): void
    {
        DB::table('hall_types')->insert(['id' => 'api-made', 'name' => 'Exam Hall', 'key' => 'exam_hall', 'status' => 'active', 'description' => 'Our own']);

        $this->seed_();

        $this->assertSame(['exam.hall', 'Our own'], [HallType::find('api-made')->key, HallType::find('api-made')->description]);
        $this->assertSame(3, HallType::count());
    }

    public function test_the_deploy_seeder_runs_it(): void
    {
        $source = file_get_contents((new \ReflectionClass(DatabaseSeeder::class))->getFileName());

        $this->assertStringContainsString('$this->call(HallTypeSeeder::class);', $source);
    }
}
