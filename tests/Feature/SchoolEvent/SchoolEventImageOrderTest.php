<?php

namespace Tests\Feature\SchoolEvent;

use App\Exceptions\AppException;
use App\Models\Asset\SchoolEventImage;
use App\Models\SchoolEvent\SchoolEvent;
use App\Services\Asset\SchoolEventImageService;
use App\Services\SchoolEvent\EventService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolEventImageOrderTest extends TestCase
{
    use RefreshDatabase;

    private object $school;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = (object) ['id' => 'branch-1'];
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('event_categories')->insert(['id' => 'cat-1', 'name' => 'Sports', 'status' => true, 'school_branch_id' => 'branch-1']);
    }

    private function event(string $id, string $status = 'active'): SchoolEvent
    {
        DB::table('school_events')->insert([
            'id' => $id,
            'title' => "Event {$id}",
            'status' => $status,
            'school_branch_id' => 'branch-1',
            'event_category_id' => 'cat-1',
            'start_date' => '2099-01-01 09:00:00',
            'end_date' => '2099-01-01 17:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return SchoolEvent::find($id);
    }

    private function image(string $id, string $eventId, int $position, bool $current = true): SchoolEventImage
    {
        DB::table('school_event_pics')->insert([
            'id' => $id,
            'event_id' => $eventId,
            'school_branch_id' => 'branch-1',
            'object_key' => "events/{$id}.png",
            'mime_type' => 'image/png',
            'size_bytes' => 10,
            'is_current' => $current,
            'position' => $position,
            'created_at' => now(),
        ]);

        return SchoolEventImage::find($id);
    }

    private function currentOrder(SchoolEvent $event): array
    {
        return $event->fresh()->currentImages->pluck('id')->all();
    }

    public function test_current_images_are_returned_in_position_order(): void
    {
        $event = $this->event('e-1');
        $this->image('11111111-1111-1111-1111-111111111111', 'e-1', 2);
        $this->image('22222222-2222-2222-2222-222222222222', 'e-1', 0);
        $this->image('33333333-3333-3333-3333-333333333333', 'e-1', 1);
        $this->image('44444444-4444-4444-4444-444444444444', 'e-1', 0, false);

        $this->assertSame([
            '22222222-2222-2222-2222-222222222222',
            '33333333-3333-3333-3333-333333333333',
            '11111111-1111-1111-1111-111111111111',
        ], $this->currentOrder($event));
    }

    public function test_apply_order_mixes_saved_and_new_images_and_sets_the_cover(): void
    {
        $event = $this->event('e-1');
        $a = $this->image('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'e-1', 0);
        $b = $this->image('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'e-1', 1);
        // images uploaded in the same request, in upload order
        $new0 = $this->image('cccccccc-cccc-cccc-cccc-cccccccccccc', 'e-1', 2);
        $new1 = $this->image('dddddddd-dddd-dddd-dddd-dddddddddddd', 'e-1', 3);

        app(SchoolEventImageService::class)->applyOrder(
            $event,
            'branch-1',
            ['new:1', $b->id, 'new:0', $a->id],
            collect([$new0, $new1])
        );

        $this->assertSame([$new1->id, $b->id, $new0->id, $a->id], $this->currentOrder($event));
        $this->assertSame(0, $new1->fresh()->position);
    }

    public function test_unknown_and_missing_entries_keep_the_remaining_images_after_the_listed_ones(): void
    {
        $event = $this->event('e-1');
        $other = $this->event('e-2');
        $a = $this->image('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'e-1', 0);
        $b = $this->image('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'e-1', 1);
        $c = $this->image('cccccccc-cccc-cccc-cccc-cccccccccccc', 'e-1', 2);
        $foreign = $this->image('eeeeeeee-eeee-eeee-eeee-eeeeeeeeeeee', 'e-2', 0);

        app(SchoolEventImageService::class)->applyOrder(
            $event,
            'branch-1',
            [$c->id, $foreign->id, 'new:3', $c->id],
        );

        $this->assertSame([$c->id, $a->id, $b->id], $this->currentOrder($event));
        $this->assertSame(0, $foreign->fresh()->position, 'images of other events are untouched');
        $this->assertSame([$foreign->id], $this->currentOrder($other));
    }

    public function test_an_empty_order_changes_nothing(): void
    {
        $event = $this->event('e-1');
        $a = $this->image('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'e-1', 1);
        $b = $this->image('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'e-1', 0);

        app(SchoolEventImageService::class)->applyOrder($event, 'branch-1', []);

        $this->assertSame([$b->id, $a->id], $this->currentOrder($event));
    }

    public function test_events_by_status_list_uses_the_first_image_as_cover(): void
    {
        $this->event('e-1', 'active');
        $this->event('e-2', 'draft');
        $this->image('aaaaaaaa-aaaa-aaaa-aaaa-aaaaaaaaaaaa', 'e-1', 1);
        $this->image('bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb', 'e-1', 0);

        $service = app(EventService::class);
        $all = $service->getSchoolEventsByStatus($this->school, 'all');
        $drafts = $service->getSchoolEventsByStatus($this->school, 'draft');

        $this->assertCount(2, $all);
        $this->assertSame(['e-2'], $drafts->pluck('id')->all());
        $active = $all->firstWhere('id', 'e-1');
        $this->assertSame('events/bbbbbbbb-bbbb-bbbb-bbbb-bbbbbbbbbbbb.png', $active['cover_image']);
        $this->assertSame(2, $active['image_count']);
        $this->assertSame('Sports', $active['category_name']);
        $this->assertSame('2099-01-01 09:00:00', $active['start_date']);
    }

    public function test_events_by_status_rejects_unknown_status_and_reports_empty_lists(): void
    {
        $service = app(EventService::class);

        try {
            $service->getSchoolEventsByStatus($this->school, 'archived');
            $this->fail('An unknown status should be rejected.');
        } catch (AppException $exception) {
            $this->assertSame(400, $exception->getCode());
        }

        try {
            $service->getSchoolEventsByStatus($this->school, 'scheduled');
            $this->fail('An empty list should be reported.');
        } catch (AppException $exception) {
            $this->assertSame(404, $exception->getCode());
        }
    }

    public function test_summary_counts_events_per_status(): void
    {
        $this->event('e-1', 'active');
        $this->event('e-2', 'active');
        $this->event('e-3', 'draft');
        $this->event('e-4', 'expired');

        $this->assertSame(
            ['draft' => 1, 'scheduled' => 0, 'active' => 2, 'expired' => 1],
            app(EventService::class)->schoolEventSummary($this->school)
        );
    }
}
