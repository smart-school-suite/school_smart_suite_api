<?php

namespace App\Jobs\Teacher;

use App\Jobs\Import\BaseImportJob;
use App\Models\Teacher;
use App\Services\Auth\Teacher\CreateTeacherService;
use App\Services\Import\ImportLookups;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use Illuminate\Support\Str;

/**
 * Creates teachers from a spreadsheet through CreateTeacherService, the same
 * path as the "Add teacher" form. A teacher whose email or full name is
 * already used at the school is reported, not overwritten.
 *
 * Qualifications and teaching levels come from repeatable column groups
 * (qualification_1, field_of_study_1, ...). A level cell may also hold
 * several levels separated by "|", e.g. "Level One|Level Two".
 */
class TeacherImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::TEACHER;
    }

    protected function requiredColumns(): array
    {
        return ['email' => 'Email', 'first_name' => 'First Name', 'last_name' => 'Last Name', 'gender' => 'Gender'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'email' => ['required', 'email', 'max:100'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:200'],
            'gender_id' => ['required'],
            'allowed_level_ids' => ['required', 'array', 'min:1'],
            'qualifications' => ['required', 'array', 'min:1'],
            'qualifications.*.field_of_study' => ['required', 'string', 'max:200'],
            'qualifications.*.institution' => ['required', 'string', 'max:150'],
            'qualifications.*.year' => ['required', 'string', 'max:50'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'name' => 'full name',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'gender_id' => 'gender',
            'allowed_level_ids' => 'teaching level',
            'qualifications' => 'qualification',
            'qualifications.*.field_of_study' => 'qualification field of study',
            'qualifications.*.institution' => 'qualification institution',
            'qualifications.*.year' => 'qualification year',
        ];
    }

    protected function messages(): array
    {
        return [
            'allowed_level_ids.required' => 'At least one level the teacher may teach is required.',
            'qualifications.required' => 'At least one qualification is required.',
        ];
    }

    protected function normalize(array $row): array
    {
        $fullName = $row['full_names'] ?? trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));
        $genderId = $this->lookups->genderId($row['gender'] ?? null);

        if (!empty($row['gender']) && !$genderId) {
            throw ImportRowException::invalid("Gender must be {$this->lookups->genderNames()}.", 'gender', $row['gender']);
        }

        return [
            'name' => $fullName !== '' ? $fullName : null,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'email' => isset($row['email']) ? Str::lower($row['email']) : null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'gender_id' => $genderId,
            'allowed_level_ids' => $this->levelIds($row['allowed_levels'] ?? []),
            'qualifications' => $this->qualifications($row['qualifications'] ?? []),
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['email'];
    }

    protected function duplicateField(): ?string
    {
        return 'email';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        $teachers = Teacher::where('school_branch_id', $this->schoolBranchId);

        if ((clone $teachers)->where('email', $row['email'])->exists()) {
            throw ImportRowException::alreadyExists('A teacher with this email already exists at this school.', 'email', $row['email']);
        }

        if ((clone $teachers)->where('name', $row['name'])->exists()) {
            throw ImportRowException::alreadyExists(
                'A teacher with this full name already exists at this school. If it is a different person, add a middle name or initial.',
                'full_names',
                $row['name']
            );
        }

        app(CreateTeacherService::class)->createInstructor(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }

    /** Level names from the level columns, each cell possibly holding several ("Level One|Level Two"). */
    private function levelIds(array $levels): array
    {
        $ids = [];

        foreach ($levels as $level) {
            foreach ($this->split($level['allowed_level'] ?? null) as $name) {
                $id = $this->lookups->levelId($name);

                if (!$id) {
                    $examples = $this->lookups->levels()->sortBy('level')->take(3)->pluck('name')->join(', ');
                    throw ImportRowException::notFound(
                        "Teaching level not found in your school's level system. Use level names such as {$examples}.",
                        'allowed_level',
                        $name
                    );
                }

                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    private function qualifications(array $qualifications): array
    {
        return collect($qualifications)
            ->map(function (array $qualification) {
                $name = $qualification['qualification'] ?? null;
                $id = $this->lookups->qualificationId($name);

                if (!$name) {
                    throw ImportRowException::missing(
                        'A qualification has a field of study, institution or year but no qualification name.',
                        'qualification'
                    );
                }

                if ($name && !$id) {
                    throw ImportRowException::notFound(
                        'Qualification not found. Use a qualification abbreviation such as BSc, MSc or PhD.',
                        'qualification',
                        $name
                    );
                }

                return [
                    'qualification_id' => $id,
                    'field_of_study' => $qualification['field_of_study'] ?? null,
                    'institution' => $qualification['institution'] ?? $qualification['university'] ?? null,
                    'year' => $qualification['year'] ?? $qualification['year_obtained'] ?? null,
                ];
            })
            ->filter(fn($qualification) => $qualification['qualification_id'] !== null)
            ->unique('qualification_id')
            ->values()
            ->all();
    }

    /** @return array<int, string> */
    private function split(?string $value): array
    {
        if ($value === null) {
            return [];
        }

        return collect(preg_split('/[|;]/', $value))
            ->map(fn($part) => trim($part))
            ->filter(fn($part) => $part !== '')
            ->unique(fn($part) => ImportLookups::key($part))
            ->values()
            ->all();
    }
}
