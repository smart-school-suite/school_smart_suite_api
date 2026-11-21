<?php

namespace App\Models\AcademicYear;

use App\Models\Course\SemesterJointCourse;
use App\Models\Election\ElectionOfficeYear;
use App\Models\Exam\Exam;
use App\Models\FeeSchedule;
use App\Models\Promotion\PromotionRun;
use App\Models\ResitExam;
use App\Models\Schoolbranches;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\Student;
use App\Models\TuitionFees;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolAcademicYear extends Model
{
    use HasUuids;
    protected $fillable = [
        'specialty_id',
        'school_branch_id',
        'system_academic_year_id',
        'start_date',
        'end_date',
    ];

    // Calendar dates: serialise as Y-m-d so they are not shifted into UTC timestamps.
    protected $casts = [
        'start_date' => 'date:Y-m-d',
        'end_date' => 'date:Y-m-d',
    ];

    protected $table = 'school_academic_years';

    // Stored as plain dates whatever the caller passes (string or Carbon).
    public function setStartDateAttribute($value): void
    {
        $this->attributes['start_date'] = $value ? \Carbon\Carbon::parse($value)->toDateString() : null;
    }

    public function setEndDateAttribute($value): void
    {
        $this->attributes['end_date'] = $value ? \Carbon\Carbon::parse($value)->toDateString() : null;
    }
    public $incrementing = false;
    public $keyType = 'string';

    public function tuitionFeeSchedule(): HasMany
    {
        return $this->hasMany(FeeSchedule::class, 'school_year_id');
    }
    public function resitExam(): HasMany
    {
        return $this->hasMany(ResitExam::class, 'school_year_id');
    }
    public function schoolSemester(): HasMany
    {
        return $this->hasMany(SchoolSemester::class, 'school_year_id');
    }

    /**
     * { id, name, start_date, end_date } for API responses: name is the system
     * academic year's (e.g. "2026/2027"). Null when there is no academic year.
     */
    public static function summary(?self $year): ?array
    {
        if (!$year) {
            return null;
        }

        return [
            'id' => $year->id,
            'name' => $year->systemAcademicYear?->name,
            'start_date' => $year->start_date ? substr((string) $year->start_date, 0, 10) : null,
            'end_date' => $year->end_date ? substr((string) $year->end_date, 0, 10) : null,
        ];
    }

    public function systemAcademicYear(): BelongsTo
    {
        return $this->belongsTo(SystemAcademicYear::class, 'system_academic_year_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function exam(): HasMany
    {
        return $this->hasMany(Exam::class, 'school_year_id');
    }

    public function promotionRun(): HasMany
    {
        return $this->hasMany(PromotionRun::class, 'school_year_id');
    }

    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function tuitionFees(): HasMany
    {
        return $this->hasMany(TuitionFees::class, 'school_academic_year_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class, 'school_academic_year_id');
    }

    public function electionOfficeYears(): HasMany
    {
        return $this->hasMany(ElectionOfficeYear::class, 'school_academic_year_id');
    }
}
