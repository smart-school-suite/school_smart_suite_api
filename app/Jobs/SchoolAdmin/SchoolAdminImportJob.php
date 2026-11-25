<?php

namespace App\Jobs\SchoolAdmin;

use App\Jobs\AuthenticationJobs\SendPasswordVaiMailJob;
use App\Jobs\Import\BaseImportJob;
use App\Models\Schooladmin;
use App\Services\Helpers\Actor\ActorHelperService;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Creates school admin accounts from a spreadsheet, as the "Add admin" form
 * does: an existing email at the school is reported, not overwritten, and
 * each new admin is emailed their password.
 */
class SchoolAdminImportJob extends BaseImportJob
{
    public static function importType(): ImportType
    {
        return ImportType::SCHOOL_ADMIN;
    }

    protected function requiredColumns(): array
    {
        return ['email' => 'Email', 'first_name' => 'First Name', 'last_name' => 'Last Name'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:200'],
        ];
    }

    protected function attributes(): array
    {
        return ['name' => 'full name', 'first_name' => 'first name', 'last_name' => 'last name'];
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
        $exists = Schooladmin::where('school_branch_id', $this->schoolBranchId)
            ->where('email', $row['email'])
            ->exists();

        if ($exists) {
            throw ImportRowException::alreadyExists('A school admin with this email already exists at this school.', 'email', $row['email']);
        }

        $password = app(ActorHelperService::class)->generateRandomPassword(12);

        $admin = Schooladmin::create([
            ...$row,
            'username' => app(ActorHelperService::class)->generateUsername($row['name'], Schooladmin::class),
            'password' => Hash::make($password),
            'school_branch_id' => $this->schoolBranchId,
            'status' => 'active',
        ]);
        $admin->assignRole('schoolAdmin');

        SendPasswordVaiMailJob::dispatch($password, $admin->email)->afterCommit();

        return self::OUTCOME_CREATED;
    }
}
