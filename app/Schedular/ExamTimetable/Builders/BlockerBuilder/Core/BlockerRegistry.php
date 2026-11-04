<?php

namespace App\Schedular\ExamTimetable\Builders\BlockerBuilder\Core;

use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use Illuminate\Support\Collection;

class BlockerRegistry
{
    public function __construct(private ConstraintCatalog $catalog) {}

    public function build(array $blockers): Collection
    {
        $map = $this->catalog->blockerBuilders();
        $built = collect();

        foreach ($blockers as $blocker) {
            $type = $blocker['key'] ?? null;

            if ($type && isset($map[$type])) {
                $built->push(app($map[$type])->build($blocker));
            }
        }

        return $built;
    }
}
