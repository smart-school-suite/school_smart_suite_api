<?php

namespace App\Jobs\Course\Concerns;

use App\Models\Courses;
use App\Services\Import\ImportRowException;

/**
 * The columns course and joint course imports share: code, title, credit,
 * semester, description and course types (type_1, type_2... or several in
 * one cell separated by "|").
 *
 * Needs BaseImportJob ($this->lookups, $this->schoolBranchId).
 */
trait ImportsCourseFields
{
    protected function courseRules(): array
    {
        return [
            'course_code' => ['required', 'string', 'max:50'],
            'course_title' => ['required', 'string', 'max:255'],
            'credit' => ['required', 'integer', 'min:0', 'max:10'],
            'semester_id' => ['required'],
            'description' => ['nullable', 'string', 'max:500'],
            'typeIds' => ['required', 'array', 'min:1'],
        ];
    }

    protected function courseAttributes(): array
    {
        return [
            'course_code' => 'course code',
            'course_title' => 'course title',
            'credit' => 'course credit',
            'semester_id' => 'semester',
            'typeIds' => 'course type',
        ];
    }

    protected function courseMessages(): array
    {
        return ['typeIds.required' => 'At least one course type is required (for example Practical or Theoritical).'];
    }

    protected function normalizeCourseFields(array $row): array
    {
        $semesterId = $this->lookups->semesterId($row['semester'] ?? null);
        if (!empty($row['semester']) && !$semesterId) {
            throw ImportRowException::notFound(
                'Semester not found. Use a semester name such as First Semester, or its number (1, 2...).',
                'semester',
                $row['semester']
            );
        }

        $typeIds = [];
        foreach ($row['course_types'] ?? [] as $type) {
            foreach (preg_split('/[|;]/', (string) ($type['course_type'] ?? '')) as $name) {
                $name = trim($name);
                if ($name === '') {
                    continue;
                }
                $id = $this->lookups->courseTypeId($name);
                if (!$id) {
                    throw ImportRowException::notFound(
                        'Course type not found. Use ' . $this->lookups->referenceNames('course_types', 'name') . '.',
                        'course_type',
                        $name
                    );
                }
                $typeIds[$id] = ['type_id' => $id];
            }
        }

        return [
            'course_code' => $row['course_code'] ?? null,
            'course_title' => $row['course_title'] ?? null,
            'credit' => $row['course_credit'] ?? null,
            'semester_id' => $semesterId,
            'description' => $row['description'] ?? null,
            'typeIds' => array_values($typeIds),
        ];
    }

    /** Course codes identify a course at the school, as the create forms treat code + title. */
    protected function ensureCourseIsNew(array $row): void
    {
        $exists = Courses::where('school_branch_id', $this->schoolBranchId)
            ->whereRaw('LOWER(course_code) = ?', [mb_strtolower($row['course_code'])])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists('A course with this code already exists at this school.', 'course_code', $row['course_code']);
        }
    }
}
