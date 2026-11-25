<?php

namespace App\Http\Requests\Import;

use Illuminate\Foundation\Http\FormRequest;

/**
 * The upload every import shares: the spreadsheet and the column mapping
 * chosen in the app (mapping[standardFields][field] = column name,
 * mapping[repeatableGroups][group][n][field] = column name).
 *
 * A module's request lists the fields whose column must be chosen in
 * requiredFields(); the import job checks the columns exist in the file.
 */
abstract class ImportFileRequest extends FormRequest
{
    /** Largest upload, in kilobytes. */
    public const MAX_FILE_KB = 10240;

    /** @return array<string, string> field => label */
    abstract protected function requiredFields(): array;

    /** @return array<string, string> group => label, groups needing at least one set of columns */
    protected function requiredGroups(): array
    {
        return [];
    }

    public function rules(): array
    {
        $rules = [
            'file' => ['required', 'file', 'mimes:csv,txt,xlsx,xls', 'max:' . self::MAX_FILE_KB],
            'mapping' => ['required', 'array'],
            'mapping.standardFields' => ['required', 'array'],
            'mapping.standardFields.*' => ['nullable', 'string', 'max:150'],
            'mapping.repeatableGroups' => ['nullable', 'array'],
            'mapping.repeatableGroups.*' => ['nullable', 'array', 'max:10'],
            'mapping.repeatableGroups.*.*' => ['nullable', 'array'],
            'mapping.repeatableGroups.*.*.*' => ['nullable', 'string', 'max:150'],
        ];

        foreach (array_keys($this->requiredFields()) as $field) {
            $rules["mapping.standardFields.{$field}"] = ['required', 'string', 'max:150'];
        }

        foreach (array_keys($this->requiredGroups()) as $group) {
            $rules["mapping.repeatableGroups.{$group}"] = ['required', 'array', 'min:1', 'max:10'];
        }

        return $rules;
    }

    public function messages(): array
    {
        $messages = [
            'file.required' => 'Please choose a file to import.',
            'file.file' => 'The uploaded file is invalid.',
            'file.mimes' => 'The file must be a CSV, XLSX or XLS file.',
            'file.max' => 'The file must not be larger than 10 MB.',
            'mapping.required' => 'Match the file\'s columns before importing.',
            'mapping.standardFields.required' => 'Match the file\'s columns before importing.',
        ];

        foreach ($this->requiredFields() as $field => $label) {
            $messages["mapping.standardFields.{$field}.required"] = "Choose the column that holds the {$label}.";
        }

        foreach ($this->requiredGroups() as $group => $label) {
            $messages["mapping.repeatableGroups.{$group}.required"] = "Choose the columns that hold the {$label}.";
            $messages["mapping.repeatableGroups.{$group}.min"] = "Choose the columns that hold the {$label}.";
        }

        return $messages;
    }

    /** The mapping with empty entries removed, as the import job expects it. */
    public function mapping(): array
    {
        $mapping = $this->validated('mapping');

        return [
            'standardFields' => array_filter($mapping['standardFields'] ?? [], fn($column) => filled($column)),
            'repeatableGroups' => collect($mapping['repeatableGroups'] ?? [])
                ->map(fn($instances) => collect($instances)
                    ->map(fn($instance) => array_filter((array) $instance, fn($column) => filled($column)))
                    ->filter()
                    ->values()
                    ->all())
                ->all(),
        ];
    }
}
