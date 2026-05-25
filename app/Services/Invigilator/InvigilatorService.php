<?php

namespace App\Services\Invigilator;

use App\Exceptions\AppException;
use App\Models\ExamTimetable\Invigilator;
use App\Models\Schooladmin;
use App\Models\Teacher;

class InvigilatorService
{
    protected const TEACHER = "teacher";
    protected const SCHOOLADMIN = "school_admin";
    public function createInvigilator(array $payload, object $currentSchool)
    {
        $invigilators = $payload['invigilators'];
        $createdInvigilators = [];
        $duplicatesSkipped = [];

        $existingInvigilators = Invigilator::where('school_branch_id', $currentSchool->id)
            ->get(['invigilatable_type', 'invigilatable_id'])
            ->keyBy(function ($item) {
                return $item->invigilatable_type . '|' . $item->invigilatable_id;
            });

        $schoolAdminIds = [];
        $teacherIds = [];

        foreach ($invigilators as $invigilator) {
            $actorType = $invigilator["actor_type"] ?? null;

            if ($actorType === self::SCHOOLADMIN) {
                $schoolAdminIds[] = $invigilator["actor_id"];
            } elseif ($actorType === self::TEACHER) {
                $teacherIds[] = $invigilator["actor_id"];
            }
        }

        $schoolAdmins = !empty($schoolAdminIds)
            ? Schooladmin::where("school_branch_id", $currentSchool->id)
            ->whereIn('id', $schoolAdminIds)
            ->get()
            ->keyBy('id')
            : collect();

        $teachers = !empty($teacherIds)
            ? Teacher::where("school_branch_id", $currentSchool->id)
            ->whereIn('id', $teacherIds)
            ->get()
            ->keyBy('id')
            : collect();

        foreach ($invigilators as $invigilator) {
            $actorId = $invigilator["actor_id"];
            $actorType = $invigilator["actor_type"];

            try {
                $model = null;
                $modelClass = null;

                if ($actorType === self::SCHOOLADMIN) {
                    $model = $schoolAdmins->get($actorId);
                    $modelClass = Schooladmin::class;
                } elseif ($actorType === self::TEACHER) {
                    $model = $teachers->get($actorId);
                    $modelClass = Teacher::class;
                } else {
                    throw new AppException(
                        "invalid_actor_type",
                        400,
                        "Invalid actor type provided",
                        "The actor type '{$actorType}' is not supported. Valid types are: " . implode(', ', [self::SCHOOLADMIN, self::TEACHER]),
                        ['supported_types' => [self::SCHOOLADMIN, self::TEACHER]]
                    );
                }

                if (!$model) {
                    throw new AppException(
                        "actor_not_found",
                        404,
                        "Resource not found",
                        "The requested {$actorType} does not exist or does not belong to this school branch",
                        null
                    );
                }

                $compositeKey = $modelClass . '|' . $model->id;
                if ($existingInvigilators->has($compositeKey)) {
                    $duplicatesSkipped[] = [
                        'actor_id' => $actorId,
                        'type' => $actorType,
                        'name' => $model->name ?? null
                    ];
                    continue;
                }

                $invigilatorRecord = Invigilator::create([
                    'invigilatable_type' => $modelClass,
                    'invigilatable_id' => $model->id,
                    'school_branch_id' => $currentSchool->id,
                ]);

                $createdInvigilators[] = $invigilatorRecord;
            } catch (AppException $e) {
                throw $e;
            } catch (\Exception $e) {
                throw new AppException(
                    "invigilator_creation_failed",
                    500,
                    "Unable to create invigilator",
                    "An unexpected error occurred while creating the invigilator",
                    null
                );
            }
        }

        return [
            'created' => count($createdInvigilators),
            'duplicates_skipped' => count($duplicatesSkipped),
        ];
    }
    public function getPotentialInvigilators(object $currentSchool, array $authUser)
    {
        $existingInvigilatorKeys = Invigilator::where('school_branch_id', $currentSchool->id)
            ->get(['invigilatable_type', 'invigilatable_id'])
            ->map(function ($invigilator) {
                return $invigilator->invigilatable_type . '|' . $invigilator->invigilatable_id;
            })
            ->flip();

        $authUserId = $authUser['authUser']->id ?? null;
        $authUserType = get_class($authUser['authUser']);

        $schoolAdmins = Schooladmin::where("school_branch_id", $currentSchool->id)
            ->get(['id', 'name', 'profile_picture'])
            ->filter(function ($admin) use ($existingInvigilatorKeys) {
                $key = Schooladmin::class . '|' . $admin->id;
                return !isset($existingInvigilatorKeys[$key]);
            })
            ->map(function ($admin) use ($authUserId, $authUserType) {
                return [
                    "id" => $admin->id,
                    "name" => $admin->name,
                    "profile_picture" => $admin->profile_picture,
                    "me" => ($authUserType === Schooladmin::class && $authUserId === $admin->id),
                    "type" => "school_admin",
                    "is_current_invigilator" => false
                ];
            });

        $teachers = Teacher::where("school_branch_id", $currentSchool->id)
            ->get(['id', 'name', 'profile_picture'])
            ->filter(function ($teacher) use ($existingInvigilatorKeys) {
                $key = Teacher::class . '|' . $teacher->id;
                return !isset($existingInvigilatorKeys[$key]);
            })
            ->map(function ($teacher) use ($authUserId, $authUserType) {
                return [
                    "id" => $teacher->id,
                    "name" => $teacher->name,
                    "profile_picture" => $teacher->profile_picture,
                    "me" => ($authUserType === Teacher::class && $authUserId === $teacher->id),
                    "type" => "teacher",
                    "is_current_invigilator" => false
                ];
            });

        return $schoolAdmins->concat($teachers)->values();
    }
    public function getInvigilators(object $currentSchool, array $authUser)
    {
        $authUserId = $authUser['authUser']->id ?? null;
        $authUserType = get_class($authUser['authUser'] ?? null);

        return Invigilator::where("school_branch_id", $currentSchool->id)
            ->with(["invigilatable"])
            ->get()
            ->map(function ($invigilator) use ($authUserId, $authUserType) {
                $invigilatable = $invigilator->invigilatable;

                $type = match (get_class($invigilatable)) {
                    Schooladmin::class => "school_admin",
                    Teacher::class => "teacher",
                    default => "unknown"
                };

                return [
                    "id" => $invigilatable->id,
                    "name" => $invigilatable->name,
                    "profile_picture" => $invigilatable->profile_picture,
                    "me" => ($authUserType === get_class($invigilatable) && $authUserId === $invigilatable->id),
                    "type" => $type,
                    "invigilator_id" => $invigilator->id
                ];
            })
            ->values();
    }
    public function removeInvigilators(array $payload, object $currentSchool): array
    {
        $invigilatorIds = $payload["invigilatorIds"] ?? [];

        if (empty($invigilatorIds)) {
            return [
                'removed' => 0,
                'not_found' => 0,
                'message' => 'No invigilator IDs provided'
            ];
        }

        $invigilatorsToDelete = Invigilator::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $invigilatorIds)
            ->get();

        $foundIds = $invigilatorsToDelete->pluck('id')->toArray();
        $notFoundIds = array_diff($invigilatorIds, $foundIds);

        if ($invigilatorsToDelete->isEmpty()) {
            throw new AppException(
                "invigilators_not_found",
                404,
                "Invigilators not found",
                "None of the specified invigilators exist in this school branch",
                ['provided_ids' => $invigilatorIds]
            );
        }

        // Perform the deletion
        $deletedCount = Invigilator::where('school_branch_id', $currentSchool->id)
            ->whereIn('id', $foundIds)
            ->delete();


        return [
            'removed' => $deletedCount,
            'not_found' => count($notFoundIds),
            'not_found_ids' => $notFoundIds, // Optional: remove if not needed in response
            'message' => $deletedCount > 0
                ? "Successfully removed {$deletedCount} invigilator(s)"
                : "No invigilators were removed"
        ];
    }
}
