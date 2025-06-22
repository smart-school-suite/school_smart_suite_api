<?php

namespace App\Services\Stats;

use App\Models\Exams;
use App\Models\StatTypes;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection; // Added for type hinting

class AcademicStatService
{
    /**
     * Retrieves various academic statistics for a given school branch.
     *
     * @param object $currentSchool The current school branch object.
     * @param int|null $year The year for which to retrieve statistics (defaults to current year).
     * @return array An associative array of academic statistics.
     */
    public function getAcademicStats($currentSchool, ?int $year = null): array
    {
        // Use current year if not provided
        $year = $year ?? now()->year;

        $kpiNames = [
            'ca_exam_pass_rate',
            'ca_exam_fail_rate',
            'average_ca_exam_gpa',
            'average_exam_gpa',
            'exam_fail_rate',
            'exam_pass_rate',
            'exam_total_number_of_resit',
            'total_courses_count',
            'total_courses_count_by_department',
        ];

        // Fetch all necessary KPI IDs in a single query and key them by program_name
        // This is efficient and scalable for adding new KPIs
        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');

        // Prepare KPI IDs for direct, null-safe access
        // Using a map for direct access, but the keyBy approach directly on $kpis object is also good.
        // This makes accessing them via $kpis->get('kpi_name')->id more robust.
        $kpiIds = $kpis->mapWithKeys(fn ($kpi) => [$kpi->program_name => $kpi->id])->toArray();

        // Fetch academic data from 'school_exam_stats' for the last 5 years
        $academicData = DB::table('school_exam_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year])
            ->whereIn('stat_type_id', array_filter($kpiIds))
            ->select('stat_type_id', 'year', 'month', 'decimal_value', 'integer_value')
            ->get();

        // Fetch course data from 'course_stats'
        $courseData = DB::table('course_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->when(isset($kpiIds['total_courses_count']), fn ($query) => $query->where('stat_type_id', $kpiIds['total_courses_count']))
            ->when(isset($kpiIds['total_courses_count_by_department']), fn ($query) => $query->orWhere('stat_type_id', $kpiIds['total_courses_count_by_department']))
            ->select('stat_type_id', 'integer_value', 'department_id')
            ->get();

        // Fetch upcoming exams
        $upcomingExams = Exams::with(['specialty', 'examtype'])
            ->where("school_branch_id", $currentSchool->id)
            ->where("status", "!=", "inactive")
            ->orderBy('start_date')
            ->take(5)
            ->get();

        // Prepare the results array, calling private helper methods
        return [
            'average_gpa_current_year' => $this->averageGpa(
                $academicData,
                $year,
                $kpiIds['average_ca_exam_gpa'] ?? null,
                $kpiIds['average_exam_gpa'] ?? null
            ),
            'average_exam_pass_rate_current_year' => $this->averageExamPassRate(
                $academicData,
                $year,
                $kpiIds['ca_exam_pass_rate'] ?? null,
                $kpiIds['exam_pass_rate'] ?? null
            ),
            'ca_exam_pass_fail_rates_current_year' => $this->averageCaExamPassFailRate(
                $academicData,
                $year,
                $kpiIds['ca_exam_pass_rate'] ?? null,
                $kpiIds['ca_exam_fail_rate'] ?? null,
                $kpiIds['exam_pass_rate'] ?? null,
                $kpiIds['exam_fail_rate'] ?? null
            ),
            'pass_rate_over_years' => $this->passRateOverTime(
                $academicData,
                $kpiIds['ca_exam_pass_rate'] ?? null,
                $kpiIds['exam_pass_rate'] ?? null
            ),
            'fail_rate_over_years' => $this->failRateOverTime(
                $academicData,
                $kpiIds['ca_exam_fail_rate'] ?? null,
                $kpiIds['exam_fail_rate'] ?? null
            ),
            'average_gpa_over_time' => $this->averageGpaOverTime(
                $academicData,
                array_filter([$kpiIds['average_ca_exam_gpa'], $kpiIds['average_exam_gpa']])
            ),
            'total_courses_count' => $this->totalNumberOfCourses(
                $courseData->where('stat_type_id', $kpiIds['total_courses_count'] ?? null)->first()
            ),
            'total_courses_count_by_department' => $this->totalCoursesCountByDepartment(
                $courseData->where('stat_type_id', $kpiIds['total_courses_count_by_department'] ?? null)
            ),
            'total_number_of_resit_current_year' => $this->totalNumberOfResit(
                $academicData,
                $kpiIds['exam_total_number_of_resit'] ?? null,
                $year
            ),
            'total_number_of_resit_over_time' => $this->totalNumberOfResitOverTime(
                $academicData,
                $kpiIds['exam_total_number_of_resit'] ?? null
            ),
            'upcoming_exams' => $this->upcomingExams($upcomingExams),
        ];
    }

    /**
     * Safely retrieves a KPI value (decimal or integer) from a collection for a specific year and KPI ID.
     *
     * @param Collection $data The collection of academic or course stats.
     * @param string|null $kpiId The ID of the StatType KPI.
     * @param int $year The year to filter by.
     * @param string $valueColumn The column to sum ('decimal_value' or 'integer_value').
     * @return float|int The sum of the KPI value, or 0 if not found or invalid.
     */
    private function getKpiSumForYear(Collection $data, ?string $kpiId, int $year, string $valueColumn): float|int
    {
        if (is_null($kpiId)) {
            return 0;
        }

        return $data->where('year', $year)
                    ->where('stat_type_id', $kpiId)
                    ->sum($valueColumn);
    }

    /**
     * Calculates the average GPA for the current year (combining CA and main exams).
     *
     * @param Collection $academicData Collection of academic stats.
     * @param int $year The current year.
     * @param string|null $caAverageGpaKpi KPI ID for average CA exam GPA.
     * @param string|null $examAverageGpaKpi KPI ID for average main exam GPA.
     * @return float The calculated average GPA, or 0.0 if no data.
     */
    private function averageGpa(Collection $academicData, int $year, ?string $caAverageGpaKpi, ?string $examAverageGpaKpi): float
    {
        $relevantKpiIds = array_filter([$caAverageGpaKpi, $examAverageGpaKpi]);
        if (empty($relevantKpiIds)) {
            return 0.0;
        }

        $currentYearGpas = $academicData
            ->where('year', $year)
            ->whereIn('stat_type_id', $relevantKpiIds);

        $count = $currentYearGpas->count();
        if ($count === 0) {
            return 0.0;
        }

        return (float) $currentYearGpas->sum('decimal_value') / $count;
    }

    /**
     * Calculates the average exam pass rate for the current year (combining CA and main exams).
     *
     * @param Collection $academicData Collection of academic stats.
     * @param int $year The current year.
     * @param string|null $caPassRateKpi KPI ID for CA exam pass rate.
     * @param string|null $examPassRateKpi KPI ID for main exam pass rate.
     * @return float The calculated average pass rate, or 0.0 if no data.
     */
    private function averageExamPassRate(Collection $academicData, int $year, ?string $caPassRateKpi, ?string $examPassRateKpi): float
    {
        $relevantKpiIds = array_filter([$caPassRateKpi, $examPassRateKpi]);
        if (empty($relevantKpiIds)) {
            return 0.0;
        }

        $currentYearPassRateData = $academicData
            ->where('year', $year)
            ->whereIn('stat_type_id', $relevantKpiIds);

        $count = $currentYearPassRateData->count();
        if ($count === 0) {
            return 0.0;
        }

        return (float) $currentYearPassRateData->sum('decimal_value') / $count;
    }

    /**
     * Calculates the pass and fail rates for CA and main exams for the current year.
     *
     * @param Collection $academicData Collection of academic stats.
     * @param int $year The current year.
     * @param string|null $caPassRateKpi KPI ID for CA pass rate.
     * @param string|null $caFailRateKpi KPI ID for CA fail rate.
     * @param string|null $examPassRateKpi KPI ID for main exam pass rate.
     * @param string|null $examFailRateKpi KPI ID for main exam fail rate.
     * @return array An associative array containing individual pass/fail rates.
     */
    private function averageCaExamPassFailRate(
        Collection $academicData,
        int $year,
        ?string $caPassRateKpi,
        ?string $caFailRateKpi,
        ?string $examPassRateKpi,
        ?string $examFailRateKpi
    ): array {
        $caPassRate = $this->getKpiSumForYear($academicData, $caPassRateKpi, $year, 'decimal_value');
        $caFailRate = $this->getKpiSumForYear($academicData, $caFailRateKpi, $year, 'decimal_value');
        $examPassRate = $this->getKpiSumForYear($academicData, $examPassRateKpi, $year, 'decimal_value');
        $examFailRate = $this->getKpiSumForYear($academicData, $examFailRateKpi, $year, 'decimal_value');

        $caPassRateCount = $academicData->where('year', $year)->where('stat_type_id', $caPassRateKpi)->count();
        $caFailRateCount = $academicData->where('year', $year)->where('stat_type_id', $caFailRateKpi)->count();
        $examPassRateCount = $academicData->where('year', $year)->where('stat_type_id', $examPassRateKpi)->count();
        $examFailRateCount = $academicData->where('year', $year)->where('stat_type_id', $examFailRateKpi)->count();


        return [
            'ca_pass_rate' => $caPassRateCount > 0 ? (float) $caPassRate / $caPassRateCount : 0.0,
            'ca_fail_rate' => $caFailRateCount > 0 ? (float) $caFailRate / $caFailRateCount : 0.0,
            'exam_pass_rate' => $examPassRateCount > 0 ? (float) $examPassRate / $examPassRateCount : 0.0,
            'exam_fail_rate' => $examFailRateCount > 0 ? (float) $examFailRate / $examFailRateCount : 0.0,
        ];
    }

    /**
     * Calculates the average pass rate over time (last 5 years) for CA and main exams.
     *
     * @param Collection $academicData Collection of academic stats.
     * @param string|null $caPassRateKpi KPI ID for CA pass rate.
     * @param string|null $examPassRateKpi KPI ID for main exam pass rate.
     * @return array Containing an array of average pass rates per year.
     */
    private function passRateOverTime(Collection $academicData, ?string $caPassRateKpi, ?string $examPassRateKpi): array
    {
        $relevantKpiIds = array_filter([$caPassRateKpi, $examPassRateKpi]);
        if (empty($relevantKpiIds)) {
            return ['pass_rate_over_years' => []];
        }

        $passRateData = $academicData->whereIn('stat_type_id', $relevantKpiIds)->groupBy('year');

        $averages = $passRateData->map(function ($group, $year) {
            return [
                'year' => (int) $year,
                'average_pass_rate' => (float) $group->avg('decimal_value'),
            ];
        })->values()->sortBy('year')->toArray();

        return [
            'pass_rate_over_years' => $averages,
        ];
    }

    /**
     * Calculates the average fail rate over time (last 5 years) for CA and main exams.
     *
     * @param Collection $academicData Collection of academic stats.
     * @param string|null $caFailRateKpi KPI ID for CA fail rate.
     * @param string|null $examFailRateKpi KPI ID for main exam fail rate.
     * @return array Containing an array of average fail rates per year.
     */
    private function failRateOverTime(Collection $academicData, ?string $caFailRateKpi, ?string $examFailRateKpi): array
    {
        $relevantKpiIds = array_filter([$caFailRateKpi, $examFailRateKpi]);
        if (empty($relevantKpiIds)) {
            return ['fail_rate_over_years' => []];
        }

        $failRateData = $academicData->whereIn('stat_type_id', $relevantKpiIds)->groupBy('year');

        $averages = $failRateData->map(function ($group, $year) {
            return [
                'year' => (int) $year,
                'average_fail_rate' => (float) $group->avg('decimal_value'),
            ];
        })->values()->sortBy('year')->toArray();

        return [
            'fail_rate_over_years' => $averages,
        ];
    }

    /**
     * Calculates the average GPA over time (last 5 years).
     *
     * @param Collection $academicData Collection of academic stats.
     * @param array $averageGpaKpiIds Array of KPI IDs for average GPA (e.g., CA and main exam).
     * @return array Containing an array of average GPAs per year.
     */
    private function averageGpaOverTime(Collection $academicData, array $averageGpaKpiIds): array
    {
        if (empty($averageGpaKpiIds)) {
            return ['average_gpa_over_time' => []];
        }

        $gpaData = $academicData->whereIn('stat_type_id', $averageGpaKpiIds)->groupBy('year');

        $averages = $gpaData->map(function ($group, $year) {
            return [
                'year' => (int) $year,
                'average_gpa' => (float) $group->avg('decimal_value'),
            ];
        })->values()->sortBy('year')->toArray();

        return [
            'average_gpa_over_time' => $averages,
        ];
    }

    /**
     * Retrieves the total number of courses.
     * Assumes $courseStat is a single record or null with 'integer_value' for 'total_courses_count' KPI.
     *
     * @param object|null $courseStat A single record from course_stats table for total courses.
     * @return int
     */
    private function totalNumberOfCourses(?object $courseStat): int
    {
        return (int) ($courseStat->integer_value ?? 0);
    }

    /**
     * Retrieves the total number of courses by department.
     * Assumes $courseData is a collection of records for 'total_courses_count_by_department' KPI.
     *
     * @param Collection $courseData Collection of course stats data filtered by department KPI.
     * @return array An array of course counts grouped by department.
     */
    private function totalCoursesCountByDepartment(Collection $courseData): array
    {
        if ($courseData->isEmpty()) {
            return [];
        }

        $departmentCounts = $courseData->groupBy('department_id')->map(function ($items, $departmentId) {
            // If you have a Department model, fetch the name:
            // $departmentName = Department::find($departmentId)->name ?? 'Unknown Department';
            $departmentName = 'Department ' . $departmentId; // Placeholder if no Department model is available for joining here

            return [
                'department' => $departmentName,
                'total_courses' => (int) $items->sum('integer_value'),
            ];
        })->values()->toArray();

        return $departmentCounts;
    }


    /**
     * Retrieves the total number of resits for the current year.
     *
     * @param Collection $academicData Collection of academic stats.
     * @param string|null $resitCountKpi KPI ID for total number of resits.
     * @param int $year The current year.
     * @return int The total number of resits for the given year, or 0.
     */
    private function totalNumberOfResit(Collection $academicData, ?string $resitCountKpi, int $year): int
    {
        return (int) $this->getKpiSumForYear($academicData, $resitCountKpi, $year, 'integer_value');
    }

    /**
     * Retrieves the total number of resits over time (last 5 years).
     *
     * @param Collection $academicData Collection of academic stats.
     * @param string|null $resitCountKpi KPI ID for total number of resits.
     * @return array An array of resit counts per year.
     */
    private function totalNumberOfResitOverTime(Collection $academicData, ?string $resitCountKpi): array
    {
        if (is_null($resitCountKpi)) {
            return [];
        }

        $resitData = $academicData->where('stat_type_id', $resitCountKpi)->groupBy('year');

        $resitCounts = $resitData->map(function ($group, $year) {
            return [
                'year' => (int) $year,
                'resit_count' => (int) $group->sum('integer_value'),
            ];
        })->values()->sortBy('year')->toArray(); // Ensure numeric keys and sort by year

        return $resitCounts;
    }

    /**
     * Formats the upcoming exams data.
     *
     * @param Collection $exams Collection of Exams models.
     * @return array Containing an array of upcoming exam details.
     */
    private function upcomingExams(Collection $exams): array
    {
        return [
            'upcoming_exams' => $exams->values()->toArray()
        ];
    }
}
