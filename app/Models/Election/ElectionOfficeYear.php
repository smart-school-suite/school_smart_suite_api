<?php

namespace App\Models\Election;

use App\Models\AcademicYear\SchoolAcademicYear;
use App\Models\AcademicYear\SystemAcademicYear;
use App\Models\Schoolbranches;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One academic year of an office holder's tenure. References the
 * (specialty specific) school academic year whose real dates drive tenure.
 */
class ElectionOfficeYear extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'election_office_years';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'school_branch_id',
        'election_office_holder_id',
        'school_academic_year_id',
        'system_academic_year_id',
        'term_year_number',
    ];

    protected $casts = [
        'term_year_number' => 'integer',
    ];

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function officeHolder(): BelongsTo
    {
        return $this->belongsTo(ElectionOfficeHolder::class, 'election_office_holder_id');
    }

    public function schoolAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SchoolAcademicYear::class, 'school_academic_year_id');
    }

    public function systemAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'system_academic_year_id');
    }
}
