<?php

namespace App\Services\Exam;

use App\Exceptions\AppException;
use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Exam\Exam;
use App\Models\Examtype;
use App\Models\SchoolSemester;
use App\Models\Student;
use App\Services\Helpers\DateRules\DateRangeRules;
use Illuminate\Support\Collection;

/**
 * Where an exam can be created, and within which limits. The create drawer
 * lists the options and the create endpoint enforces them, so both agree.
 *
 * - An exam is created for a school semester. Its academic year is the
 *   semester's, and its exam type is one of that semester's (its CA or its
 *   Exam). An academic year without semesters can't get exams: create its
 *   semesters first.
 * - Each exam type is created once per academic year. A semester's Exam
 *   needs its CA first, because the Exam's max score includes the CA's.
 * - The specialty needs students, since the exam's candidates are its students.
 * - Dates fall within the semester and not in the past, so a semester that
 *   has ended takes no new exams.
 * - Recommended: per academic year, the first semester that can still take
 *   an exam, and in it the next exam type (the CA, then the Exam).
 *
 * Whatever can't be used carries an `issue`: what was found, in plain words,
 * the next step, and an `action` code the client turns into a button
 * (add_students, manage_semesters, view_exams, create_ca, create_academic_year).
 * `summary` sums up what was found across the school.
 */
class ExamCreationPolicy
{
    use DateRangeRules;

    private const TYPES = ['ca', 'exam'];

    /** Every school semester of the branch, described for the create drawer. */
    public function options(object $currentSchool): array
    {
        $semesters = SchoolSemester::where('school_branch_id', $currentSchool->id)
            ->with(['semester:id,name,count', 'schoolYear.systemAcademicYear', 'schoolYear.specialty.level'])
            ->get();

        $context = $this->context($currentSchool->id, $semesters);
        $described = $semesters
            ->map(fn(SchoolSemester $semester) => $this->describe($semester, $context))
            ->all();
        $described = $this->recommend($described);

        $yearsWithSemesters = $semesters->pluck('school_year_id')->unique();
        $yearsWithout = SchoolAcademicYear::where('school_branch_id', $currentSchool->id)
            ->whereNotIn('id', $yearsWithSemesters)
            ->whereDate('end_date', '>=', $this->today())
            ->with(['systemAcademicYear', 'specialty.level'])
            ->get()
            ->map(fn($year) => $this->academicYear($year))
            ->values()
            ->all();

        return [
            'summary' => $this->summary($described, $yearsWithout, $currentSchool->id),
            'semesters' => $described,
            // Still-running academic years that need semesters before they can take exams.
            'academic_years_without_semesters' => $yearsWithout,
        ];
    }

    /**
     * Checks a create request against the rules and returns what the exam is
     * created with: [school_year_id, semester, exam_type, ca (for an Exam)].
     */
    public function validated(object $currentSchool, array $data): array
    {
        $semester = SchoolSemester::where('school_branch_id', $currentSchool->id)
            ->with(['semester:id,name,count', 'schoolYear.systemAcademicYear', 'schoolYear.specialty.level'])
            ->find($data['school_semester_id'] ?? null);

        if (!$semester) {
            throw new AppException(
                'The selected school semester was not found.',
                404,
                'Semester Not Found',
                'The school semester could not be found in your school. It may have been deleted; pick another semester.'
            );
        }

        $described = $this->describe($semester, $this->context($currentSchool->id, collect([$semester])));
        $semesterName = $described['name'];

        if (!$described['available']) {
            $issue = $described['issue'];
            throw new AppException(
                'Exams cannot be created for this semester.',
                422,
                $issue['title'],
                "{$issue['message']} {$issue['next_step']}"
            );
        }

        $type = collect($described['exam_types'])->firstWhere('id', $data['exam_type_id'] ?? null);
        if (!$type) {
            throw new AppException(
                'The exam type does not belong to the selected semester.',
                422,
                'Exam Type Not In This Semester',
                "That exam type belongs to another semester. Pick one of the {$semesterName}'s exam types: " . collect($described['exam_types'])->pluck('name')->implode(', ') . '.'
            );
        }

        if (!$type['available']) {
            $issue = $type['issue'];
            throw new AppException(
                $type['created'] ? 'This exam already exists.' : 'This exam cannot be created yet.',
                $type['created'] ? 409 : 422,
                $issue['title'],
                "{$issue['message']} {$issue['next_step']}"
            );
        }

        foreach (['start_date', 'end_date'] as $field) {
            $bounds = $field === 'start_date'
                ? $this->startBounds($described['window'], $data['end_date'] ?? null)
                : $this->endBounds($described['window'], $data['start_date'] ?? null);
            $value = $this->date($data[$field] ?? null);
            $label = $field === 'start_date' ? 'start date' : 'end date';

            if ($bounds['min'] && $value < $bounds['min']) {
                $this->dateRefused($label, "It must be on or after {$this->human($bounds['min'])}: {$bounds['min_reason']}.");
            }
            if ($bounds['max'] && $value > $bounds['max']) {
                $this->dateRefused($label, "It must be on or before {$this->human($bounds['max'])}: {$bounds['max_reason']}.");
            }
        }

        $ca = $type['type'] === 'exam'
            ? Exam::where('school_branch_id', $currentSchool->id)->find($type['requires_exam_id'])
            : null;

        return [
            'school_year_id' => $semester->school_year_id,
            'semester' => $semester,
            'exam_type_id' => $type['id'],
            'ca' => $ca,
        ];
    }

