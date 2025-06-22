<?php

namespace App\Services\Stats;

use App\Models\ElectionRoles;
use App\Models\Elections;
use Illuminate\Support\Facades\DB;
use App\Models\StatTypes;

class ElectionStatService
{
    /**
     * Retrieves and formats various election statistics for a given school branch and year.
     *
     * @param object $currentSchool The current school branch object.
     * @param int|null $year The year for which to retrieve statistics (defaults to current year).
     * @return array An array containing different election statistics.
     * @throws \Exception If required StatTypes are not found.
     */
    public function getElectionStats($currentSchool, $year = null)
    {
        $year = $year ?? now()->year;

        $kpiNames = [
            'total_election_count',
            'total_election_type_count_by_election',
            'total_vote_count',
            'total_application_count',
            'total_application_rejection_count',
            'total_application_acceptance_count',
        ];


        $kpis = StatTypes::whereIn('program_name', $kpiNames)->get()->keyBy('program_name');


        foreach ($kpiNames as $kpiName) {
            if (!$kpis->has($kpiName)) {

                throw new \Exception("Required StatType '{$kpiName}' not found in the database.");
            }
        }


        $electionCountKpiId = $kpis->get('total_election_count')->id;
        $electionTypeCountKpiId = $kpis->get('total_election_type_count_by_election')->id;
        $voteCountKpiId = $kpis->get('total_vote_count')->id;
        $applicationCountKpiId = $kpis->get('total_application_count')->id;
        $applicationRejectionCountKpiId = $kpis->get('total_application_rejection_count')->id;
        $electionAcceptanceCountKpiId = $kpis->get('total_application_acceptance_count')->id;


        $electionRoleData = ElectionRoles::where("school_branch_id", $currentSchool->id)->get();

        $elections = Elections::where("school_branch_id", $currentSchool->id)
            ->whereYear("created_at", $year)
            ->with(['electionType'])
            ->get();


        $electionStatData = DB::table('election_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->whereIn("stat_type_id", [$electionCountKpiId, $electionTypeCountKpiId])
            ->where("year", $year)
            ->get();


        $startYearVote = $year - 4;
        $electionVoteStatData = DB::table('election_vote_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->where("stat_type_id", $voteCountKpiId)
            ->whereBetween("year", [$startYearVote, $year])
            ->get();

        $startYearApplication = $year - 4;
        $electionApplicationData = DB::table('election_application_stats')
            ->where("school_branch_id", $currentSchool->id)
            ->whereIn("stat_type_id", [
                $applicationCountKpiId,
                $applicationRejectionCountKpiId,
                $electionAcceptanceCountKpiId
            ])
            ->whereBetween("year", [$startYearApplication, $year])
            ->get();


        $stats = [
            'total_election_count_current_year' => $this->electionCount($electionStatData, $electionCountKpiId),
            'total_election_roles_count' => $this->totalElectionRoleCount($electionRoleData),
            'upcoming_elections' => $this->upcomingElectionData($elections),
            'live_elections' => $this->getLiveElections($elections),
            'election_type_counts' => $this->getElectionTypeCounts($electionStatData, $electionTypeCountKpiId),
            'total_votes_over_time' => $this->totalNumberOfVotesOverTime($electionVoteStatData, $voteCountKpiId),
            'total_applications_over_time' => $this->totalNumberOfApplications($electionApplicationData, $applicationCountKpiId),
            'total_rejected_applications_over_time' => $this->totalRejectedApplicationsOverTime($electionApplicationData, $applicationRejectionCountKpiId),
            'total_accepted_applications_over_time' => $this->totalAcceptedApplicationsOverTime($electionApplicationData, $electionAcceptanceCountKpiId),
        ];

        return $stats;
    }

    /**
     * Retrieves the total vote count from pre-calculated stats.
     * Assumes $electionVoteStatData is a collection that might have multiple entries for the same KPI for different years.
     * If you need the *current year's* vote count, you'd typically filter by year again or ensure the passed collection is already filtered.
     * For a single value, it usually means fetching for the current year.
     *
     * @param \Illuminate\Support\Collection $electionVoteStatData
     * @param int $voteCountKpiId
     * @return int
     */
    public function electionVoteNumbers($electionVoteStatData, $voteCountKpiId)
    {

        return $electionVoteStatData->where("stat_type_id", $voteCountKpiId)->sum('integer_value');
    }

    /**
     * Retrieves the total election count for the current year from pre-calculated stats.
     *
     * @param \Illuminate\Support\Collection $electionStatData
     * @param int $electionCountKpiId
     * @return int
     */
    public function electionCount($electionStatData, $electionCountKpiId)
    {

        return $electionStatData->where("stat_type_id", $electionCountKpiId)->sum('integer_value');
    }

    /**
     * Returns the total count of election roles.
     *
     * @param \Illuminate\Database\Eloquent\Collection $electionRoleData
     * @return int
     */
    public function totalElectionRoleCount($electionRoleData)
    {
        return $electionRoleData->count();
    }

    /**
     * Retrieves the top 5 upcoming elections.
     *
     * @param \Illuminate\Database\Eloquent\Collection $elections
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function upcomingElectionData($elections)
    {
        return $elections->where("status", "pending")->sortBy('start_date')->take(5)->values(); // Sort by date for proper "upcoming"
    }

    /**
     * Retrieves the top 5 live/ongoing elections.
     *
     * @param \Illuminate\Database\Eloquent\Collection $elections
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLiveElections($elections)
    {
        return $elections->where("status", "ongoing")->sortByDesc('created_at')->take(5)->values(); // Or sortBy('start_date')
    }

    /**
     * Calculates the total number of applications over time for the specified KPI.
     *
     * @param \Illuminate\Support\Collection $electionApplicationData
     * @param int $applicationCountKpiId
     * @return array
     */
    public function totalNumberOfApplications($electionApplicationData, $applicationCountKpiId)
    {
        $filteredData = $electionApplicationData->where("stat_type_id", $applicationCountKpiId);
        $totalApplicationOverYears = $filteredData->groupBy('year');

        $applicationOverYears = [];
        foreach ($totalApplicationOverYears as $year => $applications) {
            $applicationOverYears[] = [
                'year' => (int) $year,
                'total_applications' => $applications->sum('integer_value')
            ];
        }

        usort($applicationOverYears, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        return $applicationOverYears;
    }

    /**
     * Calculates the total number of votes over time for the specified KPI.
     *
     * @param \Illuminate\Support\Collection $electionVoteStatData
     * @param int $voteCountKpiId
     * @return array
     */
    public function totalNumberOfVotesOverTime($electionVoteStatData, $voteCountKpiId)
    {
        $filteredData = $electionVoteStatData->where("stat_type_id", $voteCountKpiId);
        $totalVoteOverYears = $filteredData->groupBy('year');

        $voteOverYears = [];
        foreach ($totalVoteOverYears as $year => $votes) {
            $voteOverYears[] = [
                'year' => (int) $year,
                'total_votes' => $votes->sum('integer_value')
            ];
        }

        usort($voteOverYears, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        return $voteOverYears;
    }

    /**
     * Calculates the total number of rejected applications over time for the specified KPI.
     *
     * @param \Illuminate\Support\Collection $electionApplicationData
     * @param int $applicationRejectionCountKpiId
     * @return array
     */
    public function totalRejectedApplicationsOverTime($electionApplicationData, $applicationRejectionCountKpiId)
    {
        $filteredData = $electionApplicationData->where("stat_type_id", $applicationRejectionCountKpiId);
        $rejectedApplicationsOverYears = $filteredData->groupBy('year');

        $data = [];
        foreach ($rejectedApplicationsOverYears as $year => $applications) {
            $data[] = [
                'year' => (int) $year,
                'rejected_applications' => $applications->sum('integer_value')
            ];
        }

        usort($data, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        return $data;
    }

    /**
     * Calculates the total number of accepted applications over time for the specified KPI.
     *
     * @param \Illuminate\Support\Collection $electionApplicationData
     * @param int $electionAcceptanceCountKpiId
     * @return array
     */
    public function totalAcceptedApplicationsOverTime($electionApplicationData, $electionAcceptanceCountKpiId)
    {
        $filteredData = $electionApplicationData->where("stat_type_id", $electionAcceptanceCountKpiId);
        $acceptedApplicationsOverYears = $filteredData->groupBy('year');

        $data = [];
        foreach ($acceptedApplicationsOverYears as $year => $applications) {
            $data[] = [
                'year' => (int) $year,
                'accepted_applications' => $applications->sum('integer_value')
            ];
        }

        usort($data, function ($a, $b) {
            return $a['year'] <=> $b['year'];
        });

        return $data;
    }

    /**
     * Gets the count of elections by their type for the current year.
     *
     * @param \Illuminate\Support\Collection $electionStatData
     * @param int $electionTypeCountKpiId
     * @return array
     */
    public function getElectionTypeCounts($electionStatData, $electionTypeCountKpiId)
    {
        $electionTypeStats = $electionStatData->where("stat_type_id", $electionTypeCountKpiId);
        $groupedByType = $electionTypeStats->groupBy('reference_id');

        $counts = [];
        foreach ($groupedByType as $electionTypeId => $group) {
            $counts[] = [
                'election_type_id' => $electionTypeId,
                'count' => $group->sum('integer_value')
            ];
        }

        return $counts;
    }
}
