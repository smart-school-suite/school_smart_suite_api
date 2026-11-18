<?php

namespace App\Services\Semester;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Course\SemesterJoinCourseReference;
use App\Models\Exam\Exam;
use App\Models\InstructorAvailability;
use App\Models\Promotion\PromotionRun;
use App\Models\SchoolSemester;
use App\Models\Semester;
use App\Models\SemesterTimetable\SemesterTimetableVersion;
use App\Services\Helpers\DateRules\DateRangeRules;

/**
 * What may change on a school semester, and within which limits. The update
 * endpoints enforce it and the update drawer shows it, so both agree.
 *
 * - A semester that has ended, or whose academic year has ended or is
 *   promoting its students, is read-only.
 * - Its academic year never changes: its timetable, teacher availability and
 *   joint courses were set up for that year's specialty. Delete the semester
 *   and create it in the other academic year instead.
 * - Which semester it is (e.g. First Semester) changes only before it starts
 *   and before exams, a timetable or joint courses are created for it, and
 *   stays unique within the academic year.
 * - Its start date changes only before it starts; its end date until it
 *   ends. Dates never move into the past, stay within the academic year, and
 *   keep clear of the academic year's other semesters.
 */
class SchoolSemesterUpdatePolicy
{
    use DateRangeRules;

    private const PROMOTION_STARTED = ['evaluating', 'processing', 'completed', 'completed_with_issues', 'scheduled'];

    /** The rules for the semester as it is now, for the update drawer. */
    public function describe(SchoolSemester $semester): array
    {
        $semester->loadMissing(['semester', 'schoolYear.systemAcademicYear', 'schoolYear.specialty.level']);
        $year = $semester->schoolYear;
        $context = $this->context($semester);
        $status = $this->status($semester);
        $readOnly = $this->readOnlyReasons($semester, $context, $status);

        $startReasons = $readOnly ?: ($status !== 'upcoming'
            ? ["The semester began on {$this->human($semester->start_date)}. A start date that has passed cannot change."]
            : []);
        $semesterReasons = $readOnly ?: $this->semesterTypeReasons($context, $status);

        return [
            'id' => $semester->id,
            'status' => $status,
            'read_only' => !empty($readOnly),
            'semester' => $semester->semester?->only(['id', 'name', 'count']),
            'academic_year' => [
                'id' => $year?->id,
                'name' => $year?->systemAcademicYear?->name,
                'start_date' => $this->date($year?->start_date),
                'end_date' => $this->date($year?->end_date),
                'specialty' => $year?->specialty?->specialty_name,
                'level' => $year?->specialty?->level?->name,
            ],
            'start_date' => $this->date($semester->start_date),
            'end_date' => $this->date($semester->end_date),
            'fields' => [
                'start_date' => [
                    'editable' => empty($startReasons),
                    'reasons' => $startReasons,
                    ...$this->startBounds($year, $context, $this->date($semester->end_date)),
                ],
                'end_date' => [
                    'editable' => empty($readOnly),
                    'reasons' => $readOnly,
                    ...$this->endBounds($year, $context, $this->date($semester->start_date)),
                ],
                'semester_id' => [
                    'editable' => empty($semesterReasons),
                    'reasons' => $semesterReasons,
                    'options' => empty($semesterReasons) ? $this->semesterOptions($semester) : [],
                ],
                'school_year_id' => [
                    'editable' => false,
                    'reasons' => [$this->academicYearReason($status)],
                ],
            ],
            'dependents' => $context['counts'],
        ];
    }