    /** One semester with its exam types, what exists and what can be created. */
    private function describe(SchoolSemester $semester, array $context): array
    {
        $year = $semester->schoolYear;
        $name = $semester->semester?->name ?? 'semester';
        $window = [
            'label' => "the {$name}",
            'source' => 'semester',
            'start_date' => $this->date($semester->start_date),
            'end_date' => $this->date($semester->end_date),
        ];
        $status = $this->status($window);

        $types = $context['types']->get($semester->semester_id, collect())
            ->sortBy(fn($type) => array_search(strtolower($type->type), self::TYPES, true))
            ->values();

        $specialty = $year?->specialty?->specialty_name;
        $where = $this->where($year);
        $issues = [];
        if (!$year || !$year->specialty) {
            $issues[] = $this->issue(
                'missing_academic_year',
                'Academic year not found',
                "We found the {$name}, but the academic year or specialty it belongs to has been deleted, so there is no one to take an exam.",
                'Delete this semester, or create the academic year again and add its semesters.',
                'manage_semesters'
            );
        } elseif (!in_array($year->specialty_id, $context['specialties_with_students'], true)) {
            $issues[] = $this->issue(
                'no_students',
                "No students in {$specialty} yet",
                "We found the {$name} of {$where}, but no students are enrolled in {$specialty}. An exam is taken by the specialty's students, so nobody would sit it.",
                "Add or import students into {$specialty}, then come back to create its exams.",
                'add_students',
                ['specialty_id' => $year->specialty_id, 'specialty' => $specialty]
            );
        }
        if ($status === 'ended') {
            $issues[] = $this->issue(
                'semester_ended',
                "The {$name} has ended",
                "The {$name} of {$where} ended on {$this->human($window['end_date'])}, and a new exam can't be dated in the past.",
                'Pick an ongoing or upcoming semester. If the next semester is missing, create it first.',
                'manage_semesters'
            );
        }
        if ($types->isEmpty()) {
            $issues[] = $this->issue(
                'no_exam_types',
                "No exams are set up for the {$name}",
                "The {$name} has no CA or exam type, so there is nothing to create for it.",
                'Exam types are shared by every school. Contact support to have them added.',
                null
            );
        }
        $semesterIssue = $issues[0] ?? null;

        $examTypes = [];
        $ca = null;
        foreach ($types as $type) {
            $kind = strtolower($type->type);
            $exam = $context['exams']->get($semester->school_year_id . '|' . $type->id);
            $typeIssue = null;
            $requires = null;

            if ($exam) {
                $typeIssue = $this->issue(
                    'already_created',
                    "{$type->exam_name} already exists",
                    "{$type->exam_name} was already created for {$where}: {$this->human($exam->start_date)} – {$this->human($exam->end_date)}, out of " . (float) $exam->max_score . '. Each exam is created once per academic year.',
                    'Open it from the exams list to change its dates or score.',
                    'view_exams',
                    ['exam_id' => $exam->id]
                );
            } elseif ($kind === 'exam') {
                if (!$ca) {
                    $caType = $types->first(fn($t) => strtolower($t->type) === 'ca');
                    $caName = $caType?->exam_name ?? 'the CA';
                    $typeIssue = $this->issue(
                        'ca_missing',
                        "{$caName} comes first",
                        "The {$type->exam_name}'s max score includes the CA's, and {$caName} hasn't been created for {$where} yet.",
                        "Create {$caName} first, then come back for the {$type->exam_name}.",
                        $caType ? 'create_ca' : null,
                        ['exam_type_id' => $caType?->id]
                    );
                } else {
                    $requires = $ca;
                }
            }

            if ($kind === 'ca') {
                $ca = $exam;
            }

            $examTypes[] = [
                'id' => $type->id,
                'name' => $type->exam_name,
                'type' => $kind,
                'created' => (bool) $exam,
                'exam' => $exam ? [
                    'id' => $exam->id,
                    'start_date' => $this->date($exam->start_date),
                    'end_date' => $this->date($exam->end_date),
                    'max_score' => (float) $exam->max_score,
                ] : null,
                'available' => !$semesterIssue && !$typeIssue,
                // Why it can't be created (its own reason first, else the semester's).
                'issue' => $typeIssue ?? $semesterIssue,
                'reasons' => array_values(array_filter([($typeIssue ?? $semesterIssue)['message'] ?? null])),
                // An Exam's max score adds the CA's to the exam's own part.
                'requires_exam_id' => $requires?->id,
                'ca_max_score' => $requires ? (float) $requires->max_score : null,
                'recommended' => false,
            ];
        }

        $created = collect($examTypes)->where('created', true);
        $usage = $created->isEmpty() ? 'unused' : ($created->count() === count($examTypes) ? 'complete' : 'partial');
        $next = collect($examTypes)->firstWhere('available', true);
        if (!$semesterIssue && !$next) {
            $semesterIssue = $this->issue(
                'all_created',
                "Every exam of the {$name} exists",
                'Its ' . $created->pluck('name')->implode(' and ') . " were already created for {$where}.",
                'Open them from the exams list to change dates or scores, or pick another semester.',
                'view_exams'
            );
            $issues[] = $semesterIssue;
        }

        return [
            'id' => $semester->id,
            'name' => $name,
            'order' => (int) ($semester->semester?->count ?? 0),
            'status' => $status,
            'start_date' => $window['start_date'],
            'end_date' => $window['end_date'],
            'window' => $window,
            'academic_year' => $this->academicYear($year),
            'usage' => $usage,
            'available' => !$semesterIssue,
            'issue' => $semesterIssue,
            'issues' => $issues,
            'reasons' => array_column($issues, 'message'),
            'next_exam_type_id' => $next['id'] ?? null,
            'used' => $created->pluck('name')->values()->all(),
            'missing' => collect($examTypes)->where('created', false)->pluck('name')->values()->all(),
            'recommended' => false,
            'recommendation' => null,
            'exam_types' => $examTypes,
            'fields' => [
                'start_date' => $this->startBounds($window, null),
                'end_date' => $this->endBounds($window, null),
            ],
        ];
    }

