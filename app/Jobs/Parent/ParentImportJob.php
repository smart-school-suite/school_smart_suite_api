<?php

namespace App\Jobs\Parent;

use App\Jobs\Import\BaseImportJob;
use App\Services\Import\ImportLookups;
use App\Services\Import\ImportRowException;
use App\Services\Import\ImportType;
use App\Services\Parent\ParentService;

/**
 * Creates guardians (parents) from a spreadsheet through ParentService, like
 * the "Add guardian" form. A guardian with the same phone number at the
 * school is reported as already existing.
 *
 * Contact method and language are matched to the form's options; both are
 * optional here and default to "phone call" and "English".
 */
class ParentImportJob extends BaseImportJob
{
    private const CONTACT_METHODS = [
        'phone call' => ['phone call', 'call', 'phone', 'voice', 'telephone'],
        'text message' => ['text message', 'text', 'sms', 'message', 'whatsapp'],
    ];

    private const LANGUAGES = [
        'English' => ['english', 'en', 'eng'],
        'French' => ['french', 'fr', 'francais', 'français'],
        'Pigin' => ['pigin', 'pidgin', 'pidgin english'],
    ];

    public static function importType(): ImportType
    {
        return ImportType::PARENT;
    }

    protected function requiredColumns(): array
    {
        return ['name' => 'Guardian Name', 'phone' => 'Phone', 'address' => 'Address'];
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string', 'max:255'],
        ];
    }

    protected function attributes(): array
    {
        return ['name' => 'guardian name'];
    }

    protected function normalize(array $row): array
    {
        return [
            'name' => $row['name'] ?? null,
            'phone' => $row['phone'] ?? null,
            'address' => $row['address'] ?? null,
            'preferred_contact_method' => $this->option($row['contact_method'] ?? null, self::CONTACT_METHODS, 'phone call', 'contact_method', 'Contact method must be phone call or text message.'),
            'preferred_language' => $this->option($row['language'] ?? null, self::LANGUAGES, 'English', 'language', 'Language must be English, French or Pigin.'),
        ];
    }

    protected function duplicateKey(array $row): ?string
    {
        return $row['phone'] ? preg_replace('/\D/', '', $row['phone']) : null;
    }

    protected function duplicateField(): ?string
    {
        return 'phone';
    }

    protected function importRow(array $row, int $rowNumber): string
    {
        // Guardians existing before the import (loaded once); repeats within
        // the file are caught as duplicates.
        if ($this->lookups->guardian($row['phone'])) {
            throw ImportRowException::alreadyExists('A guardian with this phone number already exists at this school.', 'phone', $row['phone']);
        }

        app(ParentService::class)->createParent(
            $row,
            (object) ['id' => $this->schoolBranchId],
            (object) ['id' => $this->systemJob->initiated_by_id]
        );

        return self::OUTCOME_CREATED;
    }

    private function option(?string $value, array $options, string $default, string $field, string $message): string
    {
        if ($value === null) {
            return $default;
        }

        foreach ($options as $canonical => $aliases) {
            if (in_array(ImportLookups::key($value), $aliases, true)) {
                return $canonical;
            }
        }

        throw ImportRowException::invalid($message, $field, $value);
    }
}