    /**
     * Checks the requested changes against the rules and returns the values
     * to save (only the fields that actually change). With $allowNoChanges a
     * request that changes nothing returns [] instead of being refused, for
     * bulk updates.
     */
    public function validatedChanges(SchoolSemester $semester, array $data, bool $allowNoChanges = false): array
    {
        $semester->loadMissing('schoolYear');
        $year = $semester->schoolYear;
        $context = $this->context($semester);
        $status = $this->status($semester);

        $changes = collect([
            'start_date' => isset($data['start_date']) ? substr((string) $data['start_date'], 0, 10) : null,
            'end_date' => isset($data['end_date']) ? substr((string) $data['end_date'], 0, 10) : null,
            'semester_id' => $data['semester_id'] ?? null,
            'school_year_id' => $data['school_year_id'] ?? null,
        ])
            ->filter(fn($value) => $value !== null && $value !== '')
            ->reject(fn($value, $field) => $value === (in_array($field, ['start_date', 'end_date'], true)
                ? $this->date($semester->{$field})
                : $semester->{$field}))
            ->all();

        if (empty($changes)) {
            if ($allowNoChanges) {
                return [];
            }
            $this->fail('No Changes', 'Change at least one field before saving.');
        }

        if ($readOnly = $this->readOnlyReasons($semester, $context, $status)) {
            $this->fail('Semester Is Read-Only', $readOnly[0], 409);
        }

        if (isset($changes['school_year_id'])) {
            $this->fail('Academic Year Cannot Change', $this->academicYearReason($status), 409);
        }

        if (isset($changes['semester_id'])) {
            if ($reasons = $this->semesterTypeReasons($context, $status)) {
                $this->fail('Semester Cannot Change', $reasons[0], 409);
            }
            $type = Semester::find($changes['semester_id']);
            if (!$type) {
                $this->fail('Semester Not Found', 'The selected semester does not exist.', 404);
            }
            $taken = SchoolSemester::where('school_year_id', $semester->school_year_id)
                ->where('semester_id', $type->id)
                ->where('id', '!=', $semester->id)
                ->exists();
            if ($taken) {
                $this->fail('Semester Already Exists', "This academic year already has a {$type->name}.", 409);
            }
        }

        if (isset($changes['start_date']) && $status !== 'upcoming') {
            $this->fail('Start Date Cannot Change', "The semester began on {$this->human($semester->start_date)}. A start date that has passed cannot change.", 409);
        }

        $start = $changes['start_date'] ?? $this->date($semester->start_date);
        $end = $changes['end_date'] ?? $this->date($semester->end_date);

        if ($start >= $end) {
            $this->fail('Invalid Date Range', 'The start date must be before the end date.');
        }
        if (isset($changes['start_date'])) {
            $this->assertWithin('start date', $start, $this->startBounds($year, $context, $end));
        }
        if (isset($changes['end_date'])) {
            $this->assertWithin('end date', $end, $this->endBounds($year, $context, $start));
        }

        unset($changes['school_year_id']);

        return $changes;
    }

    private function readOnlyReasons(SchoolSemester $semester, array $context, string $status): array
    {
        $reasons = [];
        if ($status === 'ended') {
            $reasons[] = "This semester ended on {$this->human($semester->end_date)}. Its records are history and cannot change.";
        }
        $yearEnd = $this->date($semester->schoolYear?->end_date);
        if ($yearEnd && $yearEnd < $this->today() && $status !== 'ended') {
            $reasons[] = "Its academic year ended on {$this->human($yearEnd)}, so the semester can no longer change.";
        }
        if ($context['promotion_started']) {
            $reasons[] = 'Students are being or have been promoted out of its academic year, so the semester can no longer change.';
        }
        return $reasons;
    }

    private function semesterTypeReasons(array $context, string $status): array
    {
        $reasons = [];
        if ($status !== 'upcoming') {
            $reasons[] = 'The semester has already started, so it stays the same semester.';
        }
        $counts = $context['counts'];
        $linked = array_filter([
            $counts['exams'] ? $this->plural($counts['exams'], 'exam') : null,
            $counts['timetables'] ? $this->plural($counts['timetables'], 'timetable version') : null,
            $counts['joint_courses'] ? $this->plural($counts['joint_courses'], 'joint course') : null,
        ]);
        if ($linked) {
            $reasons[] = 'It already has ' . implode(', ', $linked) . ' set up for this semester.';
        }
        return $reasons;
    }

    private function academicYearReason(string $status): string
    {
        return $status === 'upcoming'
            ? "Its timetable, teacher availability and joint courses were set up for this academic year's specialty. To use another academic year, delete this semester and create it there."
            : "Its timetable, teacher availability and joint courses were set up for this academic year's specialty, so it never changes.";
    }

