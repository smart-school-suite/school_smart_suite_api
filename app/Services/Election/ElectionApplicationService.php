<?php

namespace App\Services\Election;

use App\Jobs\NotificationJobs\SendAdminApplicationApprovedNotification;
use App\Jobs\NotificationJobs\SendCandidacyApprovedNotification;
use App\Models\Election\Election;
use App\Models\Election\ElectionApplication;
use App\Models\Election\ElectionCandidate;
use App\Models\Election\ElectionRole;
use App\Models\Schooladmin;
use App\Services\Election\Support\ElectionParticipantType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Candidacy applications: submission by eligible participants and review
 * (approve / reject) by administrators. Approval creates the candidate.
 */
class ElectionApplicationService
{
    use FindsElectionRecords;

    private const RELATIONS = ['applicantable', 'position:id,name,seats', 'election:id,title,status,election_type_id', 'candidate'];

    /** Elections in which applications may still be reviewed. */
    private const REVIEWABLE_STATUSES = [
        Election::STATUS_UPCOMING,
        Election::STATUS_APPLICATION_OPEN,
        Election::STATUS_APPLICATION_CLOSED,
    ];

    public function __construct(
        private ElectionService $electionService,
        private ElectionEligibilityService $eligibility,
    ) {}

    /**
     * Submit an application. Participants apply for themselves; a school admin
     * may submit on behalf of an eligible participant (applicant_type/applicant_id).
     */
    public function createApplication(array $data, object $currentSchool, Model $authUser): ElectionApplication
    {
        $applicant = $this->resolveApplicant($data, $currentSchool, $authUser);

        return DB::transaction(function () use ($data, $currentSchool, $applicant) {
            $election = $this->findInBranch(Election::class, $data['election_id'], $currentSchool, [], true);
            $this->electionService->sync($election);

            if (!$election->isApplicationWindowOpen()) {
                $this->fail("Applications closed", "This election is not currently accepting applications.", 409);
            }

            $participant = $this->eligibility->participantRecord($election, $applicant, true);
            if (!$participant?->can_apply) {
                $this->fail("Not eligible", "This participant is not eligible to apply for candidacy in this election.", 403);
            }

            $position = $this->positionForElection($election, $data['election_role_id']);

            $violations = $this->eligibility->officeTermViolations($election, $position, $applicant);
            if ($violations) {
                $this->fail("Not eligible for this position", implode(' ', $violations), 403);
            }

            $existing = ElectionApplication::where('election_id', $election->id)
                ->where('applicantable_type', $applicant->getMorphClass())
                ->where('applicantable_id', $applicant->getKey());

            if ((clone $existing)->where('election_role_id', $position->id)->exists()) {
                $this->fail("Application already submitted", "An application for this position has already been submitted.", 409);
            }

            $activeCount = (clone $existing)
                ->whereIn('application_status', [ElectionApplication::STATUS_PENDING, ElectionApplication::STATUS_APPROVED])
                ->count();
            $limit = max(1, (int) $election->max_applications_per_participant);
            if ($activeCount >= $limit) {
                $this->fail("Application limit reached", "A participant may apply for at most {$limit} position(s) in this election.", 409);
            }

            $application = ElectionApplication::create([
                'school_branch_id' => $currentSchool->id,
                'election_id' => $election->id,
                'election_role_id' => $position->id,
                'applicantable_type' => $applicant->getMorphClass(),
                'applicantable_id' => $applicant->getKey(),
                'manifesto' => $data['manifesto'],
                'personal_vision' => $data['personal_vision'],
                'commitment_statement' => $data['commitment_statement'],
                'application_status' => ElectionApplication::STATUS_PENDING,
            ]);

            return $application->load(self::RELATIONS);
        });
    }

    /**
     * Applicants may edit their pending application while applications are open.
     */
    public function updateApplication(array $data, string $applicationId, object $currentSchool, Model $authUser): ElectionApplication
    {
        $application = $this->findInBranch(ElectionApplication::class, $applicationId, $currentSchool, ['election']);
        $this->assertOwnerOrAdmin($application, $authUser);

        if ($application->application_status !== ElectionApplication::STATUS_PENDING) {
            $this->fail("Application already reviewed", "Only pending applications can be edited.", 409);
        }
        $this->electionService->sync($application->election);
        if (!$application->election->isApplicationWindowOpen()) {
            $this->fail("Applications closed", "Applications can only be edited while the application period is open.", 409);
        }

        $attributes = Arr::only(array_filter($data, fn($v) => $v !== null), ['manifesto', 'personal_vision', 'commitment_statement']);

        if (!empty($data['election_role_id']) && $data['election_role_id'] !== $application->election_role_id) {
            $position = $this->positionForElection($application->election, $data['election_role_id']);
            $applicant = $application->applicantable;
            $violations = $this->eligibility->officeTermViolations($application->election, $position, $applicant);
            if ($violations) {
                $this->fail("Not eligible for this position", implode(' ', $violations), 403);
            }
            $duplicate = ElectionApplication::where('election_id', $application->election_id)
                ->where('election_role_id', $position->id)
                ->where('applicantable_type', $application->applicantable_type)
                ->where('applicantable_id', $application->applicantable_id)
                ->exists();
            if ($duplicate) {
                $this->fail("Application already submitted", "An application for this position has already been submitted.", 409);
            }
            $attributes['election_role_id'] = $position->id;
        }

        $application->update($attributes);
        return $application->fresh(self::RELATIONS);
    }