    /**
     * Per academic year, the first semester that can still take an exam is
     * recommended, with its next exam type. Recommended semesters come first.
     */
    private function recommend(array $semesters): array
    {
        $byYear = collect($semesters)->groupBy(fn($semester) => $semester['academic_year']['id']);

        foreach ($byYear as $yearSemesters) {
            $first = $yearSemesters
                ->where('available', true)
                ->sortBy([['order', 'asc'], ['start_date', 'asc']])
                ->first();
            if (!$first) {
                continue;
            }

            foreach ($semesters as &$semester) {
                if ($semester['id'] !== $first['id']) {
                    continue;
                }
                $semester['recommended'] = true;
                $next = collect($semester['exam_types'])->firstWhere('id', $semester['next_exam_type_id']);
                $semester['recommendation'] = $semester['usage'] === 'unused'
                    ? "No exam has been created for the {$semester['name']} yet. Start with its {$next['name']}."
                    : implode(', ', $semester['used']) . " already exists; {$next['name']} is next.";
                foreach ($semester['exam_types'] as &$type) {
                    $type['recommended'] = $type['id'] === $semester['next_exam_type_id'];
                }
                unset($type);
            }
            unset($semester);
        }

        $rank = fn($semester) => $semester['recommended'] ? 0 : ($semester['available'] ? 1 : 2);
        usort($semesters, fn($a, $b) => [$rank($a), $a['start_date'], $a['order']] <=> [$rank($b), $b['start_date'], $b['order']]);

        return $semesters;
    }

