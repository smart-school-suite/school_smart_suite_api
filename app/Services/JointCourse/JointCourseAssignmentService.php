<?php

namespace App\Services\JointCourse;

use App\Exceptions\AppException;
use App\Models\Courses;
use App\Models\Specialty;
use App\Models\Teacher;
use Illuminate\Support\Collection;

/**
 * Read model for the "Manage Assignment" drawer of a joint course: who teaches
 * it now and who could. Assigning and changing teachers stays with
 * TeacherCoursePreferenceService (teacher-course/assign and
 * teacher-course/teacher/change), which applies the same eligibility rule
 * used here: an active teacher with a teaching preference for at least one of
 * the course's specialties.
 */
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
                'teacherCoursePreference',
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
        $currentTeacherIds = $course->teacherCoursePreference->pluck('teacher_id')->unique()->values()->all();

        $teachers = Teacher::where('school_branch_id', $currentSchool->id)
            ->with(['specialties.department', 'qualifications', 'currentAvatar'])
            ->withCount('teacherCoursePreference as course_count')
            ->orderBy('name')
            ->get()
            ->map(fn(Teacher $teacher) => $this->presentTeacher($teacher, $specialtyIds, $departmentIds, $currentTeacherIds));

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
            'current_teachers' => $sorted->where('is_current', true)->values()->all(),
            'teachers' => $sorted->all(),
            'summary' => [
                'teacher_count' => $sorted->count(),
                'eligible_count' => $sorted->where('eligible', true)->count(),
                'is_assigned' => !empty($currentTeacherIds),
            ],
        ];
    }

    private function presentCourse(Courses $course): array
    {
        $specialties = $course->specialties;

        return [
            'id' => $course->id,
            'code' => $course->course_code,
            'title' => $course->course_title,
            'credit' => $course->credit,
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

    private function presentTeacher(Teacher $teacher, array $specialtyIds, array $departmentIds, array $currentTeacherIds): array
    {
        $matching = $teacher->specialties->whereIn('id', $specialtyIds);
        $department = $this->primaryDepartment($teacher->specialties, $departmentIds);
        $qualification = $teacher->qualifications->sortByDesc('level')->first();

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
            'qualification' => $qualification ? [
                'name' => $qualification->name,
                'abbreviation' => $qualification->abbreviation,
                'field_of_study' => $qualification->pivot?->field_of_study,
            ] : null,
            'department' => $department,
            'course_count' => (int) $teacher->course_count,
            'matching_specialty_count' => $matching->count(),
            'is_current' => in_array($teacher->id, $currentTeacherIds, true),
            'eligible' => $reason === null,
            'group' => $reason === null ? self::GROUP_ELIGIBLE : self::GROUP_INELIGIBLE,
            'ineligible_reason' => $reason,
        ];
    }

    /**
     * The department a teacher mostly teaches in, preferring one of the
     * course's departments when the teacher teaches there at all.
     */
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
