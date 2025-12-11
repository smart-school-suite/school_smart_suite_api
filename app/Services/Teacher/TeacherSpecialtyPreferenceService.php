<?php

namespace App\Services\Teacher;

use App\Exceptions\AppException;
use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Teacher;
use App\Events\Actions\AdminActionEvent;

class TeacherSpecialtyPreferenceService
{
    public function getTeacherPreference($teacherId, $currentSchool)
    {
        $preferences = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->with([
                'specailty:id,specialty_name,level_id',
                'specailty.level:id,name,level'
            ])
            ->get();

        if ($preferences->isEmpty()) {
            throw new AppException(
                "No specialty preferences found for teacher ID '{$teacherId}' at school branch ID '{$currentSchool->id}'.",
                404,
                "No Preferences Assigned 🧑‍🏫",
                "We couldn't find any defined specialty or subject preferences for this teacher. Please ensure their preferred teaching areas have been properly set up and saved.",
                null
            );
        }

        $formatted = $preferences->map(function ($preference) {
            $specialty = $preference->specailty;
            $level = $specialty->level;

            return [
                'id' => $preference->id,
                'specialty_id' => $specialty->id,
                'specialty_name' => $specialty->specialty_name,
                'level_name' => $level?->name,
                'level' => $level?->level,
            ];
        });

        return $formatted;
    }
    public function getAvailableSpecialtiesForTeacher($teacherId, $currentSchool)
    {
        $assignedSpecialtyIds = TeacherSpecailtyPreference::where("school_branch_id", $currentSchool->id)
            ->where("teacher_id", $teacherId)
            ->pluck("specialty_id");

        $availableSpecialties = Specialty::with('level:id,name,level')
            ->where("school_branch_id", $currentSchool->id)
            ->whereNotIn("id", $assignedSpecialtyIds)
            ->select("id", "specialty_name", "level_id")
            ->get()
            ->map(function ($specialty) {
                return [
                    'id' => $specialty->id,
                    'specialty_name' => $specialty->specialty_name,
                    'level_name' => $specialty->level?->name,
                    'level' => $specialty->level?->level,
                ];
            });

        if ($availableSpecialties->isEmpty()) {
            throw new AppException(
                "No additional specialties are available for teacher ID '{$teacherId}' to choose from at school branch ID '{$currentSchool->id}'.",
                404,
                "All Specialties Assigned ✅",
                "This teacher has already been assigned all available specialties in the system. There are no other specialties remaining to be added as a preference.",
                null
            );
        }

        return $availableSpecialties;
    }
    public function removeTeacherSpecialtyPreference($currentSchool, array $preferences, $authAdmin)
    {
        return DB::transaction(function () use ($currentSchool, $preferences, $authAdmin) {
            $preferenceIds = collect($preferences)->pluck('preference_id')->filter()->unique()->values();

            if ($preferenceIds->isEmpty()) {
                throw new AppException(
                    "No preferences selected",
                    400,
                    "Invalid Request",
                    "Please provide at least one preference ID to remove."
                );
            }

            $validPreferences = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
                ->whereIn('id', $preferenceIds)
                ->with('teacher')
                ->get();

            if ($validPreferences->isEmpty()) {
                throw new AppException(
                    "No valid specialty preferences found",
                    404,
                    "Preferences Not Found",
                    "None of the provided preference IDs exist or belong to your school branch."
                );
            }

            $teacherIds = $validPreferences->pluck('teacher_id')->unique();

            Teacher::whereIn('id', $teacherIds)
                ->where('school_branch_id', $currentSchool->id)
                ->lockForUpdate()
                ->get();

            $preferencesByTeacher = $validPreferences->groupBy('teacher_id');

            $deletedCount = 0;
            $affectedTeachers = [];

            foreach ($preferencesByTeacher as $teacherId => $prefs) {
                $countToDelete = $prefs->count();

                $deleted = TeacherSpecailtyPreference::whereIn('id', $prefs->pluck('id'))->delete();
                $deletedCount += $deleted;

                $teacher = $prefs->first()->teacher;

                $teacher->decrement('num_assigned_specialties', $countToDelete);

                if ($teacher->num_assigned_specialties <= 0) {
                    $teacher->num_assigned_specialties = 0;
                    $teacher->specialty_assignment_status = 'unassigned';
                    $teacher->save();
                }

                $affectedTeachers[] = [
                    'teacher_id'   => $teacher->id,
                    'teacher_name' => $teacher->name ?? trim("{$teacher->first_name} {$teacher->last_name}"),
                    'specialties_removed' => $countToDelete,
                    'remaining_specialties' => $teacher->num_assigned_specialties,
                    'status_changed' => $teacher->wasChanged('specialty_assignment_status'),
                ];
            }

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.teacherSpecialtyPreference.remove"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "teacherSpecialtyPreferenceManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'total_removed'      => $deletedCount,
                    'affected_teachers'  => $affectedTeachers,
                    'removed_preference_ids' => $validPreferences->pluck('id')->toArray(),
                ],
                "message" => "Teacher specialty preferences removed successfully",
            ]);

