<?php

namespace Tests\Feature\SchoolEvent;

use App\Exceptions\AppException;
use App\Http\Requests\Event\CreateEventRequest;
use App\Models\SchoolEvent\SchoolEvent;
use App\Services\SchoolEvent\CreateEventService;
use App\Services\SchoolEvent\UpdateDraftEventStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class SchoolEventDraftTest extends TestCase
{
    use RefreshDatabase;

    private const CATEGORY = '11111111-2222-3333-4444-555555555555';

    private object $school;
    private array $author = ['userId' => 'admin-1', 'userType' => 'App\\Models\\Schooladmin', 'authUser' => null];

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();

        $this->school = (object) ['id' => 'branch-1'];
        DB::table('school_branches')->insert(['id' => 'branch-1', 'name' => 'Main', 'abbreviation' => 'M', 'school_id' => 'school-1']);
        DB::table('event_categories')->insert(['id' => self::CATEGORY, 'name' => 'Sports', 'status' => true, 'school_branch_id' => 'branch-1']);
    }

    /** Runs the create request's validation and returns the error keys. */
    private function createErrors(array $data): array
    {
        $request = CreateEventRequest::create('/school-event/create', 'POST', $data);
        $request->setContainer($this->app)->setRedirector($this->app->make('redirect'));

        try {
            $request->validateResolved();
            return [];
        } catch (ValidationException $exception) {
            return array_keys($exception->errors());
        }
    }

    public function test_a_draft_with_only_one_detail_is_valid(): void
    {
        $this->assertSame([], $this->createErrors(['status' => 'draft', 'title' => 'Sports Day']));
        $this->assertSame([], $this->createErrors(['status' => 'draft', 'location' => 'Main Field']));
        $this->assertSame([], $this->createErrors(['status' => 'draft', 'school_wide' => 'true']));
        $this->assertSame([], $this->createErrors(['status' => 'draft', 'end_date' => '2099-01-01 17:00']));
    }

    public function test_an_empty_draft_is_rejected(): void
    {
        $this->assertSame(['draft'], $this->createErrors(['status' => 'draft']));
        $this->assertSame(['draft'], $this->createErrors([
            'status' => 'draft',
            'title' => '',
            'student_audience' => [['department_ids' => []]],
        ]));
    }

    public function test_publishing_still_requires_every_detail(): void
    {
        $errors = $this->createErrors(['status' => 'active', 'title' => 'Sports Day', 'school_wide' => 'true']);

        foreach (['description', 'organizer', 'location', 'start_date', 'end_date', 'event_category_id'] as $field) {
            $this->assertContains($field, $errors);
        }
        $this->assertNotContains('title', $errors);
    }

    public function test_a_draft_can_be_saved_without_a_title_or_category(): void
    {
        $event = app(CreateEventService::class)->createSchoolEvent($this->school, $this->author, [
            'status' => 'draft',
            'description' => 'Races and relays for every house.',
        ]);

        $saved = SchoolEvent::find($event->id);
        $this->assertSame('draft', $saved->status);
        $this->assertNull($saved->title);
        $this->assertNull($saved->event_category_id);
        $this->assertSame('Races and relays for every house.', $saved->description);
        $this->assertNull($saved->published_at);
    }

    public function test_publishing_an_incomplete_draft_lists_the_missing_details(): void
    {
        $event = app(CreateEventService::class)->createSchoolEvent($this->school, $this->author, [
            'status' => 'draft',
            'title' => 'Sports Day',
        ]);

        try {
            app(UpdateDraftEventStatusService::class)->updateDraftSchoolEvent($this->school, $this->author, [
                'school_event_id' => $event->id,
                'status' => 'active',
            ]);
            $this->fail('An incomplete draft must not be published.');
        } catch (AppException $exception) {
            $this->assertSame(422, $exception->getCode());
            $this->assertStringContainsString('event_category_id', $exception->getMessage());
            $this->assertStringNotContainsString('title', $exception->getMessage());
        }

        $this->assertSame('draft', SchoolEvent::find($event->id)->status);
    }

    public function test_publishing_a_complete_draft_without_invitees_is_rejected(): void
    {
        $event = app(CreateEventService::class)->createSchoolEvent($this->school, $this->author, [
            'status' => 'draft',
            'title' => 'Sports Day',
            'description' => 'Races and relays for every house.',
            'organizer' => 'PE Department',
            'location' => 'Main Field',
            'start_date' => '2099-01-01 09:00',
            'end_date' => '2099-01-01 17:00',
            'event_category_id' => self::CATEGORY,
        ]);

        try {
            app(UpdateDraftEventStatusService::class)->updateDraftSchoolEvent($this->school, $this->author, [
                'school_event_id' => $event->id,
                'status' => 'active',
            ]);
            $this->fail('A draft without invitees must not be published.');
        } catch (AppException $exception) {
            $this->assertSame(422, $exception->getCode());
            $this->assertSame('Invitees Required', $exception->getTitle());
        }
    }

    public function test_saving_a_draft_keeps_earlier_details(): void
    {
        $event = app(CreateEventService::class)->createSchoolEvent($this->school, $this->author, [
            'status' => 'draft',
            'title' => 'Sports Day',
        ]);

        app(UpdateDraftEventStatusService::class)->updateDraftSchoolEvent($this->school, $this->author, [
            'school_event_id' => $event->id,
            'status' => 'draft',
            'location' => 'Main Field',
        ]);

        $saved = SchoolEvent::find($event->id);
        $this->assertSame('Sports Day', $saved->title);
        $this->assertSame('Main Field', $saved->location);
        $this->assertSame('draft', $saved->status);
    }
}
