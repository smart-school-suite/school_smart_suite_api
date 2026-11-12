<?php

namespace App\Services\Election;

use App\Models\Election\ElectionType;
use App\Services\Election\Support\FindsElectionRecords;
use Illuminate\Support\Facades\DB;

class ElectionTypeService
{
    use FindsElectionRecords;

    public function createElectionType(array $data, object $currentSchool): ElectionType
    {
        $this->assertUniqueTitle($data['election_title'], $currentSchool);

        return ElectionType::create([
            'election_title' => $data['election_title'],
            'description' => $data['description'] ?? '',
            'status' => 'active',
            'school_branch_id' => $currentSchool->id,
        ]);
    }

    public function updateElectionType(array $data, string $electionTypeId, object $currentSchool): ElectionType
    {
        $electionType = $this->findInBranch(ElectionType::class, $electionTypeId, $currentSchool);

        if (isset($data['election_title']) && $data['election_title'] !== $electionType->election_title) {
            $this->assertUniqueTitle($data['election_title'], $currentSchool, $electionType->id);
        }

        $electionType->update(array_filter($data, fn($value) => $value !== null));
        return $electionType->fresh();
    }

    public function bulkUpdateElectionTypes(array $items, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($items)->map(
            fn($item) => $this->updateElectionType(collect($item)->except('election_type_id')->all(), $item['election_type_id'], $currentSchool)
        )->all());
    }

    public function deleteElectionType(string $electionTypeId, object $currentSchool): ElectionType
    {
        $electionType = $this->findInBranch(ElectionType::class, $electionTypeId, $currentSchool);

        if ($electionType->elections()->exists()) {
            $this->fail(
                "Election type in use",
                "This election type has elections attached to it. Deactivate it instead to preserve election history.",
                409
            );
        }

        DB::transaction(function () use ($electionType) {
            $electionType->positions()->each(function ($position) {
                $position->officeTerm()->delete();
                $position->delete();
            });
            $electionType->delete();
        });

        return $electionType;
    }

    public function getElectionTypes(object $currentSchool)
    {
        return ElectionType::where('school_branch_id', $currentSchool->id)
            ->withCount(['positions', 'elections'])
            ->orderBy('election_title')
            ->get();
    }

    public function getActiveElectionTypes(object $currentSchool)
    {
        return ElectionType::where('school_branch_id', $currentSchool->id)
            ->where('status', 'active')
            ->with(['positions' => fn($q) => $q->where('status', 'active')])
            ->orderBy('election_title')
            ->get();
    }

    public function getElectionTypeDetails(string $electionTypeId, object $currentSchool): ElectionType
    {
        return $this->findInBranch(ElectionType::class, $electionTypeId, $currentSchool, ['positions.officeTerm'])
            ->loadCount('elections');
    }

    public function setStatus(array $electionTypeIds, string $status, object $currentSchool): array
    {
        return DB::transaction(fn() => collect($electionTypeIds)->map(function ($id) use ($status, $currentSchool) {
            $electionType = $this->findInBranch(ElectionType::class, $id, $currentSchool);
            $electionType->update(['status' => $status]);
            return $electionType;
        })->all());
    }

    private function assertUniqueTitle(string $title, object $currentSchool, ?string $ignoreId = null): void
    {
        $exists = ElectionType::where('school_branch_id', $currentSchool->id)
            ->where('election_title', $title)
            ->when($ignoreId, fn($q) => $q->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            $this->fail("Election type already exists", "An election type titled '{$title}' already exists for this school branch.", 409);
        }
    }
}