    /** Exam types by semester, the branch's exams by "year|type", specialties with students. */
    private function context(string $schoolBranchId, Collection $semesters): array
    {
        $yearIds = $semesters->pluck('school_year_id')->unique()->values();
        $specialtyIds = $semesters->map(fn($semester) => $semester->schoolYear?->specialty_id)->filter()->unique()->values();

        return [
            'types' => Examtype::whereIn('semester_id', $semesters->pluck('semester_id')->unique())
                ->whereIn('type', self::TYPES)
                ->get()
                ->groupBy('semester_id'),
            'exams' => Exam::where('school_branch_id', $schoolBranchId)
                ->whereIn('school_year_id', $yearIds)
                ->get(['id', 'exam_type_id', 'school_year_id', 'start_date', 'end_date', 'max_score'])
                ->keyBy(fn($exam) => $exam->school_year_id . '|' . $exam->exam_type_id),
            'specialties_with_students' => Student::where('school_branch_id', $schoolBranchId)
                ->whereIn('specialty_id', $specialtyIds)
                ->distinct()
                ->pluck('specialty_id')
                ->all(),
        ];
    }

    private function startBounds(array $window, ?string $end): array
    {
        $min = [['date' => $this->today(), 'why' => 'it cannot be in the past']];
        $max = [];
        if ($window['start_date']) {
            $min[] = ['date' => $window['start_date'], 'why' => "{$window['label']} starts on {$this->human($window['start_date'])}", 'kind' => 'window'];
        }
        if ($window['end_date']) {
            $max[] = ['date' => $window['end_date'], 'why' => "{$window['label']} ends on {$this->human($window['end_date'])}", 'kind' => 'window'];
        }
        if ($end = $this->date($end)) {
            $max[] = ['date' => $end, 'why' => 'it must be on or before the end date', 'kind' => 'pair'];
        }

        return $this->bounds($min, $max);
    }

    private function endBounds(array $window, ?string $start): array
    {
        $min = [['date' => $this->today(), 'why' => 'it cannot be in the past']];
        $max = [];
        if ($window['start_date']) {
            $min[] = ['date' => $window['start_date'], 'why' => "{$window['label']} starts on {$this->human($window['start_date'])}", 'kind' => 'window'];
        }
        if ($window['end_date']) {
            $max[] = ['date' => $window['end_date'], 'why' => "{$window['label']} ends on {$this->human($window['end_date'])}", 'kind' => 'window'];
        }
        if ($start = $this->date($start)) {
            $min[] = ['date' => $start, 'why' => 'it must be on or after the start date', 'kind' => 'pair'];
        }

        return $this->bounds($min, $max);
    }

