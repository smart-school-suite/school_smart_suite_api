<?php

namespace App\Services\JointCourse;

use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Specialty;
use App\Models\Teacher;
use Illuminate\Support\Collection;

class JointCourseAssignmentService
{
    public const GROUP_ELIGIBLE = 'eligible';
    public const GROUP_INELIGIBLE = 'ineligible';
    public function getAssignment(string $jointCourseId, object $currentSchool): array
    {
        $course = Courses::where('school_branch_id', $currentSchool->id)
            ->with([
                'semester',
                'specialties.department',
                'specialties.level',
                'teacherCoursePreference' => fn($query) => $query->orderBy('created_at'),
            ])
            ->find($jointCourseId);

        if (!$course) {
            throw new AppException(
                "Joint course {$jointCourseId} not found.",
                404,
                'Course Not Found',
                'The course you are trying to manage does not exist or has already been deleted.',
                null
            );
        }

        $specialtyIds = $course->specialties->pluck('id')->all();
        $departmentIds = $course->specialties->pluck('department_id')->filter()->unique()->values()->all();
        // A course has one teacher for now; if older data holds more, the first assigned counts.
        $currentTeacherId = $course->teacherCoursePreference->first()?->teacher_id;

        $teachers = Teacher::where('school_branch_id', $currentSchool->id)
            ->with(['specialties.department', 'qualifications', 'currentAvatar'])
            ->withCount('teacherCoursePreference as course_count')
            ->orderBy('name')
            ->get()
            ->map(fn(Teacher $teacher) => $this->presentTeacher($teacher, $specialtyIds, $departmentIds, $currentTeacherId));

        $sorted = $teachers
            ->sortBy(fn(array $teacher) => [
                $teacher['is_current'] ? 0 : 1,
                $teacher['eligible'] ? 0 : 1,
                -$teacher['matching_specialty_count'],
                $teacher['course_count'],
                $teacher['name'],
            ])
            ->values();

        return [
            'course' => $this->presentCourse($course),
            'current_teacher' => $sorted->firstWhere('is_current', true),
            'teachers' => $sorted->all(),
            'summary' => [
                'teacher_count' => $sorted->count(),
                'eligible_count' => $sorted->where('eligible', true)->count(),
                'is_assigned' => $currentTeacherId !== null,
            ],
        ];
    }

    private function presentCourse(Courses $course): array
    {
        $specialties = $course->specialties;
        return [
            'id' => $course->id,
            'code' => $course->course_code ?? null,
            'title' => $course->course_title ?? null,
            'credit' => $course->credit ?? null,
            'description' => $course->description ?? null,
            'type' => 'joint',
            'type_label' => 'Joint Course',
            'semester' => $course->semester
                ? ['id' => $course->semester->id, 'name' => $course->semester->name]
                : null,
            'levels' => $specialties->pluck('level')->filter()->unique('id')
                ->map(fn($level) => ['id' => $level->id, 'name' => $level->name])
                ->values()->all(),
            'departments' => $specialties->pluck('department')->filter()->unique('id')
                ->map(fn($department) => ['id' => $department->id, 'name' => $department->department_name])
                ->values()->all(),
            'specialties' => $specialties
                ->map(fn(Specialty $specialty) => [
                    'id' => $specialty->id,
                    'name' => $specialty->specialty_name,
                    'level_name' => $specialty->level?->name,
                    'department_name' => $specialty->department?->department_name,
                ])
                ->values()->all(),
        ];
    }

    private function presentTeacher(Teacher $teacher, array $specialtyIds, array $departmentIds, ?string $currentTeacherId): array
    {
        $matching = $teacher->specialties->whereIn('id', $specialtyIds);
        $department = $this->primaryDepartment($teacher->specialties, $departmentIds);
        $qualification = $teacher->qualifications;

        $reason = match (true) {
            $teacher->status !== 'active' => 'This teacher account is deactivated.',
            $teacher->specialties->isEmpty() => 'This teacher has no teaching specialty preference yet.',
            $matching->isEmpty() => "This teacher has no teaching preference for any of this course's specialties.",
            default => null,
        };

        return [
            'id' => $teacher->id,
            'name' => $teacher->name,
            'first_name' => $teacher->first_name,
            'last_name' => $teacher->last_name,
            'username' => $teacher->username,
            'profile_picture' => $teacher->currentAvatar?->object_key,
            'status' => $teacher->status,
            'qualification' => $qualification ?? null,
            'department' => $department,
            'course_count' => (int) $teacher->course_count,
            'matching_specialty_count' => $matching->count(),
            'is_current' => $teacher->id === $currentTeacherId,
            'eligible' => $reason === null,
            'group' => $reason === null ? self::GROUP_ELIGIBLE : self::GROUP_INELIGIBLE,
            'ineligible_reason' => $reason,
        ];
    }
    private function primaryDepartment(Collection $specialties, array $departmentIds): ?array
    {
        $departments = $specialties->pluck('department')->filter();

        if ($departments->isEmpty()) {
            return null;
        }

        $ranked = $departments
            ->groupBy('id')
            ->sortByDesc(fn(Collection $group) => [
                in_array($group->first()->id, $departmentIds, true) ? 1 : 0,
                $group->count(),
            ])
            ->first()
            ->first();

        return [
            'id' => $ranked->id,
            'name' => $ranked->department_name,
            'is_participating' => in_array($ranked->id, $departmentIds, true),
        ];
    }
}
