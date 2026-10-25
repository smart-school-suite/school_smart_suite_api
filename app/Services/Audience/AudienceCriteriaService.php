<?php

namespace App\Services\Audience;

use Illuminate\Support\Arr;

class AudienceCriteriaService
{
    public function __construct(
        public array $departmentIds = [],
        public array $specialtyIds = [],
        public array $levelIds = [],
        public array $individualIds = [],
    ) {}

    public static function fromBlocks(?array $blocks): self
    {
        if (empty($blocks)) {
            return new self();
        }

        if (Arr::isAssoc($blocks)) {
            $blocks = [$blocks];
        }

        $blocks = collect($blocks)->filter(fn($block) => is_array($block));

        $ids = fn(string $key): array => $blocks
            ->pluck($key)
            ->flatten()
            ->filter(fn($id) => $id !== null && $id !== '')
            ->unique()
            ->values()
            ->all();

        return new self(
            departmentIds: $ids('department_ids'),
            specialtyIds: $ids('specialty_ids'),
            levelIds: $ids('level_ids'),
            individualIds: $ids('individual_ids'),
        );
    }

    public function isEmpty(): bool
    {
        return empty($this->departmentIds)
            && empty($this->specialtyIds)
            && empty($this->levelIds)
            && empty($this->individualIds);
    }

    public function hasSpecialtyScope(): bool
    {
        return !empty($this->specialtyIds) || !empty($this->departmentIds) || !empty($this->levelIds);
    }
}
