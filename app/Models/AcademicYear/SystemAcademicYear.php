<?php

namespace App\Models\AcademicYear;

use App\Models\Course\SemesterJointCourse;
use App\Models\Election\Election;
use App\Models\Election\ElectionOfficeYear;
use App\Models\ExamJointCourse\ExamJointCourse;
use App\Models\Graduation\GraduationBatch;
use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SystemAcademicYear extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'year_start',
        'year_end',
    ];

    protected $table = 'system_academic_years';
    public $incrementing = false;
    public $keyType = 'string';

    public function semesterJointCourse(): HasMany
    {
        return $this->hasMany(SemesterJointCourse::class);
    }
    public function schoolAcademicYear(): HasMany
    {
        return $this->hasMany(SchoolAcademicYear::class, 'school_year_id');
    }
    public function examJointCourse(): HasMany
    {
        return $this->hasMany(ExamJointCourse::class);
    }
    public function election(): HasMany
    {
        return $this->hasMany(Election::class, "academic_year_id");
    }
    public function electionOfficeYears(): HasMany
    {
        return $this->hasMany(ElectionOfficeYear::class, "system_academic_year_id");
    }

    public function graduationBatch(): HasMany
    {
        return $this->hasMany(GraduationBatch::class, 'school_year_id');
    }
}
