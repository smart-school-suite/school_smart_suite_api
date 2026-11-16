<?php

namespace App\Events\AcademicYear;

use App\Models\AcademicYear\SchoolAcademicYear;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * A school academic year was created for a specialty, by hand or as an
 * automatically created copy.
 */
class SchoolAcademicYearCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly SchoolAcademicYear $academicYear) {}
}