    /**
     * What was found across the school, for the top of the drawer:
     * a headline and one finding per kind of problem, with its next step.
     */
    private function summary(array $semesters, array $yearsWithout, string $schoolBranchId): array
    {
        $total = count($semesters);
        $usable = count(array_filter($semesters, fn($semester) => $semester['available']));
        $findings = [];

        $blocked = collect($semesters)->reject(fn($semester) => $semester['available'])->groupBy(fn($semester) => $semester['issue']['code']);
        $copy = [
            'no_students' => fn($n, $list) => [
                ($n === 1 ? '1 semester is' : "{$n} semesters are") . ' waiting for students: ' . $this->names($list->pluck('issue.data.specialty')->unique()) . (
                    $list->pluck('issue.data.specialty')->unique()->count() === 1 ? ' has' : ' have'
                ) . ' no students enrolled yet.',
                'Add or import students into those specialties to create their exams.',
                'add_students',
            ],
            'semester_ended' => fn($n) => [
                ($n === 1 ? '1 semester has' : "{$n} semesters have") . ' already ended, so they can\'t take a new exam.',
                'Create the next semester if it is missing.',
                'manage_semesters',
            ],
            'all_created' => fn($n) => [
                ($n === 1 ? '1 semester already has' : "{$n} semesters already have") . ' all of their exams.',
                'Manage those from the exams list.',
                'view_exams',
            ],
            'missing_academic_year' => fn($n) => [
                ($n === 1 ? '1 semester belongs' : "{$n} semesters belong") . ' to an academic year or specialty that was deleted.',
                'Delete those semesters or recreate their academic year.',
                'manage_semesters',
            ],
            'no_exam_types' => fn($n) => [
                ($n === 1 ? '1 semester has' : "{$n} semesters have") . ' no exam types set up.',
                'Contact support to have them added.',
                null,
            ],
        ];
        foreach ($copy as $code => $describe) {
            if (!$blocked->has($code)) {
                continue;
            }
            [$text, $next, $action] = $describe($blocked[$code]->count(), $blocked[$code]);
            $findings[] = ['code' => $code, 'count' => $blocked[$code]->count(), 'text' => $text, 'next_step' => $next, 'action' => $action];
        }

        if ($yearsWithout) {
            $n = count($yearsWithout);
            $findings[] = [
                'code' => 'no_semesters',
                'count' => $n,
                'text' => ($n === 1 ? '1 academic year has' : "{$n} academic years have") . ' no semesters yet: '
                    . $this->names(collect($yearsWithout)->map(fn($year) => trim("{$year['specialty']} · {$year['level']} ({$year['name']})"))) . '.',
                'next_step' => 'Exams are created for a semester, so create their semesters first.',
                'action' => 'manage_semesters',
            ];
        }

        if ($total === 0 && !$yearsWithout) {
            $running = SchoolAcademicYear::where('school_branch_id', $schoolBranchId)->whereDate('end_date', '>=', $this->today())->exists();
            $findings[] = $running
                ? ['code' => 'no_semesters', 'count' => 0, 'text' => 'No school semesters were found.', 'next_step' => 'Create the semesters of your academic years first.', 'action' => 'manage_semesters']
                : ['code' => 'no_academic_years', 'count' => 0, 'text' => 'No academic year is running.', 'next_step' => 'Create an academic year and its semesters first.', 'action' => 'create_academic_year'];
        }

        $headline = match (true) {
            $total === 0 => "We didn't find any semester to create an exam for.",
            $usable === 0 => 'We found ' . $this->count($total, 'semester') . ", but none can take a new exam right now.",
            $usable === $total => 'We found ' . $this->count($total, 'semester') . ($total === 1 ? ', and it can take a new exam.' : ', and all of them can take a new exam.'),
            default => 'We found ' . $this->count($total, 'semester') . ": {$usable} can take a new exam.",
        };

        return ['semesters' => $total, 'available' => $usable, 'headline' => $headline, 'findings' => $findings];
    }

    private function issue(string $code, string $title, string $message, string $nextStep, ?string $action, array $data = []): array
    {
        return ['code' => $code, 'title' => $title, 'message' => $message, 'next_step' => $nextStep, 'action' => $action, 'data' => $data];
    }

    /** "Software Engineering · Level 100 (2026/2027)". */
    private function where(?SchoolAcademicYear $year): string
    {
        $parts = array_filter([$year?->specialty?->specialty_name, $year?->specialty?->level?->name]);
        $label = implode(' · ', $parts) ?: 'this specialty';
        $name = $year?->systemAcademicYear?->name;
        return $name ? "{$label} ({$name})" : $label;
    }

    /** "Nursing", "Nursing and Accounting", "Nursing, Accounting and 2 more". */
    private function names(Collection $names): string
    {
        $names = $names->filter()->values();
        if ($names->count() <= 2) {
            return $names->implode(' and ');
        }
        return $names->take(2)->implode(', ') . ' and ' . ($names->count() - 2) . ' more';
    }

    private function count(int $n, string $word): string
    {
        return "{$n} {$word}" . ($n === 1 ? '' : 's');
    }

    private function status(array $window): string
    {
        $today = $this->today();
        if ($window['end_date'] && $window['end_date'] < $today) {
            return 'ended';
        }
        return $window['start_date'] && $window['start_date'] > $today ? 'upcoming' : 'ongoing';
    }

    private function academicYear(?SchoolAcademicYear $year): array
    {
        return [
            'id' => $year?->id,
            'name' => $year?->systemAcademicYear?->name,
            'start_date' => $this->date($year?->start_date),
            'end_date' => $this->date($year?->end_date),
            'specialty' => $year?->specialty?->specialty_name,
            'level' => $year?->specialty?->level?->name,
        ];
    }

    private function dateRefused(string $label, string $why): never
    {
        throw new AppException(
            "The {$label} is not allowed.",
            422,
            ucfirst($label) . ' Not Allowed',
            $why
        );
    }
}
