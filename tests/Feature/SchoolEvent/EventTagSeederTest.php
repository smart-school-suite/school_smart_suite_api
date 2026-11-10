<?php

namespace Tests\Feature\SchoolEvent;

use Database\Seeders\EventTagSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class EventTagSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_seeds_each_event_tag_once(): void
    {
        $this->seed(EventTagSeeder::class);

        $expected = collect(EventTagSeeder::TAGS)->unique(fn ($name) => Str::lower($name))->count();
        $this->assertSame($expected, DB::table('event_tags')->count());
        $this->assertTrue(DB::table('event_tags')->where('name', 'Inter-House Sports')->exists());
    }

    public function test_running_it_again_keeps_existing_tags_and_adds_no_duplicates(): void
    {
        DB::table('event_tags')->insert([
            'id' => (string) Str::uuid(),
            'name' => 'sports',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->seed(EventTagSeeder::class);
        $this->seed(EventTagSeeder::class);

        $this->assertSame(1, DB::table('event_tags')->whereRaw('lower(name) = ?', ['sports'])->count());
        $expected = collect(EventTagSeeder::TAGS)->unique(fn ($name) => Str::lower($name))->count();
        $this->assertSame($expected, DB::table('event_tags')->count());
    }
}
