<?php

namespace App\Services\Stats;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection; // Added for type hinting
use App\Models\Announcement;
use App\Models\SchoolEvent;
use App\Models\Elections;
use App\Models\StatTypes;
use App\Models\Department; // Added for fetching department names

class OperationalStatService
{
    /**
     * Retrieves various operational statistics for a given school branch.
     *
     * @param object $currentSchool The current school branch object.
     * @param int|null $year The year for which to retrieve statistics (defaults to current year).
     * @param int|null $month The month for which to retrieve statistics (defaults to current month).
     * @return array An associative array of operational statistics.
     */
    public function getOperationalStats($currentSchool, ?int $year = null, ?int $month = null): array
    {
        // Use current year/month if not provided
        $year = $year ?? now()->year;
        $month = $month ?? now()->month;

        // Define target KPIs to fetch from StatTypes
        $targetKpis = [
            'total_number_of_specialties',
            'total_number_of_departments',
            'registered_teachers_count_over_time', // Historical teacher count
            'total_registered_teachers',           // Current total teachers
            'registered_students_count_over_time', // Historical student count
            'total_school_admin',                  // Current total school admins
            'department_registration_count_over_time', // Student registration by department over time
            'female_registered_student_count_over_time', // Female student count over time
            'male_registered_student_count_over_time'    // Male student count over time
        ];

        // Fetch all necessary KPI IDs in a single query and key them by program_name
        $kpis = StatTypes::whereIn('program_name', $targetKpis)->get()->keyBy('program_name');

        // Prepare KPI IDs for direct, null-safe access
        $kpiIds = [
            'total_number_of_specialties'               => $kpis->get('total_number_of_specialties')->id ?? null,
            'total_number_of_departments'               => $kpis->get('total_number_of_departments')->id ?? null,
            'registered_teachers_count_over_time'       => $kpis->get('registered_teachers_count_over_time')->id ?? null,
            'total_registered_teachers'                 => $kpis->get('total_registered_teachers')->id ?? null,
            'registered_students_count_over_time'       => $kpis->get('registered_students_count_over_time')->id ?? null,
            'total_school_admin'                        => $kpis->get('total_school_admin')->id ?? null,
            'department_registration_count_over_time'   => $kpis->get('department_registration_count_over_time')->id ?? null,
            'female_registered_student_count_over_time' => $kpis->get('female_registered_student_count_over_time')->id ?? null,
            'male_registered_student_count_over_time'   => $kpis->get('male_registered_student_count_over_time')->id ?? null
        ];

        // --- Fetch all raw data efficiently from the database ---
        // Assume 'integer_value' column stores the counts in *_stats tables.

        // Specialty stats (assuming a single record for total, or sum for current month/year)
        // Get the integer_value for the specialty count for the current year, latest month
        $specialtyStatsValue = DB::table('specialty_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->when($kpiIds['total_number_of_specialties'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->where('year', $year) // Assuming count is per year
            ->latest('month')
            ->value('integer_value'); // Get just the integer_value

        // Department stats (assuming a single record for total, or sum for current month/year)
        // Get the integer_value for the department count for the current year, latest month
        $departmentStatsValue = DB::table('department_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->when($kpiIds['total_number_of_departments'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->where('year', $year) // Assuming count is per year
            ->latest('month')
            ->value('integer_value'); // Get just the integer_value

        // Teacher stats (for both historical and current total)
        // Fetch all relevant teacher stats for the last 5 years
        $teacherStatsData = DB::table('teacher_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year]) // For historical data over 5 years
            ->whereIn('stat_type_id', array_filter([ // Use array_filter to remove null KPI IDs
                $kpiIds['registered_teachers_count_over_time'],
                $kpiIds['total_registered_teachers']
            ]))
            ->get(['integer_value', 'year', 'month', 'stat_type_id']);

        // School Admin stats (current total)
        // Get the integer_value for school admin count for the current year, latest month
        $schoolAdminStatsValue = DB::table('school_admin_stats')
            ->where('school_branch_id', $currentSchool->id)
            ->when($kpiIds['total_school_admin'], fn ($query, $kpiId) => $query->where('stat_type_id', $kpiId))
            ->where('year', $year) // Assuming current total for the year
            ->latest('month')
            ->value('integer_value'); // Get just the integer_value

        // Student registration data (historical and by gender/department)
        // Fetch all relevant student stats for the last 5 years
        $studentStatsData = DB::table('student_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->whereBetween('year', [$year - 5, $year])
            ->whereIn('stat_type_id', array_filter([ // Use array_filter to remove null KPI IDs
                $kpiIds['registered_students_count_over_time'],
                $kpiIds['department_registration_count_over_time'],
                $kpiIds['female_registered_student_count_over_time'],
                $kpiIds['male_registered_student_count_over_time']
            ]))
            ->get(['integer_value', 'year', 'month', 'stat_type_id', 'department_id']); // Ensure department_id is fetched if needed

        // Announcements (active and scheduled)
        $announcementData = Announcement::where("school_branch_id", $currentSchool->id)
            ->where(function ($query) {
                $query->where("status", "active")
                      ->orWhere("status", "scheduled");
            })
            ->with(['announcementCategory', 'announcementLabel', 'announcementTag'])
            ->get();

        // School Events (active and scheduled)
        $schoolEventData = SchoolEvent::where("school_branch_id", $currentSchool->id)
            ->where(function ($query) {
                $query->where("status", "active")
                      ->orWhere("status", "scheduled");
            })
            ->with(['eventTag', 'eventCategory'])
            ->get();

        // Elections (ongoing and pending)
        $electionData = Elections::where("school_branch_id", $currentSchool->id)
            ->where(function ($query) {
                $query->where("status", "ongoing")
                      ->orWhere("status", "pending");
            })
            ->with(['electionType'])
            ->get();

        // --- Process the fetched data using private methods ---
        return [
            'total_specialties' => $this->specialtyCount($specialtyStatsValue),
            'total_departments' => $this->departmentCount($departmentStatsValue),

            // Filter teacherStatsData for the current total registered teachers
            'total_registered_teachers' => $this->totalNumberOfTeachers(
                $teacherStatsData->where('stat_type_id', $kpiIds['total_registered_teachers'])
                                 ->where('year', $year) // Ensure we get current year's total
                                 ->sortByDesc('month') // Get latest month's total
                                 ->first()
            ),
            // Filter teacherStatsData for historical teacher count over time
            'teachers_count_over_time' => $this->teachersCountOverTime(
                $teacherStatsData->where('stat_type_id', $kpiIds['registered_teachers_count_over_time'])
            ),
            'total_school_admin' => $this->totalNumberOfSchoolAdmin($schoolAdminStatsValue),
            'total_staff' => $this->totalNumberOfStaff(
                $teacherStatsData->where('stat_type_id', $kpiIds['total_registered_teachers'])
                                 ->where('year', $year)
                                 ->sortByDesc('month')
                                 ->first(),
                $schoolAdminStatsValue
            ),
            'announcements' => $this->getAnnouncementData($announcementData),
            'school_events' => $this->getEventsData($schoolEventData),
            'elections' => $this->getElectionData($electionData),

            // Filter studentStatsData for male/female counts over time
            'student_gender_registration_over_time' => $this->totalMaleFemaleRegistrationCountOverTime(
                $studentStatsData->whereIn('stat_type_id', array_filter([
                    $kpiIds['male_registered_student_count_over_time'],
                    $kpiIds['female_registered_student_count_over_time']
                ])),
                $kpiIds['male_registered_student_count_over_time'],
                $kpiIds['female_registered_student_count_over_time']
            ),
            // Filter studentStatsData for department registration counts over time
            'student_registration_by_department_over_years' => $this->totalStudentCountOverYearsByDepartment(
                $studentStatsData->where('stat_type_id', $kpiIds['department_registration_count_over_time'])
            ),
        ];
    }

    /**
     * Processes announcement data to separate active and scheduled announcements.
     *
     * @param Collection $announcementData Collection of Announcement models.
     * @return array
     */
    private function getAnnouncementData(Collection $announcementData): array
    {
        // Use `filter` instead of `where` on collection directly to create new collections
        // This prevents modifying the original collection or side effects if used multiple times
        $activeAnnouncements = $announcementData->filter(fn ($announcement) => $announcement->status === "active")->take(5);
        $scheduledAnnouncements = $announcementData->filter(fn ($announcement) => $announcement->status === "scheduled")->take(5);

        return [
            "active_announcement" => [
                'count' => $activeAnnouncements->count(),
                'announcement_details' => $activeAnnouncements->values()->toArray() // Reset keys and convert to array
            ],
            "scheduled_announcement" => [
                'count' => $scheduledAnnouncements->count(),
                'announcement_details' => $scheduledAnnouncements->values()->toArray() // Reset keys and convert to array
            ]
        ];
    }

    /**
     * Processes school event data to separate active and scheduled events.
     *
     * @param Collection $schoolEventData Collection of SchoolEvent models.
     * @return array
     */
    private function getEventsData(Collection $schoolEventData): array
    {
        $activeEvents = $schoolEventData->filter(fn ($event) => $event->status === "active")->take(5);
        $scheduledEvents = $schoolEventData->filter(fn ($event) => $event->status === "scheduled")->take(5);

        return [
            "active_events" => [
                'count' => $activeEvents->count(),
                'event_details' => $activeEvents->values()->toArray()
            ],
            'scheduled_events' => [
                'count' => $scheduledEvents->count(),
                'event_details' => $scheduledEvents->values()->toArray()
            ]
        ];
    }

    /**
     * Processes election data to separate ongoing and upcoming elections.
     *
     * @param Collection $electionData Collection of Election models.
     * @return array
     */
    private function getElectionData(Collection $electionData): array
    {
        $activeElections = $electionData->filter(fn ($election) => $election->status === "ongoing")->take(3);
        $upcomingElections = $electionData->filter(fn ($election) => $election->status === 'pending')->take(3);

        return [
            'active_elections' => [
                'count' => $activeElections->count(),
                'election_details' => $activeElections->values()->toArray()
            ],
            'upcoming_elections' => [
                'count' => $upcomingElections->count(),
                'election_details' => $upcomingElections->values()->toArray()
            ]
        ];
    }

    /**
     * Retrieves the total count of specialties.
     * Assumes $specialtyStatValue is the direct integer count for the current year/month.
     *
     * @param int|null $specialtyStatValue The integer value of total specialties.
     * @return int
     */
    private function specialtyCount(?int $specialtyStatValue): int
    {
        return $specialtyStatValue ?? 0;
    }

    /**
     * Retrieves the total count of departments.
     * Assumes $departmentStatValue is the direct integer count for the current year/month.
     *
     * @param int|null $departmentStatValue The integer value of total departments.
     * @return int
     */
    private function departmentCount(?int $departmentStatValue): int
    {
        return $departmentStatValue ?? 0;
    }

    /**
     * Retrieves the total number of registered teachers.
     * Assumes $teacherStat is a single record or null for the current year/month,
     * containing the 'integer_value' for 'total_registered_teachers' KPI.
     *
     * @param object|null $teacherStat A single record from teacher_stats table.
     * @return int
     */
    private function totalNumberOfTeachers(?object $teacherStat): int
    {
        return (int) ($teacherStat->integer_value ?? 0);
    }

    /**
     * Retrieves teacher registration counts over time (last 5 years).
     *
     * @param Collection $teacherHistoricalData Collection of teacher stats data filtered by 'registered_teachers_count_over_time' KPI.
     * @return Collection
     */
    private function teachersCountOverTime(Collection $teacherHistoricalData): Collection
    {
        // Group by year and sum the integer_value for each year
        return $teacherHistoricalData
            ->groupBy('year')
            ->map(function ($yearData, $year) {
                return [
                    'year' => (int) $year,
                    'count' => (int) $yearData->sum('integer_value'),
                ];
            })
            ->values() // Reset keys
            ->sortBy('year'); // Sort by year
    }

    /**
     * Retrieves the total number of school administrators.
     * Assumes $schoolAdminStatValue is the direct integer count for the current year/month.
     *
     * @param int|null $schoolAdminStatValue The integer value of total school admins.
     * @return int
     */
    private function totalNumberOfSchoolAdmin(?int $schoolAdminStatValue): int
    {
        return $schoolAdminStatValue ?? 0;
    }

    /**
     * Calculates the total number of staff (teachers + school admins).
     *
     * @param object|null $teacherStat A single record for total teachers (containing integer_value).
     * @param int|null $schoolAdminStatValue The integer value for total school admins.
     * @return int
     */
    private function totalNumberOfStaff(?object $teacherStat, ?int $schoolAdminStatValue): int
    {
        return $this->totalNumberOfTeachers($teacherStat) + $this->totalNumberOfSchoolAdmin($schoolAdminStatValue);
    }

    /**
     * Calculates total male and female student registration counts over specified years.
     *
     * @param Collection $studentGenderData Collection of student stats data filtered by male/female KPIs.
     * @param string|null $maleCountKpiId KPI ID for male students.
     * @param string|null $femaleCountKpiId KPI ID for female students.
     * @return Collection
     */
    private function totalMaleFemaleRegistrationCountOverTime(
        Collection $studentGenderData,
        ?string $maleCountKpiId,
        ?string $femaleCountKpiId
    ): Collection {
        if (!$maleCountKpiId || !$femaleCountKpiId) {
            return collect(); // Return empty collection if KPI IDs are missing
        }

        // Group by year and then by stat_type_id to sum male/female counts per year
        return $studentGenderData->groupBy('year')->map(function ($yearData, $year) use ($maleCountKpiId, $femaleCountKpiId) {
            $maleCount = $yearData->where('stat_type_id', $maleCountKpiId)->sum('integer_value');
            $femaleCount = $yearData->where('stat_type_id', $femaleCountKpiId)->sum('integer_value');
            return [
                'year' => (int) $year,
                'male_student_count' => (int) $maleCount,
                'female_student_count' => (int) $femaleCount,
            ];
        })->values()->sortBy('year'); // Ensure numeric keys and sorted by year
    }

    /**
     * Calculates total student counts over years, grouped by department.
     *
     * @param Collection $studentDepartmentData Collection of student stats data filtered by department KPI.
     * @return Collection
     */
    private function totalStudentCountOverYearsByDepartment(Collection $studentDepartmentData): Collection
    {
        // Get unique department IDs from the fetched data
        $departmentIds = $studentDepartmentData->pluck('department_id')->filter()->unique();

        // Fetch department names from the database in a single query
        $departments = Department::whereIn('id', $departmentIds)->get()->keyBy('id');

        // Group by year first, then by department_id within each year
        $result = $studentDepartmentData->groupBy('year')->map(function ($yearData, $year) use ($departments) {
            $departmentCounts = $yearData->groupBy('department_id')->map(function ($items, $departmentId) use ($departments) {
                return [
                    'department' => $departments->get($departmentId)->name ?? 'Unknown',
                    'total_students' => (int) $items->sum('integer_value'), // Sum the integer_value for students in this department for this year
                ];
            })->values(); // Reset keys for department counts within the year

            return [
                'year' => (int) $year,
                'department_data' => $departmentCounts->toArray(), // Convert department data to array
            ];
        })->values()->sortBy('year'); // Reset keys for the main collection and sort by year

        return $result;
    }
}
