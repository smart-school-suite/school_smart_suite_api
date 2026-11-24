<?php

namespace App\Services\Semester;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\Semester;
use App\Services\AutoCreation\AutoCreationSettings;
use App\Services\AutoCreation\AutoCreationTargets;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SchoolSemesterCreationOptions
{
    private const STATUS_ORDER = ['ongoing' => 0, 'upcoming' => 1, 'ended' => 2];

    public function __construct(private AutoCreationTargets $targets, private SemesterLimit $limit) {}

    public function describe(object $currentSchool, ?Carbon $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $settings = SemesterAutoCreationSettings::forBranch($currentSchool->id);
        $limit = $this->limit->forBranch($currentSchool->id);
        $allSemesters = Semester::query()->orderBy('count')->get(['id', 'name', 'count']);
        $semesters = $allSemesters->filter(fn (Semester $semester) => (int) $semester->count <= $limit)->values();

        $years = SchoolAcademicYear::query()
            ->where('school_branch_id', $currentSchool->id)
            ->with(['specialty.level', 'systemAcademicYear', 'schoolSemester'])
            ->get();

        $schoolYears = $years
            ->map(fn (SchoolAcademicYear $year) => $this->describeYear($year, $semesters, $allSemesters->pluck('count', 'id'), $years, $settings, $today, $limit))
            ->sort(fn (array $a, array $b) => [self::STATUS_ORDER[$a['status']], $a['label']] <=> [self::STATUS_ORDER[$b['status']], $b['label']])
            ->values()
            ->all();

        return [
            'today' => $today->toDateString(),
            'max_semesters' => $limit,
            'auto_creation' => ['enabled' => $settings->enabled()],
            'school_years' => $schoolYears,
        ];
    }

    private function describeYear(
        SchoolAcademicYear $year,
        Collection $semesters,
        Collection $countOf,
        Collection $allYears,
        AutoCreationSettings $settings,
        Carbon $today,
        int $limit
    ): array {
        $start = Carbon::parse($year->start_date)->startOfDay();
        $end = Carbon::parse($year->end_date)->startOfDay();
        $existing = $year->schoolSemester->keyBy('semester_id');

        $existingByCount = $existing
            ->map(fn ($semester) => [
                'count' => (int) ($countOf[$semester->semester_id] ?? 0),
                'start' => Carbon::parse($semester->start_date)->startOfDay(),
                'end' => Carbon::parse($semester->end_date)->startOfDay(),
            ])
            ->sortBy('count')
            ->values();

        $plannedTotal = $limit;

        $semesterOptions = $semesters->map(function (Semester $semester) use ($existing, $existingByCount, $start, $end, $plannedTotal) {
            $created = $existing->get($semester->id);

            return [
                'id' => $semester->id,
                'name' => $semester->name,
                'count' => (int) $semester->count,
                'created' => (bool) $created,
                'existing' => $created ? [
                    'id' => $created->id,
                    'start_date' => Carbon::parse($created->start_date)->toDateString(),
                    'end_date' => Carbon::parse($created->end_date)->toDateString(),
                ] : null,
                'suggested' => $created ? null : $this->suggest((int) $semester->count, $existingByCount, $start, $end, $plannedTotal),
            ];
        })->values();

        $recommended = $semesterOptions->first(fn (array $option) => !$option['created'] && $option['suggested']);

        return [
            'id' => $year->id,
            'label' => $this->label($year),
            'specialty_name' => $year->specialty?->specialty_name,
            'level_name' => $year->specialty?->level?->name,
            'academic_year' => $year->systemAcademicYear?->name,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'status' => $end->lt($today) ? 'ended' : ($start->gt($today) ? 'upcoming' : 'ongoing'),
            'semesters' => $semesterOptions->all(),
            'recommended_semester_id' => $recommended['id'] ?? null,
            'copy_targets' => $this->copyTargets($year, $allYears, $settings),
        ];
    }

    private function suggest(int $count, Collection $existing, Carbon $yearStart, Carbon $yearEnd, int $plannedTotal): ?array
    {
        $previous = $existing->filter(fn ($semester) => $semester['count'] < $count)->last();
        $next = $existing->first(fn ($semester) => $semester['count'] > $count);
        $total = max($plannedTotal, $count);

        if ($previous) {
            $start = $previous['end']->copy()->addDay();
        } else {
            $slotDays = intdiv($yearStart->diffInDays($yearEnd) + 1, $total);
            $start = $yearStart->copy()->addDays($slotDays * ($count - 1));
        }

        if ($next) {
            $end = $next['start']->copy()->subDay();
        } else {
            $remainingSlots = max(1, $total - $count + 1);
            $end = $start->copy()->addDays(intdiv($start->diffInDays($yearEnd) + 1, $remainingSlots) - 1);
        }

        if ($end->gt($yearEnd)) {
            $end = $yearEnd->copy();
        }

        $overlaps = $existing->contains(fn ($semester) => $start->lte($semester['end']) && $end->gte($semester['start']));

        if ($start->lt($yearStart) || $end->lte($start) || $overlaps) {
            return null;
        }

        return ['start_date' => $start->toDateString(), 'end_date' => $end->toDateString()];
    }

    private function copyTargets(SchoolAcademicYear $year, Collection $allYears, AutoCreationSettings $settings): array
    {
        if (!$year->specialty || !$settings->enabled()) {
            return [];
        }

        return $this->targets->for($year->specialty, $settings)
            ->map(fn ($specialty) => $allYears->first(
                fn (SchoolAcademicYear $candidate) => $candidate->specialty_id === $specialty->id
                    && $candidate->system_academic_year_id === $year->system_academic_year_id
            ))
            ->filter()
            ->map(fn (SchoolAcademicYear $target) => [
                'school_year_id' => $target->id,
                'label' => $this->label($target),
                'start_date' => Carbon::parse($target->start_date)->toDateString(),
                'end_date' => Carbon::parse($target->end_date)->toDateString(),
                'existing_semester_ids' => $target->schoolSemester->pluck('semester_id')->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function label(SchoolAcademicYear $year): string
    {
        return collect([$year->specialty?->specialty_name, $year->specialty?->level?->name])
            ->filter()
            ->implode(' · ') ?: 'School year';
    }
}
