<?php

namespace App\Services\Election;

use App\Models\Election\Election;
use App\Models\Election\ElectionOfficeTerm;
use App\Models\Election\ElectionRole;
use App\Models\Election\ElectionType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

/**
 * Election positions (stored in election_roles) and their office terms.
 */
class ElectionRoleService
{
    use FindsElectionRecords;

    public function createElectionRole(array $data, object $currentSchool): ElectionRole
    {
        $this->findInBranch(ElectionType::class, $data['election_type_id'], $currentSchool);
        $this->assertUniqueName($data['name'], $data['election_type_id'], $currentSchool);

        return DB::transaction(function () use ($data, $currentSchool) {
            $position = ElectionRole::create([
                'name' => $data['name'],
                'description' => $data['description'] ?? '',
                'seats' => $data['seats'] ?? 1,
                'status' => 'active',
                'election_type_id' => $data['election_type_id'],
                'school_branch_id' => $currentSchool->id,
            ]);

            if (!empty($data['office_term'])) {
                $this->saveOfficeTerm($position, $data['office_term']);
            }

            return $position->load('officeTerm');
        });
    }

    public function updateElectionRole(array $data, string $electionRoleId, object $currentSchool): ElectionRole
    {
        $position = $this->findInBranch(ElectionRole::class, $electionRoleId, $currentSchool);
        $attributes = Arr::only(array_filter($data, fn($v) => $v !== null), ['name', 'description', 'seats', 'election_type_id']);

        if (isset($attributes['election_type_id']) && $attributes['election_type_id'] !== $position->election_type_id) {
            $this->findInBranch(ElectionType::class, $attributes['election_type_id'], $currentSchool);
            if ($position->candidates()->exists()) {
                $this->fail("Position in use", "This position already has candidates and cannot be moved to another election type.", 409);
            }
        }

        if (isset($attributes['name']) && $attributes['name'] !== $position->name) {
            $this->assertUniqueName($attributes['name'], $attributes['election_type_id'] ?? $position->election_type_id, $currentSchool, $position->id);
        }

        if (isset($attributes['seats']) && (int) $attributes['seats'] !== $position->seats) {
            $this->assertNoOpenVoting($position);
        }

        return DB::transaction(function () use ($position, $attributes, $data) {
            $position->update($attributes);
            if (!empty($data['office_term'])) {
                $this->saveOfficeTerm($position, $data['office_term']);
            }
            return $position->fresh('officeTerm');
        });
    }

    public function bulkUpdateElectionRoles(array $items, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($items)->map(
            fn($item) => $this->updateElectionRole(Arr::except($item, 'election_role_id'), $item['election_role_id'], $currentSchool)
        )->all());
    }

    public function deleteElectionRole(string $electionRoleId, object $currentSchool): ElectionRole
    {
        $position = $this->findInBranch(ElectionRole::class, $electionRoleId, $currentSchool);

        if ($position->applications()->exists() || $position->officeHolders()->exists()) {
            $this->fail(
                "Position in use",
                "This position has applications or office holders. Deactivate it instead to preserve election history.",
                409
            );
        }

        DB::transaction(function () use ($position) {
            $position->officeTerm()->delete();
            $position->delete();
        });

        return $position;
    }

    public function bulkDeleteElectionRoles(array $electionRoleIds, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($electionRoleIds)
            ->map(fn($id) => $this->deleteElectionRole($id, $currentSchool))->all());
    }

    public function setStatus(array $electionRoleIds, string $status, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($electionRoleIds)->map(function ($id) use ($status, $currentSchool) {
            $position = $this->findInBranch(ElectionRole::class, $id, $currentSchool);
            if ($status === 'inactive') {
                $this->assertNoOpenVoting($position);
            }
            $position->update(['status' => $status]);
            return $position;
        })->all());
    }

    public function getElectionRoles(object $currentSchool, ?string $electionTypeId = null)
    {
        return ElectionRole::where('school_branch_id', $currentSchool->id)
            ->when($electionTypeId, fn($q) => $q->where('election_type_id', $electionTypeId))
            ->with(['electionType:id,election_title', 'officeTerm'])
            ->orderBy('name')
            ->get();
    }

    public function getElectionRolesByElection(object $currentSchool, string $electionId, bool $activeOnly = false)
    {
        $election = $this->findInBranch(Election::class, $electionId, $currentSchool);

        return ElectionRole::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $election->election_type_id)
            ->when($activeOnly, fn($q) => $q->where('status', 'active'))
            ->with('officeTerm')
            ->withCount([
                'candidates as candidates_count' => fn($q) => $q->where('election_id', $election->id),
                'applications as applications_count' => fn($q) => $q->where('election_id', $election->id),
            ])
            ->orderBy('name')
            ->get();
    }

    public function getElectionRoleDetails(string $electionRoleId, object $currentSchool): ElectionRole
    {
        return $this->findInBranch(ElectionRole::class, $electionRoleId, $currentSchool, [
            'electionType',
            'officeTerm',
            'currentOfficeHolders.holderable',
        ]);
    }

    // ------------------------------------------------------- office terms

    public function saveOfficeTerm(ElectionRole $position, array $data): ElectionOfficeTerm
    {
        $allowReelection = filter_var($data['allow_reelection'] ?? false, FILTER_VALIDATE_BOOLEAN);

        return ElectionOfficeTerm::updateOrCreate(
            ['school_branch_id' => $position->school_branch_id, 'election_role_id' => $position->id],
            [
                'duration_academic_years' => $data['duration_academic_years'] ?? 1,
                'allow_reelection' => $allowReelection,
                // Without re-election a holder can only ever serve one term.
                'maximum_terms' => $allowReelection ? ($data['maximum_terms'] ?? null) : 1,
            ]
        );
    }

    public function configureOfficeTerm(string $electionRoleId, array $data, object $currentSchool): ElectionOfficeTerm
    {
        $position = $this->findInBranch(ElectionRole::class, $electionRoleId, $currentSchool);
        return $this->saveOfficeTerm($position, $data);
    }

    public function getOfficeTerms(object $currentSchool)
    {
        return ElectionOfficeTerm::where('school_branch_id', $currentSchool->id)
            ->with('position:id,name,election_type_id,seats,status')
            ->get();
    }

    // ------------------------------------------------------------ helpers

    private function assertUniqueName(string $name, string $electionTypeId, object $currentSchool, ?string $ignoreId = null): void
    {
        $exists = ElectionRole::where('school_branch_id', $currentSchool->id)
            ->where('election_type_id', $electionTypeId)
            ->where('name', $name)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            $this->fail("Position already exists", "A position named '{$name}' already exists for this election type.", 409);
        }
    }

    private function assertNoOpenVoting(ElectionRole $position): void
    {
        $voting = Election::where('school_branch_id', $position->school_branch_id)
            ->where('election_type_id', $position->election_type_id)
            ->where('status', Election::STATUS_VOTING_OPEN)
            ->exists();

        if ($voting) {
            $this->fail("Voting in progress", "This position cannot be changed while an election using it is open for voting.", 409);
        }
    }
}
