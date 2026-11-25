<?php

namespace App\Jobs\JointCourse;

use App\Jobs\Course\Concerns\ImportsCourseFields;
use App\Jobs\Import\BaseImportJob;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use App\Services\JointCourse\JointCourseService;

/**
 * Creates joint courses (one course taught to several specialties) from a
 * spreadsheet through JointCourseService, like the "Add joint course" form.
 * The specialties come from the specialty_1/level_1, specialty_2/level_2...
 * columns; at least two are needed.
 */
class JointCourseImportJob extends BaseImportJob
{
    use ImportsCourseFields;

    public static function importType(): ImportType
    {
        return ImportType::JOINT_COURSE;
    }

    protected function requiredColumns(): array
    {
        return [
            'course_code' => 'Course Code',
            'course_title' => 'Course Title',
            'course_credit' => 'Course Credit',
            'semester' => 'Semester',
        ];
    }

    protected function rules(): array
    {
        return $this->courseRules() + ['specialtyIds' => ['required', 'array', 'min:2']];
    }

    protected function attributes(): array
    {
        return $this->courseAttributes() + ['specialtyIds' => 'specialties'];
    }

    protected function messages(): array
    {
        return $this->courseMessages() + [
            'specialtyIds.required' => 'A joint course needs at least two specialties.',
            'specialtyIds.min' => 'A joint course needs at least two different specialties.',
        ];
    }

    protected function normalize(array $row): array
    {
        $specialtyIds = [];

        foreach ($row['specialties'] ?? [] as $entry) {
            $name = $entry['specialty'] ?? null;
            if ($name === null) {
                continue;
            }

            $specialty = $this->lookups->specialty($name, $entry['level'] ?? null);
            if (!$specialty) {
                throw ImportRowException::notFound(
                    empty($entry['level'])
                        ? 'No specialty with this name at your school.'
                        : 'No specialty with this name at this level at your school.',
                    'specialty',
                    $name
                );
            }
            $specialtyIds[$specialty->id] = $specialty->id;
        }

        return $this->normalizeCourseFields($row) + ['specialtyIds' => array_values($specialtyIds)];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['course_code'];
    }

    protected function duplicateField(): ?string
    {
        return 'course_code';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $this->ensureCourseIsNew($row);

        app(JointCourseService::class)->createJointCourse(
            (object) ['id' => $this->schoolBranchId],
            $row,
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
