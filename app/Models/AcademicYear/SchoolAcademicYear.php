<?php

namespace App\Models\AcademicYear;

use App\Models\Specialty;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\BelongsTo;

class SchoolAcademicYear extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'specialty_id',
        'school_branch_id',
        'school_year_id',
        'start_date',
        'end_date',
    ];

    protected $table = 'school_academic_years';
    public $incrementing = false;
    public $keyType = 'string';

    public function systemAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'school_year_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
}
