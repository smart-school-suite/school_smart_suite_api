<?php

namespace App\Services\AcademicYear;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Specialty;
use App\Services\AutoCreation\AutoCreationSettings;
use App\Services\AutoCreation\AutoCreationTargets;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class SchoolAcademicYearCreationOptions
{
    private const SWITCH_MONTH = 8;

    public function __construct(private AutoCreationTargets $targets) {}

    public function describe(object $currentSchool, ?Carbon $today = null): array
    {
        $today = ($today ?? now())->copy()->startOfDay();
        $currentStart = $today->month >= self::SWITCH_MONTH ? $today->year : $today->year - 1;
        $settings = AutoCreationSettings::forBranch($currentSchool->id, AutoCreationSettings::ACADEMIC_YEAR);

        $offered = SystemAcademicYear::query()
            ->whereIn('year_start', [$currentStart, $currentStart + 1])
            ->orderBy('year_start')
            ->get();

        $schoolYears = SchoolAcademicYear::query()
            ->where('school_branch_id', $currentSchool->id)
            ->with('systemAcademicYear')
            ->get();

        $specialties = Specialty::query()
            ->where('school_branch_id', $currentSchool->id)
            ->with(['level', 'department'])
            ->withCount('student')
            ->get()
            ->sortBy(fn (Specialty $specialty) => [$specialty->specialty_name, (int) $specialty->level?->level])
            ->values();

        $years = $offered->map(fn (SystemAcademicYear $year) => [
            'id' => $year->id,
            'name' => $year->name,
            'year_start' => (int) $year->year_start,
            'year_end' => (int) $year->year_end,
            'current' => (int) $year->year_start === $currentStart,
            'suggestion' => $this->baseSuggestion($year, $schoolYears),
        ]);

        return [
            'today' => $today->toDateString(),
            'auto_creation' => ['enabled' => $settings->enabled()],
            'academic_years' => $years->map(fn (array $year) => collect($year)->except('suggestion')->all() + [
                'suggestion_source' => $year['suggestion']['source'],
            ])->values()->all(),
            'specialties' => $specialties
                ->map(fn (Specialty $specialty) => $this->describeSpecialty($specialty, $years, $schoolYears, $settings, $specialties))
                ->values()
                ->all(),
        ];
    }

    private function describeSpecialty(
        Specialty $specialty,
        Collection $years,
        Collection $schoolYears,
        AutoCreationSettings $settings,
        Collection $specialties
    ): array {
        $own = $schoolYears
            ->where('specialty_id', $specialty->id)
            ->filter(fn (SchoolAcademicYear $year) => $year->systemAcademicYear)
            ->sortBy(fn (SchoolAcademicYear $year) => (int) $year->systemAcademicYear->year_start)
            ->values();

        return [
            'id' => $specialty->id,
            'label' => $this->label($specialty),
            'specialty_name' => $specialty->specialty_name,
            'level_name' => $specialty->level?->name,
            'department_name' => $specialty->department?->department_name,
            'students_count' => (int) $specialty->student_count,
            'years' => $years->map(fn (array $year) => $this->describeYear($year, $own))->values()->all(),
            'copy_targets' => $this->copyTargets($specialty, $settings, $schoolYears, $specialties),
        ];
    }

    private function describeYear(array $year, Collection $own): array
    {
        $existing = $own->firstWhere('system_academic_year_id', $year['id']);

        if ($existing) {
            return [
                'system_academic_year_id' => $year['id'],
                'created' => true,
                'existing' => [
                    'id' => $existing->id,
                    'start_date' => Carbon::parse($existing->start_date)->toDateString(),
                    'end_date' => Carbon::parse($existing->end_date)->toDateString(),
                ],
                'allowed' => null,
                'suggested' => null,
            ];
        }

        $allowed = $this->allowed($year, $own);

        return [
            'system_academic_year_id' => $year['id'],
            'created' => false,
            'existing' => null,
            'allowed' => $allowed,
            'suggested' => $allowed ? $this->fit($year['suggestion'], $allowed) : null,
        ];
    }

    private function allowed(array $year, Collection $own): ?array
    {
        $previous = $own->filter(fn (SchoolAcademicYear $other) => (int) $other->systemAcademicYear->year_start < $year['year_start'])->last();
        $next = $own->first(fn (SchoolAcademicYear $other) => (int) $other->systemAcademicYear->year_start > $year['year_start']);

        $start = $previous
            ? Carbon::parse($previous->end_date)->startOfDay()->addDay()
            : Carbon::create($year['year_start'], 1, 1);
        $end = $next
            ? Carbon::parse($next->start_date)->startOfDay()->subDay()
            : Carbon::create($year['year_end'], 12, 31);

        if ($end->lte($start)) {
            return null;
        }

        return [
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
            'after' => $previous ? [
                'name' => $previous->systemAcademicYear->name,
                'end_date' => Carbon::parse($previous->end_date)->toDateString(),
            ] : null,
            'before' => $next ? [
                'name' => $next->systemAcademicYear->name,
                'start_date' => Carbon::parse($next->start_date)->toDateString(),
            ] : null,
        ];
    }

    private function baseSuggestion(SystemAcademicYear $year, Collection $schoolYears): array
    {
        $same = $this->mostCommonDates($schoolYears->where('system_academic_year_id', $year->id));
        if ($same) {
            return [...$same, 'source' => 'other_classes'];
        }

        $previous = $this->mostCommonDates(
            $schoolYears->filter(fn (SchoolAcademicYear $other) => (int) $other->systemAcademicYear?->year_start === (int) $year->year_start - 1)
        );
        if ($previous) {
            return [
                'start_date' => Carbon::parse($previous['start_date'])->addYear()->toDateString(),
                'end_date' => Carbon::parse($previous['end_date'])->addYear()->toDateString(),
                'source' => 'last_year',
            ];
        }

        return [
            'start_date' => Carbon::create((int) $year->year_start, 9, 1)->toDateString(),
            'end_date' => Carbon::create((int) $year->year_end, 6, 30)->toDateString(),
            'source' => 'typical',
        ];
    }

    private function mostCommonDates(Collection $schoolYears): ?array
    {
        $pair = $schoolYears
            ->map(fn (SchoolAcademicYear $year) => Carbon::parse($year->start_date)->toDateString() . '|' . Carbon::parse($year->end_date)->toDateString())
            ->countBy()
            ->sortDesc()
            ->keys()
            ->first();

        if (!$pair) {
            return null;
        }

        [$start, $end] = explode('|', $pair);

        return ['start_date' => $start, 'end_date' => $end];
    }

    private function fit(array $suggestion, array $allowed): ?array
    {
        $start = max($suggestion['start_date'], $allowed['start_date']);
        $end = min($suggestion['end_date'], $allowed['end_date']);

        return $start < $end ? ['start_date' => $start, 'end_date' => $end] : null;
    }

    private function copyTargets(Specialty $specialty, AutoCreationSettings $settings, Collection $schoolYears, Collection $specialties): array
    {
        if (!$settings->enabled()) {
            return [];
        }

        return $this->targets->for($specialty, $settings)
            ->map(fn ($target) => [
                'specialty_id' => $target->id,
                'label' => $this->label($specialties->firstWhere('id', $target->id) ?? $target),
                'existing_year_ids' => $schoolYears->where('specialty_id', $target->id)->pluck('system_academic_year_id')->values()->all(),
            ])
            ->values()
            ->all();
    }

    private function label(Specialty $specialty): string
    {
        return collect([$specialty->specialty_name, $specialty->level?->name])->filter()->implode(' · ') ?: 'Specialty';
    }
}
