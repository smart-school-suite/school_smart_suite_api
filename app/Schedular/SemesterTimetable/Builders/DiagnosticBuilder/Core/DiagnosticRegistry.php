<?php

namespace App\Schedular\SemesterTimetable\Builders\DiagnosticBuilder\Core;

use App\Schedular\SemesterTimetable\Solution\Catalog\ConstraintCatalog;
use Illuminate\Support\Collection;

class DiagnosticRegistry
{
    public function __construct(private ConstraintCatalog $catalog) {}

    public function build($diagnostics): Collection
    {
        $map = $this->catalog->diagnosticBuilders();
        $diagnosticDTOs = [];

        foreach ($diagnostics as $diagnostic) {
            $type = $diagnostic["constraint_failed"]["key"] ?? null;

            if ($type && isset($map[$type])) {
                $diagnosticDTOs[] = (new $map[$type]())->build($diagnostic);
            }
        }

        return collect($diagnosticDTOs);
    }
}