    public function withdrawApplication(string $applicationId, object $currentSchool, Model $authUser): ElectionApplication
    {
        return DB::transaction(function () use ($applicationId, $currentSchool, $authUser) {
            $application = $this->findInBranch(ElectionApplication::class, $applicationId, $currentSchool, ['election', 'candidate'], true);
            $this->assertOwnerOrAdmin($application, $authUser);
            $this->electionService->sync($application->election);

            if (!in_array($application->application_status, [ElectionApplication::STATUS_PENDING, ElectionApplication::STATUS_APPROVED], true)) {
                $this->fail("Application cannot be withdrawn", "Only pending or approved applications can be withdrawn.", 409);
            }
            if (!in_array($application->election->status, self::REVIEWABLE_STATUSES, true)) {
                $this->fail("Too late to withdraw", "Applications cannot be withdrawn once voting has started.", 409);
            }

            $application->update(['application_status' => ElectionApplication::STATUS_WITHDRAWN]);
            $application->candidate?->update([
                'status' => ElectionCandidate::STATUS_WITHDRAWN,
                'status_reason' => 'Application withdrawn by the applicant.',
            ]);

            return $application->fresh(self::RELATIONS);
        });
    }

    public function deleteApplication(string $applicationId, object $currentSchool): ElectionApplication
    {
        $application = $this->findInBranch(ElectionApplication::class, $applicationId, $currentSchool, ['candidate']);

        if ($application->candidate && $application->candidate->votes()->exists()) {
            $this->fail("Application has votes", "This candidate has already received votes; the application cannot be deleted.", 409);
        }

        DB::transaction(function () use ($application) {
            $application->candidate?->delete();
            $application->delete();
        });

        return $application;
    }

