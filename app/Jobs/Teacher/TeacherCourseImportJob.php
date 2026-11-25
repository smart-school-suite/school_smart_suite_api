<?php

namespace App\Jobs\Teacher;

use App\Jobs\Import\BaseImportJob;
use App\Models\Course\CourseSpecialty;
use App\Models\Teacher\TeacherSpecialty;
use App\Models\TeacherCoursePreference;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Assigns courses to teachers from a spreadsheet: one row is a teacher (full
 * name or email) and a course (code or title). Specialty and level are
 * optional and only used to tell apart courses sharing a title.
 *
 * Follows the rules of the Teacher Course page (TeacherCoursePreferenceService):
 * a course has one teacher, and the course must belong to one of the
 * teacher's specialties.
 */
class TeacherCourseImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::TEACHER_COURSE;
    }

    protected function requiredColumns(): array
    {
        return ['teacher' => 'Teacher Name', 'course' => 'Course'];
    }

    protected function rules(): array
    {
        return [
            'teacher_name' => ['required'],
            'course_name' => ['required'],
        ];
    }

    protected function attributes(): array
    {
        return ['teacher_name' => 'teacher', 'course_name' => 'course'];
    }

    protected function normalize(array $row): array
    {
        $teacher = $this->lookups->teacher($row['teacher'] ?? null);
        if (!empty($row['teacher']) && !$teacher) {
            throw ImportRowException::notFound('No teacher with this name or email at your school.', 'teacher', $row['teacher']);
        }

        $specialty = $this->lookups->specialty($row['specialty'] ?? null, $row['level'] ?? null);
        if (!empty($row['specialty']) && !$specialty) {
            throw ImportRowException::notFound('No specialty with this name at your school.', 'specialty', $row['specialty']);
        }

        $course = $this->lookups->course($row['course'] ?? null, $specialty);
        if (!empty($row['course']) && !$course) {
            throw ImportRowException::notFound('No course with this code or title at your school.', 'course', $row['course']);
        }

        return [
            'teacher_name' => $row['teacher'] ?? null,
            'course_name' => $row['course'] ?? null,
            'teacher_id' => $teacher?->id,
            'course_id' => $course?->id,
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['course_id'];
    }

    protected function duplicateField(): ?string
    {
        return 'course_name';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $assignments = TeacherCoursePreference::where('school_branch_id', $this->schoolBranchId)
            ->where('course_id', $row['course_id']);

        if ((clone $assignments)->where('teacher_id', $row['teacher_id'])->exists()) {
            throw ImportRowException::alreadyExists('The course is already assigned to this teacher.', 'course', $row['course_name']);
        }

        if ($assignments->exists()) {
            throw ImportRowException::notAllowed(
                'The course already has a teacher. A course can have only one teacher; change its teacher on the Teacher Course page.',
                'course',
                $row['course_name']
            );
        }

        $specialtyIds = TeacherSpecialty::where('school_branch_id', $this->schoolBranchId)
            ->where('teacher_id', $row['teacher_id'])
            ->pluck('specialty_id');

        if ($specialtyIds->isEmpty()) {
            throw ImportRowException::notAllowed(
                'The teacher is not assigned to any specialty yet. Assign the teacher to the course\'s specialty first.',
                'teacher',
                $row['teacher_name']
            );
        }

        $inTeachersSpecialty = CourseSpecialty::where('course_id', $row['course_id'])
            ->whereIn('specialty_id', $specialtyIds)
            ->exists();

        if (!$inTeachersSpecialty) {
            throw ImportRowException::notAllowed(
                "The course is not part of any of the teacher's specialties. Assign the teacher to the course's specialty first.",
                'course',
                $row['course_name']
            );
        }

        TeacherCoursePreference::create([
            'teacher_id' => $row['teacher_id'],
            'course_id' => $row['course_id'],
            'school_branch_id' => $this->schoolBranchId,
        ]);

        return self::OUTCOME_CREATED;
    }
}
