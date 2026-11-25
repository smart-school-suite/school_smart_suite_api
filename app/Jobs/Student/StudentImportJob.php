<?php

namespace App\Jobs\Student;

use App\Jobs\Import\BaseImportJob;
use App\Models\Student;
use App\Services\Auth\Student\CreateStudentService;
use App\Services\Import\ImportLookups;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use Illuminate\Support\Str;

/**
 * Registers students from a spreadsheet through CreateStudentService, the
 * same path as the "Add student" form, so registration and tuition fees are
 * generated the same way. A student whose email or full name is already used
 * at the school is reported, not overwritten.
 *
 * Specialty, batch, guardian, relationship and source are given by name; the
 * guardian may also be given by phone number. Import guardians and batches
 * first.
 */
class StudentImportJob extends BaseImportJob
{
    private const PAYMENT_FORMATS = [
        'one time' => ['one time', 'one-time', 'onetime', 'full', 'full payment', 'once'],
        'installmental' => ['installmental', 'installment', 'installments', 'instalment', 'instalments', 'by installment'],
    ];

    public static function importType(): ImportType
    {
        return ImportType::STUDENT;
    }

    protected function requiredColumns(): array
    {
        return [
            'email' => 'Email',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'gender' => 'Gender',
            'specialty' => 'Specialty',
            'batch' => 'Student Batch',
            'guardian' => 'Guardian',
            'relationship' => 'Relationship',
            'student_source' => 'Student Source',
        ];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'gender_id' => ['required'],
            'specialty_id' => ['required'],
            'student_batch_id' => ['required'],
            'guardian_id' => ['required'],
            'relationship_id' => ['required'],
            'student_source_id' => ['required'],
        ];
    }

    protected function attributes(): array
    {
        return [
            'name' => 'full name',
            'first_name' => 'first name',
            'last_name' => 'last name',
            'gender_id' => 'gender',
            'specialty_id' => 'specialty',
            'student_batch_id' => 'student batch',
            'guardian_id' => 'guardian',
            'relationship_id' => 'relationship',
            'student_source_id' => 'student source',
        ];
    }

    protected function normalize(array $row): array
    {
        $fullName = $row['full_names'] ?? trim(($row['first_name'] ?? '') . ' ' . ($row['last_name'] ?? ''));

        $genderId = $this->lookups->genderId($row['gender'] ?? null);
        if (!empty($row['gender']) && !$genderId) {
            throw ImportRowException::invalid("Gender must be {$this->lookups->genderNames()}.", 'gender', $row['gender']);
        }

        $specialty = $this->lookups->specialty($row['specialty'] ?? null, $row['level'] ?? null);
        if (!empty($row['specialty']) && !$specialty) {
            throw ImportRowException::notFound(
                empty($row['level']) ? 'No specialty with this name at your school.' : 'No specialty with this name at this level at your school.',
                'specialty',
                $row['specialty']
            );
        }

        $batch = $this->lookups->reference('student_batches', ['name'], $row['batch'] ?? null, branchScoped: true);
        if (!empty($row['batch']) && !$batch) {
            throw ImportRowException::notFound('No student batch with this name at your school. Create it first, or import student batches before students.', 'batch', $row['batch']);
        }

        $guardian = $this->lookups->guardian($row['guardian'] ?? null);
        if (!empty($row['guardian']) && !$guardian) {
            throw ImportRowException::notFound('No guardian with this name or phone number at your school. Import guardians before students.', 'guardian', $row['guardian']);
        }

        $relationship = $this->lookups->reference('stu_par_relationships', ['name'], $row['relationship'] ?? null);
        if (!empty($row['relationship']) && !$relationship) {
            throw ImportRowException::invalid(
                'Relationship not recognised. Use ' . $this->lookups->referenceNames('stu_par_relationships', 'name') . '.',
                'relationship',
                $row['relationship']
            );
        }

        $source = $this->lookups->reference('student_sources', ['name'], $row['student_source'] ?? null);
        if (!empty($row['student_source']) && !$source) {
            throw ImportRowException::invalid(
                'Student source not recognised. Use ' . $this->lookups->referenceNames('student_sources', 'name') . '.',
                'student_source',
                $row['student_source']
            );
        }

        return [
            'name' => $fullName !== '' ? $fullName : null,
            'first_name' => $row['first_name'] ?? null,
            'last_name' => $row['last_name'] ?? null,
            'email' => isset($row['email']) ? Str::lower($row['email']) : null,
            'phone' => $row['phone'] ?? null,
            'gender_id' => $genderId,
            'specialty_id' => $specialty?->id,
            'student_batch_id' => $batch?->id,
            'guardian_id' => $guardian?->id,
            'relationship_id' => $relationship?->id,
            'student_source_id' => $source?->id,
            'payment_format' => $this->paymentFormat($row['fee_payment_format'] ?? null),
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
        $students = Student::where('school_branch_id', $this->schoolBranchId);

        if ((clone $students)->where('email', $row['email'])->exists()) {
            throw ImportRowException::alreadyExists('A student with this email already exists at this school.', 'email', $row['email']);
        }

        if ((clone $students)->whereRaw('LOWER(name) = ?', [mb_strtolower($row['name'])])->exists()) {
            throw ImportRowException::alreadyExists(
                'A student with this full name already exists at this school. If it is a different person, add a middle name or initial.',
                'full_names',
                $row['name']
            );
        }

        // Phone numbers are unique across all students.
        if ($row['phone'] && Student::where('phone', $row['phone'])->exists()) {
            throw ImportRowException::alreadyExists('This phone number is already used by another student.', 'phone', $row['phone']);
        }

        $student = app(CreateStudentService::class)->createStudent(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        if ($row['payment_format']) {
            $student->forceFill(['payment_format' => $row['payment_format']])->save();
        }

        return self::OUTCOME_CREATED;
    }

    private function paymentFormat(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        foreach (self::PAYMENT_FORMATS as $format => $aliases) {
            if (in_array(ImportLookups::key($value), $aliases, true)) {
                return $format;
            }
        }

        throw ImportRowException::invalid('Payment format must be one time or installmental.', 'fee_payment_format', $value);
    }
}
