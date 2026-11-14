<?php

namespace App\Services\ResitTimetable\Support;

use App\Models\AccessedStudent;
use App\Models\ExamTimetable\ExamSessionInvigilator;
use App\Models\ExamTimetable\ExamTimetableSlot;
use App\Models\ResitExam;
use App\Models\Resitexamtimetable;
use App\Models\ResitTimetable\ResitSessionInvigilator;
use App\Schedular\ExamTimetable\Placement\Support\Time;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Sessions of the active exam and resit timetables between two dates, in one
 * shape, so each kind of timetable sees what the other commits:
 *
 *  {kind, owner_id, date, start_time, end_time, start, end, course_id, course_title,
 *   halls: hall_id => candidates, people: ["Type:id"], student_ids?: [], source}
 *
 * People are "actorable_type:actorable_id": invigilator rows are per exam or
 * resit exam, so the same teacher is matched through the person.
 */
class ActiveExamSessions
{
    public const KIND_EXAM = 'exam';
    public const KIND_RESIT = 'resit';

    public function __construct(private ResitCandidates $resitCandidates) {}

    public function exams(string $schoolBranchId, string $startDate, string $endDate, ?string $exceptExamId = null, bool $withStudents = false): Collection
    {
        $slots = ExamTimetableSlot::where('school_branch_id', $schoolBranchId)
            ->when($exceptExamId, fn($query) => $query->where('exam_id', '!=', $exceptExamId))
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->whereHas('version')
            ->with(['course', 'examSessionHall', 'examSessionInvig.invigilator'])
            ->get();

        $students = $withStudents
            ? AccessedStudent::where('school_branch_id', $schoolBranchId)
                ->whereIn('exam_id', $slots->pluck('exam_id')->unique())
                ->get(['exam_id', 'student_id'])
                ->groupBy('exam_id')
                ->map(fn(Collection $rows) => $rows->pluck('student_id')->map(fn($id) => (string) $id)->unique()->values()->all())
            : collect();

        return $slots->map(fn(ExamTimetableSlot $slot) => $this->session(
            self::KIND_EXAM,
            $slot->exam_id,
            $slot,
            $slot->course?->course_title,
            $slot->examSessionHall,
            $slot->examSessionInvig->map(fn(ExamSessionInvigilator $row) => $row->invigilator),
            $withStudents ? ($students[$slot->exam_id] ?? []) : null,
            "another exam's active timetable",
        ))->filter()->values();
    }

    public function resits(string $schoolBranchId, string $startDate, string $endDate, ?string $exceptResitExamId = null, bool $withStudents = false): Collection
    {
        $slots = Resitexamtimetable::where('school_branch_id', $schoolBranchId)
            ->whereNotNull('version_id')
            ->when($exceptResitExamId, fn($query) => $query->where('resit_exam_id', '!=', $exceptResitExamId))
            ->whereDate('date', '>=', $startDate)
            ->whereDate('date', '<=', $endDate)
            ->with(['course', 'sessionHalls', 'sessionInvigilators.invigilator'])
            ->get();

        $candidates = [];
        if ($withStudents) {
            ResitExam::whereIn('id', $slots->pluck('resit_exam_id')->unique())
                ->get()
                ->each(function (ResitExam $resitExam) use ($schoolBranchId, &$candidates) {
                    $candidates[$resitExam->id] = $this->resitCandidates->byCourse($schoolBranchId, $resitExam);
                });
        }

        return $slots->map(fn(Resitexamtimetable $slot) => $this->session(
            self::KIND_RESIT,
            $slot->resit_exam_id,
            $slot,
            $slot->course?->course_title,
            $slot->sessionHalls,
            $slot->sessionInvigilators->map(fn(ResitSessionInvigilator $row) => $row->invigilator),
            $withStudents ? ($candidates[$slot->resit_exam_id][$slot->course_id] ?? []) : null,
            "another resit timetable",
        ))->filter()->values();
    }

    /** Both kinds together, except one exam or resit exam. */
    public function all(string $schoolBranchId, string $startDate, string $endDate, ?string $exceptExamId = null, ?string $exceptResitExamId = null, bool $withStudents = false): Collection
    {
        return $this->exams($schoolBranchId, $startDate, $endDate, $exceptExamId, $withStudents)
            ->concat($this->resits($schoolBranchId, $startDate, $endDate, $exceptResitExamId, $withStudents))
            ->values();
    }

    public static function personKey(?string $type, ?string $id): string
    {
        return "{$type}:{$id}";
    }

    private function session(string $kind, string $ownerId, object $slot, ?string $courseTitle, Collection $halls, Collection $invigilators, ?array $students, string $source): ?array
    {
        $start = Time::normalize((string) $slot->getRawOriginal('start_time'));
        $end = Time::normalize((string) $slot->getRawOriginal('end_time'));

        if (!$start || !$end) {
            return null;
        }

        return array_filter([
            'kind' => $kind,
            'owner_id' => $ownerId,
            'date' => Carbon::parse($slot->getRawOriginal('date') ?? $slot->date)->format('Y-m-d'),
            'start_time' => $start,
            'end_time' => $end,
            'start' => Time::toMinutes($start),
            'end' => Time::toMinutes($end),
            'course_id' => $slot->course_id,
            'course_title' => $courseTitle,
            'halls' => $halls->groupBy('hall_id')->map(fn(Collection $rows) => (int) $rows->sum('candidate_count'))->all(),
            'people' => $invigilators->filter()->map(fn($person) => self::personKey($person->actorable_type, $person->actorable_id))->unique()->values()->all(),
            'student_ids' => $students,
            'source' => $source,
        ], fn($value) => $value !== null);
    }
}