            return [
                'message'               => 'Specialty preferences removed successfully',
                'total_removed'         => $deletedCount,
                'affected_teacher_count' => count($affectedTeachers),
                'affected_teachers'     => $affectedTeachers,
            ];
        });
    }
    public function bulkAddTeacherSpecialtyPreference($currentSchool, array $data, $authAdmin): array
    {
        return DB::transaction(function () use ($currentSchool, $data, $authAdmin) {
            $teacherIds = collect($data['teacherIds'] ?? [])
                ->pluck('teacher_id')
                ->filter()
                ->unique()
                ->values();

            $specialtyIds = collect($data['specialtyIds'] ?? [])
                ->pluck('specialty_id')
                ->filter()
                ->unique()
                ->values();

            if ($teacherIds->isEmpty() || $specialtyIds->isEmpty()) {
                throw new AppException(
                    "Teacher IDs or Specialty IDs missing",
                    400,
                    "Incomplete Request",
                    "You must provide at least one teacher ID and one specialty ID for bulk assignment."
                );
            }

            $teachers = Teacher::whereIn('id', $teacherIds)
                ->where('school_branch_id', $currentSchool->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($teachers->count() !== $teacherIds->count()) {
                throw new AppException(
                    "Some teachers not found or don't belong to this school branch",
                    404,
                    "Invalid Teachers",
                    "One or more teacher IDs are invalid or not associated with your school."
                );
            }

            $existing = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
                ->whereIn('teacher_id', $teacherIds)
                ->whereIn('specialty_id', $specialtyIds)
                ->get(['teacher_id', 'specialty_id'])
                ->pluck('specialty_id', 'teacher_id');

            $insertData = [];
            $perTeacherNewCount = $teachers->mapWithKeys(fn($t) => [$t->id => 0])->toArray();
            $wasUnassigned = [];

            foreach ($teacherIds as $teacherId) {
                $teacher = $teachers->get($teacherId);
                $wasUnassigned[$teacherId] = $teacher->specialty_assignment_status === 'unassigned';

                foreach ($specialtyIds as $specialtyId) {
                    if ($existing->has($teacherId) && $existing->get($teacherId)->contains($specialtyId)) {
                        continue;
                    }

                    $insertData[] = [
                        'id'               => Str::uuid()->toString(),
                        'teacher_id'       => $teacherId,
                        'specialty_id'     => $specialtyId,
                        'school_branch_id' => $currentSchool->id,
                        'created_at'       => now(),
                        'updated_at'       => now(),
                    ];

                    $perTeacherNewCount[$teacherId]++;
                }
            }

            if (empty($insertData)) {
                throw new AppException(
                    "No new preferences to add",
                    409,
                    "All Already Assigned",
                    "All selected teacher-specialty combinations already exist."
                );
            }

            TeacherSpecailtyPreference::insert($insertData);

            $affectedTeachersSummary = [];

            foreach ($perTeacherNewCount as $teacherId => $newCount) {
                if ($newCount === 0) continue;

                $teacher = $teachers->get($teacherId);

                $teacher->increment('num_assigned_specialties', $newCount);

                if ($wasUnassigned[$teacherId]) {
                    $teacher->specialty_assignment_status = 'assigned';
                    $teacher->save();
                }

                $affectedTeachersSummary[] = [
                    'teacher_id'     => $teacher->id,
                    'teacher_name'   => $teacher->name ?? trim("{$teacher->first_name} {$teacher->last_name}"),
                    'new_specialties_added' => $newCount,
                    'total_specialties'     => $teacher->num_assigned_specialties,
                    'status_changed_to_assigned' => $wasUnassigned[$teacherId],
                ];
            }

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.teacherSpecialtyPreference.bulkAssign"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "teacherSpecialtyPreferenceManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'total_preferences_added' => count($insertData),
                    'affected_teachers_count' => count($affectedTeachersSummary),
                    'affected_teachers'       => $affectedTeachersSummary,
                    'specialty_ids'           => $specialtyIds->toArray(),
                    'teacher_ids'             => $teacherIds->toArray(),
                ],
                "message" => "Bulk teacher specialty preferences assigned successfully",
            ]);

            return [
                'message'                  => 'Bulk assignment completed successfully',
                'total_preferences_added'  => count($insertData),
                'affected_teachers_count'  => count($affectedTeachersSummary),
                'affected_teachers'        => $affectedTeachersSummary,
                'skipped_duplicates'      => count($teacherIds) * count($specialtyIds) - count($insertData),
            ];
        });
    }
    public function bulkRemoveTeacherSpecialtyPreference($currentSchool, array $data, $authAdmin): array
    {
        return DB::transaction(function () use ($currentSchool, $data, $authAdmin) {
            $teacherIds = collect($data['teacherIds'] ?? [])
                ->pluck('teacher_id')
                ->filter()
                ->unique()
                ->values();

            $specialtyIds = collect($data['specialtyIds'] ?? [])
                ->pluck('specialty_id')
                ->filter()
                ->unique()
                ->values();

            if ($teacherIds->isEmpty() || $specialtyIds->isEmpty()) {
                throw new AppException(
                    "Teacher IDs or Specialty IDs missing",
                    400,
                    "Incomplete Request",
                    "You must provide at least one teacher ID and one specialty ID for bulk removal."
                );
            }

            $teachers = Teacher::whereIn('id', $teacherIds)
                ->where('school_branch_id', $currentSchool->id)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($teachers->count() !== $teacherIds->count()) {
                throw new AppException(
                    "Some teachers not found or do not belong to this school branch",
                    404,
                    "Invalid Teachers",
                    "One or more teacher IDs are invalid or not associated with your school."
                );
            }

            $preferencesToDelete = TeacherSpecailtyPreference::where('school_branch_id', $currentSchool->id)
                ->whereIn('teacher_id', $teacherIds)
                ->whereIn('specialty_id', $specialtyIds)
                ->get();

            if ($preferencesToDelete->isEmpty()) {
                throw new AppException(
                    "No matching specialty preferences found to remove",
                    404,
                    "Nothing to Remove",
                    "None of the selected teacher-specialty combinations exist in your school branch."
                );
            }

            $preferencesByTeacher = $preferencesToDelete->groupBy('teacher_id');
            $deletedCount = $preferencesToDelete->count();

            $affectedTeachersSummary = [];

            foreach ($preferencesByTeacher as $teacherId => $prefs) {
                $removeCount = $prefs->count();
                $teacher = $teachers->get($teacherId);

                $teacher->decrement('num_assigned_specialties', $removeCount);

                $becameUnassigned = false;
                if ($teacher->num_assigned_specialties <= 0) {
                    $teacher->num_assigned_specialties = 0;
                    $teacher->specialty_assignment_status = 'unassigned';
                    $teacher->save();
                    $becameUnassigned = true;
                }

                $affectedTeachersSummary[] = [
                    'teacher_id'       => $teacher->id,
                    'teacher_name'     => $teacher->name ?? trim("{$teacher->first_name} {$teacher->last_name}"),
                    'specialties_removed' => $removeCount,
                    'remaining_specialties' => $teacher->num_assigned_specialties,
                    'status_changed_to_unassigned' => $becameUnassigned,
                ];
            }

            TeacherSpecailtyPreference::whereIn('id', $preferencesToDelete->pluck('id'))->delete();

            AdminActionEvent::dispatch([
                "permissions"  => ["schoolAdmin.teacherSpecialtyPreference.bulkRemove"],
                "roles"        => ["schoolSuperAdmin", "schoolAdmin"],
                "schoolBranch" => $currentSchool->id,
                "feature"      => "teacherSpecialtyPreferenceManagement",
                "authAdmin"    => $authAdmin,
                "data"         => [
                    'total_preferences_removed' => $deletedCount,
                    'affected_teachers_count'   => count($affectedTeachersSummary),
                    'affected_teachers'         => $affectedTeachersSummary,
                    'removed_teacher_ids'       => $teacherIds->toArray(),
                    'removed_specialty_ids'     => $specialtyIds->toArray(),
                ],
                "message" => "Bulk teacher specialty preferences removed successfully",
            ]);

            return [
                'message'                    => 'Bulk removal completed successfully',
                'total_preferences_removed'  => $deletedCount,
                'affected_teachers_count'    => count($affectedTeachersSummary),
                'affected_teachers'          => $affectedTeachersSummary,
            ];
        });
    }
}
