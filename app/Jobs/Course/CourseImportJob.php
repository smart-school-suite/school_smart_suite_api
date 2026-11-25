<?php

namespace App\Jobs\Course;

use App\Jobs\Course\Concerns\ImportsCourseFields;
use App\Jobs\Import\BaseImportJob;
use App\Services\Course\CourseService;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;

/**
 * Creates courses of one specialty from a spreadsheet through CourseService,
 * like the "Add course" form. A course whose code is already used at the
 * school is reported, not overwritten. Courses shared by several specialties
 * are imported as joint courses.
 */
class CourseImportJob extends BaseImportJob
{
    use ImportsCourseFields;

    public static function importType(): ImportType
    {
        return ImportType::COURSE;
    }

    protected function requiredColumns(): array
    {
        return [
            'course_code' => 'Course Code',
            'course_title' => 'Course Title',
            'course_credit' => 'Course Credit',
            'semester' => 'Semester',
            'specialty' => 'Specialty Name',
        ];
    }

    protected function rules(): array
    {
        return $this->courseRules() + ['specialty_id' => ['required']];
    }

    protected function attributes(): array
    {
        return $this->courseAttributes() + ['specialty_id' => 'specialty'];
    }

    protected function messages(): array
    {
        return $this->courseMessages();
    }

    protected function normalize(array $row): array
    {
        $specialty = $this->lookups->specialty($row['specialty'] ?? null, $row['level'] ?? null);
        if (!empty($row['specialty']) && !$specialty) {
            throw ImportRowException::notFound(
                empty($row['level'])
                    ? 'No specialty with this name at your school.'
                    : 'No specialty with this name at this level at your school.',
                'specialty',
                $row['specialty']
            );
        }

        return $this->normalizeCourseFields($row) + ['specialty_id' => $specialty?->id];
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

        app(CourseService::class)->createCourse(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }
}