    private function startBounds(?SchoolAcademicYear $year, array $context, string $end): array
    {
        $min = [['date' => $this->today(), 'why' => 'it cannot be in the past']];
        $max = [['date' => $this->shift($end, -1), 'why' => 'it must be before the end date', 'kind' => 'pair']];

        if ($yearStart = $this->date($year?->start_date)) {
            $min[] = ['date' => $yearStart, 'why' => 'the academic year starts on ' . $this->human($yearStart)];
        }
        if ($context['previous_end']) {
            $min[] = ['date' => $this->shift($context['previous_end'], 1), 'why' => "the {$context['previous_name']} ends on {$this->human($context['previous_end'])}"];
        }

        return $this->bounds($min, $max);
    }

    private function endBounds(?SchoolAcademicYear $year, array $context, string $start): array
    {
        $min = [
            ['date' => $this->today(), 'why' => 'it cannot be in the past'],
            ['date' => $this->shift($start, 1), 'why' => 'it must be after the start date', 'kind' => 'pair'],
        ];
        $max = [];

        if ($yearEnd = $this->date($year?->end_date)) {
            $max[] = ['date' => $yearEnd, 'why' => 'the academic year ends on ' . $this->human($yearEnd)];
        }
        if ($context['next_start']) {
            $max[] = ['date' => $this->shift($context['next_start'], -1), 'why' => "the {$context['next_name']} starts on {$this->human($context['next_start'])}"];
        }

        return $this->bounds($min, $max);
    }

    /** What depends on the semester, and the academic year's other semesters. */
    private function context(SchoolSemester $semester): array
    {
        $start = $this->date($semester->start_date);
        $siblings = SchoolSemester::where('school_year_id', $semester->school_year_id)
            ->where('id', '!=', $semester->id)
            ->with('semester:id,name')
            ->get(['id', 'start_date', 'end_date', 'semester_id']);
        $previous = $siblings->filter(fn($s) => $this->date($s->start_date) < $start)->sortByDesc(fn($s) => $this->date($s->end_date))->first();
        $next = $siblings->filter(fn($s) => $this->date($s->start_date) > $start)->sortBy(fn($s) => $this->date($s->start_date))->first();

        return [
            'counts' => [
                // An exam belongs to a semester through its exam type.
                'exams' => Exam::where('school_year_id', $semester->school_year_id)
                    ->whereHas('examType', fn($query) => $query->where('semester_id', $semester->semester_id))
                    ->count(),
                'timetables' => SemesterTimetableVersion::where('school_semester_id', $semester->id)->count(),
                'timetable_published' => (bool) $semester->timetable_published,
                'joint_courses' => SemesterJoinCourseReference::where('school_semester_id', $semester->id)->count(),
                'teacher_availabilities' => InstructorAvailability::where('school_semester_id', $semester->id)->count(),
            ],
            'previous_end' => $previous ? $this->date($previous->end_date) : null,
            'previous_name' => $previous?->semester?->name ?? 'previous semester',
            'next_start' => $next ? $this->date($next->start_date) : null,
            'next_name' => $next?->semester?->name ?? 'next semester',
            'promotion_started' => PromotionRun::where('school_year_id', $semester->school_year_id)->whereIn('status', self::PROMOTION_STARTED)->exists(),
        ];
    }

    private function semesterOptions(SchoolSemester $semester): array
    {
        $taken = SchoolSemester::where('school_year_id', $semester->school_year_id)
            ->where('id', '!=', $semester->id)
            ->pluck('semester_id');

        return Semester::whereNotIn('id', $taken)
            ->orderBy('count')
            ->get(['id', 'name', 'count'])
            ->toArray();
    }

    private function status(SchoolSemester $semester): string
    {
        $today = $this->today();
        return match (true) {
            $this->date($semester->end_date) < $today => 'ended',
            $this->date($semester->start_date) <= $today => 'ongoing',
            default => 'upcoming',
        };
    }
}
