<?php

namespace App\Schedular\ExamTimetable\Builders\DiagnosticBuilder\Core;

use App\Schedular\ExamTimetable\Solution\Catalog\ConstraintCatalog;
use Illuminate\Support\Collection;

class DiagnosticRegistry
{
    public function __construct(private ConstraintCatalog $catalog) {}

    public function build(array $diagnostics): Collection
    {
        $map = $this->catalog->diagnosticBuilders();
        $built = [];

        foreach ($diagnostics as $diagnostic) {
            $type = $diagnostic['constraint_failed']['key'] ?? null;

            if ($type && isset($map[$type])) {
                $built[] = app($map[$type])->build($diagnostic);
            }
        }

        return collect($built);
    }
}
