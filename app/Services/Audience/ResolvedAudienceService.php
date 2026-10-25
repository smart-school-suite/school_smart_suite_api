<?php

namespace App\Services\Audience;
use Illuminate\Support\Collection;
class ResolvedAudienceService
{
    private array $idsByModel = [];
    private array $missingByModel = [];

    public function add(string $modelClass, array $ids, array $missing = []): void
    {
        $this->idsByModel[$modelClass] = array_values(array_unique(
            array_merge($this->idsByModel[$modelClass] ?? [], $ids)
        ));

        $this->missingByModel[$modelClass] = array_values(array_unique(
            array_merge($this->missingByModel[$modelClass] ?? [], $missing)
        ));
    }

    public function idsFor(string $modelClass): array
    {
        return $this->idsByModel[$modelClass] ?? [];
    }

    public function count(): int
    {
        return array_sum(array_map('count', $this->idsByModel));
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    public function counts(): array
    {
        $counts = [];
        foreach ($this->idsByModel as $class => $ids) {
            $counts[class_basename($class)] = count($ids);
        }

        return $counts;
    }

    public function hasMissing(): bool
    {
        foreach ($this->missingByModel as $missing) {
            if (!empty($missing)) {
                return true;
            }
        }

        return false;
    }

    public function missing(): array
    {
        $out = [];
        foreach ($this->missingByModel as $class => $missing) {
            if (!empty($missing)) {
                $out[class_basename($class)] = $missing;
            }
        }

        return $out;
    }

    public function recipients(): Collection
    {
        $rows = collect();

        foreach ($this->idsByModel as $class => $ids) {
            $morph = (new $class())->getMorphClass();

            foreach ($ids as $id) {
                $rows->push(['type' => $morph, 'id' => $id]);
            }
        }

        return $rows;
    }
}
