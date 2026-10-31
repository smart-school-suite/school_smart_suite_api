<?php

namespace App\Schedular\SemesterTimetable\Builders\BlockerBuilder\Core;

use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use Illuminate\Support\Collection;

class BlockerRegistry
{
    public function __construct(private ConstraintCatalog $catalog) {}

    public function build(array $blockers): Collection
    {
        $map = $this->catalog->blockerBuilders();
        $violations = collect();

        foreach ($blockers as $blocker) {
            $type = $blocker["key"] ?? null;

            if ($type && isset($map[$type])) {
                $violations->push((new $map[$type]())->build($blocker));
            }
        }

        return $violations;
    }
}
