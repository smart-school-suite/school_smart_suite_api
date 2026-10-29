<?php

namespace App\Services\Hall;

use App\Models\Hall;
use App\Models\SpecialtyHall;
use App\Models\Specialty;
use App\Exceptions\AppException;
use Illuminate\Support\Facades\DB;

class SpecialtyHallService
{
    public function assignHallToSpecialty(object $currentSchool, array $data, object $authAdmin)
    {
        $hallIds = collect($data['hallIds'] ?? [])->pluck('hall_id')->unique()->values();

        if ($hallIds->isEmpty()) {
            throw new AppException("No valid hall IDs provided", 422, "Invalid Input");
        }

        return DB::transaction(function () use ($currentSchool, $data, $authAdmin, $hallIds) {

            $halls = Hall::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $hallIds)
                ->lockForUpdate()
                ->get();

            if ($halls->count() !== $hallIds->count()) {
                throw new AppException(
                    "One or more halls were not found or do not belong to this school branch",
                    404,
                    "Hall(s) Not Found"
                );
            }

            $specialty = Specialty::where('school_branch_id', $currentSchool->id)
                ->with('level')
                ->find($data['specialty_id']);

            if (!$specialty) {
                throw new AppException(
                    "Specialty not found in this school branch",
                    404,
                    "Specialty Not Found"
                );
            }

            $createdAssignments = collect();

            foreach ($halls as $hall) {
                $alreadyAssigned = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                    ->where('hall_id', $hall->id)
                    ->where('specialty_id', $specialty->id)
                    ->where('level_id', $specialty->level_id)
                    ->exists();

                if ($alreadyAssigned) {
                    continue;
                }

                $assignment = SpecialtyHall::create([
                    'school_branch_id' => $currentSchool->id,
                    'specialty_id'     => $specialty->id,
                    'level_id'         => $specialty->level_id,
                    'hall_id'          => $hall->id,
                ]);

                $createdAssignments->push($assignment);

                $wasUnassigned = $hall->assignment_status === 'unassigned';
                $hall->increment('num_assigned_specialties');

                if ($wasUnassigned) {
                    $hall->assignment_status = 'assigned';
                    $hall->saveQuietly();
                }
            }

            $wasSpecialtyUnassigned = $specialty->hall_assignment_status === 'unassigned';
            $specialty->increment('num_assigned_hall', $createdAssignments->count());

            if ($wasSpecialtyUnassigned && $createdAssignments->isNotEmpty()) {
                $specialty->hall_assignment_status = 'assigned';
                $specialty->saveQuietly();
            }

            return [
                'specialty'     => $specialty,
                'assignments'   => $createdAssignments,
                'halls'         => $halls,
            ];
        });
    }
    public function getAvailableAssignableHalls(object $currentSchool, string $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->find($specialtyId);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are trying to Assign Was Not Found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                null
            );
        }
        $assignedHalls = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialtyId)
            ->where("level_id", $specialty->level->id)
            ->pluck('hall_id')->toArray();

        $assignableHalls = Hall::where("school_branch_id", $currentSchool->id)
            ->with(['types'])
            ->whereNotIn("id", $assignedHalls)
            ->get();

        if ($assignableHalls->isEmpty()) {
            throw new AppException(
                "No Assignable Halls Left",
                404,
                "No Assignable Halls Found",
                "We Could not find any assignable halls left looks like all the halls have already been assigned"
            );
        }

        return $assignableHalls;
    }
    public function getAssignedHalls(object $currentSchool, string $specialtyId)
    {
        $specialty = Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level'])
            ->find($specialtyId);

        if (!$specialty) {
            throw new AppException(
                "The specialty you are trying to Assign Was Not Found.",
                404,
                "Specialty Not Found",
                "We could not find the specialty with the provided ID for this school. It may have already been deleted.",
                null
            );
        }

        $assignedHalls = SpecialtyHall::where("school_branch_id", $currentSchool->id)
            ->where("specialty_id", $specialty->id)
            ->with(['hall.types'])
            ->get();


        if ($assignedHalls->isEmpty()) {
            throw new AppException(
                "No Halls Assigned",
                404,
                "No Halls Assigned",
                "We Could not find any Halls Assigned  looks like no halls have beeen assigned to {$specialty->specialty_name}, {$specialty->level->level_name}"
            );
        }

        return $assignedHalls->map(fn($assignedHall) => $assignedHall->hall);
    }
    public function removeAssignedHalls(object $currentSchool, array $data, object $authAdmin)
    {
        $hallIds = collect($data['hallIds'])->pluck('hall_id')->unique()->values();
        $specialtyId = $data['specialty_id'];

        if ($hallIds->isEmpty()) {
            throw new AppException("No hall IDs provided", 422, "Invalid Input");
        }

        return DB::transaction(function () use ($currentSchool, $hallIds, $specialtyId, $authAdmin) {

            $assignments = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $specialtyId)
                ->whereIn('hall_id', $hallIds)
                ->with(['hall', 'specialty.level'])
                ->lockForUpdate()
                ->get();

            if ($assignments->isEmpty()) {
                throw new AppException(
                    "No matching hall assignments found for this specialty",
                    404,
                    "Assignment Not Found"
                );
            }

            $deletedCount = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $specialtyId)
                ->whereIn('hall_id', $hallIds)
                ->delete();

            $affectedHallIds = $assignments->pluck('hall_id');

            Hall::whereIn('id', $affectedHallIds)
                ->where('school_branch_id', $currentSchool->id)
                ->decrement('num_assigned_specialties');

            Hall::whereIn('id', $affectedHallIds)
                ->where('school_branch_id', $currentSchool->id)
                ->where('num_assigned_specialties', '<=', 0)
                ->update([
                    'num_assigned_specialties' => 0,
                    'assignment_status' => 'unassigned'
                ]);

            $specialty = Specialty::find($specialtyId);

            if ($specialty) {
                $remaining = SpecialtyHall::where('specialty_id', $specialtyId)
                    ->where('school_branch_id', $currentSchool->id)
                    ->count();

                if ($remaining === 0) {
                    $specialty->update([
                        'num_assigned_hall' => 0,
                        'hall_assignment_status' => 'unassigned'
                    ]);
                } else {
                    $specialty->decrement('num_assigned_hall', $deletedCount);
                }
            }

            $hallsData = $assignments->map(function ($item) {
                return [
                    'hall'      => $item->hall?->name ?? 'Unknown',
                    'hall_id'   => $item->hall_id,
                    'specialty' => $item->specialty?->specialty_name ?? 'Unknown',
                    'level'     => $item->specialty?->level?->level_name ?? 'Unknown'
                ];
            })->all();


            return $deletedCount;
        });
    }
    public function removeAllAssignedHalls(object $currentSchool, string $specialtyId, object $authAdmin): int
    {
        return DB::transaction(function () use ($currentSchool, $specialtyId, $authAdmin) {

            $specialtyHalls = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $specialtyId)
                ->lockForUpdate()
                ->get(['id', 'hall_id']);

            if ($specialtyHalls->isEmpty()) {
                throw new AppException(
                    "No halls are currently assigned to this specialty.",
                    404,
                    "No Assignments Found",
                    "There are no hall assignments to remove for this specialty in the current school branch."
                );
            }

            $deletedCount = SpecialtyHall::where('school_branch_id', $currentSchool->id)
                ->where('specialty_id', $specialtyId)
                ->delete();

            $affectedHallIds = $specialtyHalls->pluck('hall_id')->unique();

            if ($affectedHallIds->isNotEmpty()) {
                Hall::whereIn('id', $affectedHallIds)
                    ->where('school_branch_id', $currentSchool->id)
                    ->decrement('num_assigned_specialties');

                Hall::whereIn('id', $affectedHallIds)
                    ->where('school_branch_id', $currentSchool->id)
                    ->where('num_assigned_specialties', '<=', 0)
                    ->update([
                        'num_assigned_specialties' => 0,
                        'assignment_status'        => 'unassigned'
                    ]);
            }

            $specialty = Specialty::where('id', $specialtyId)
                ->where('school_branch_id', $currentSchool->id)
                ->first();

            if ($specialty) {
                $specialty->update([
                    'num_assigned_hall'      => 0,
                    'hall_assignment_status' => 'unassigned',
                ]);
            }

            return $deletedCount;
        });
    }

    public function getSpecialties(object $currentSchool)
    {
        return Specialty::where("school_branch_id", $currentSchool->id)
            ->with(['level', 'department'])
            ->withCount('specialtyHall')
            ->get()
            ->map(function ($specialty) {
                $assignedCount = $specialty->specialty_hall_count;
                return [
                    'id' => $specialty->id,
                    'specialty' => $specialty->specialty_name,
                    'description' => $specialty->description,
                    'status' => $specialty->status,
                    'level_name' => $specialty->level?->name ?? '—',
                    'level' => $specialty->level->level ?? "-",
                    'department' => $specialty->department?->department_name ?? '—',
                    'assigned_hall_count' => $assignedCount,
                    'assignment_status' => $assignedCount > 0 ? 'assigned' : 'not_assigned',
                    'created_at' => $specialty->created_at,
                    'updated_at' => $specialty->updated_at,
                ];
            });
    }
}