    public function bulkDeleteApplications(array $applicationIds, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($applicationIds)
            ->map(fn($id) => $this->deleteApplication($id, $currentSchool))->all());
    }

    // ------------------------------------------------------------- review

    public function approveApplication(string $applicationId, object $currentSchool, Model $reviewer, ?string $remarks = null): ElectionApplication
    {
        return DB::transaction(function () use ($applicationId, $currentSchool, $reviewer, $remarks) {
            $application = $this->reviewableApplication($applicationId, $currentSchool);

            if ($application->application_status === ElectionApplication::STATUS_APPROVED) {
                $this->fail("Application already approved", "This application has already been approved.", 409);
            }

            $violations = $this->eligibility->officeTermViolations($application->election, $application->position, $application->applicantable);
            if ($violations) {
                $this->fail("Applicant no longer eligible", implode(' ', $violations), 409);
            }

            $this->markReviewed($application, ElectionApplication::STATUS_APPROVED, $reviewer, $remarks);

            ElectionCandidate::updateOrCreate(
                ['application_id' => $application->id],
                [
                    'school_branch_id' => $application->school_branch_id,
                    'election_id' => $application->election_id,
                    'election_role_id' => $application->election_role_id,
                    'candidateable_type' => $application->applicantable_type,
                    'candidateable_id' => $application->applicantable_id,
                    'status' => ElectionCandidate::STATUS_ACTIVE,
                    'status_reason' => null,
                ]
            );

            $payload = [['application_id' => $application->id]];
            SendCandidacyApprovedNotification::dispatch($payload, $currentSchool->id)->afterCommit();
            SendAdminApplicationApprovedNotification::dispatch($payload, $currentSchool->id)->afterCommit();

            return $application->fresh(self::RELATIONS);
        });
    }

    public function rejectApplication(string $applicationId, object $currentSchool, Model $reviewer, ?string $remarks = null): ElectionApplication
    {
        return DB::transaction(function () use ($applicationId, $currentSchool, $reviewer, $remarks) {
            $application = $this->reviewableApplication($applicationId, $currentSchool);

            if ($application->application_status === ElectionApplication::STATUS_REJECTED) {
                $this->fail("Application already rejected", "This application has already been rejected.", 409);
            }

            $this->markReviewed($application, ElectionApplication::STATUS_REJECTED, $reviewer, $remarks);
            // A previously approved application loses its candidacy.
            $application->candidate?->delete();

            return $application->fresh(self::RELATIONS);
        });
    }

    public function bulkReview(array $applicationIds, string $decision, object $currentSchool, Model $reviewer, ?string $remarks = null): array
    {
        return DB::transaction(fn() => collect($applicationIds)->map(fn($id) => $decision === ElectionApplication::STATUS_APPROVED
            ? $this->approveApplication($id, $currentSchool, $reviewer, $remarks)
            : $this->rejectApplication($id, $currentSchool, $reviewer, $remarks))->all());
    }

    // ------------------------------------------------------------ queries

    public function getApplications(object $currentSchool, array $filters = [])
    {
        return ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->when($filters['election_id'] ?? null, fn($q, $id) => $q->where('election_id', $id))
            ->when($filters['election_role_id'] ?? null, fn($q, $id) => $q->where('election_role_id', $id))
            ->when($filters['status'] ?? null, fn($q, $status) => $q->where('application_status', $status))
            ->when($filters['applicant_type'] ?? null, fn($q, $type) => $q->where('applicantable_type', ElectionParticipantType::classFor($type)))
            ->with(self::RELATIONS)
            ->latest()
            ->get()
            ->map(fn($application) => $this->present($application));
    }

    public function getApplicationDetails(string $applicationId, object $currentSchool): array
    {
        return $this->present($this->findInBranch(ElectionApplication::class, $applicationId, $currentSchool, self::RELATIONS));
    }

    public function getParticipantApplications(object $currentSchool, Model $participant, ?string $electionId = null)
    {
        return ElectionApplication::where('school_branch_id', $currentSchool->id)
            ->where('applicantable_type', $participant->getMorphClass())
            ->where('applicantable_id', $participant->getKey())
            ->when($electionId, fn($q) => $q->where('election_id', $electionId))
            ->with(self::RELATIONS)
            ->latest()
            ->get()
            ->map(fn($application) => $this->present($application));
    }

    public function present(ElectionApplication $application): array
    {
        return [
            'id' => $application->id,
            'election_id' => $application->election_id,
            'election_title' => $application->election?->title,
            'position' => $application->position,
            'applicant_type' => ElectionParticipantType::keyFor($application->applicantable_type),
            'applicant_id' => $application->applicantable_id,
            'applicant_name' => ElectionParticipantType::displayName($application->applicantable),
            'applicant' => $application->applicantable,
            'manifesto' => $application->manifesto,
            'personal_vision' => $application->personal_vision,
            'commitment_statement' => $application->commitment_statement,
            'application_status' => $application->application_status,
            'reviewed_at' => $application->reviewed_at,
            'review_remarks' => $application->review_remarks,
            'candidate_id' => $application->candidate?->id,
            'created_at' => $application->created_at,
        ];
    }

    // ------------------------------------------------------------ helpers

    private function resolveApplicant(array $data, object $currentSchool, Model $authUser): Model
    {
        if (!empty($data['applicant_type']) && !empty($data['applicant_id'])) {
            $isSelf = ElectionParticipantType::keyFor($authUser) === $data['applicant_type'] && $authUser->getKey() === $data['applicant_id'];
            if (!$isSelf && !$authUser instanceof Schooladmin) {
                $this->fail("Not allowed", "Only school administrators can apply on behalf of another participant.", 403);
            }
            return ElectionParticipantType::find($data['applicant_type'], $data['applicant_id'], $currentSchool->id);
        }

        if (!ElectionParticipantType::isSupported($authUser) || $authUser->school_branch_id !== $currentSchool->id) {
            $this->fail("Not allowed", "The authenticated user cannot apply for candidacy in this school branch.", 403);
        }
        return $authUser;
    }

    private function positionForElection(Election $election, string $positionId): ElectionRole
    {
        $position = ElectionRole::where('school_branch_id', $election->school_branch_id)
            ->where('election_type_id', $election->election_type_id)
            ->with('officeTerm')
            ->find($positionId);

        if (!$position) {
            $this->fail("Position not found", "The selected position does not belong to this election.", 404);
        }
        if ($position->status !== 'active') {
            $this->fail("Position inactive", "The position {$position->name} is not open for applications.", 409);
        }
        return $position;
    }

    private function reviewableApplication(string $applicationId, object $currentSchool): ElectionApplication
    {
        $application = $this->findInBranch(
            ElectionApplication::class,
            $applicationId,
            $currentSchool,
            ['election', 'position.officeTerm', 'applicantable', 'candidate'],
            true
        );
        $this->electionService->sync($application->election);

        if (!in_array($application->election->status, self::REVIEWABLE_STATUSES, true)) {
            $this->fail("Review closed", "Applications can only be reviewed before voting starts.", 409);
        }
        if ($application->application_status === ElectionApplication::STATUS_WITHDRAWN) {
            $this->fail("Application withdrawn", "This application was withdrawn by the applicant.", 409);
        }
        return $application;
    }

    private function markReviewed(ElectionApplication $application, string $status, Model $reviewer, ?string $remarks): void
    {
        $application->update([
            'application_status' => $status,
            'reviewed_at' => Carbon::now(),
            'reviewer_type' => $reviewer->getMorphClass(),
            'reviewer_id' => $reviewer->getKey(),
            'review_remarks' => $remarks,
        ]);
    }

    private function assertOwnerOrAdmin(ElectionApplication $application, Model $authUser): void
    {
        $isOwner = $application->applicantable_type === $authUser->getMorphClass()
            && $application->applicantable_id === $authUser->getKey();

        if (!$isOwner && !$authUser instanceof Schooladmin) {
            $this->fail("Not allowed", "Only the applicant or a school administrator can change this application.", 403);
        }
    }
}
