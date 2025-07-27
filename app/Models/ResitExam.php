<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResitExam extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
       'start_date',
       'end_date',
       'weighted_mark',
       'timetable_published',
       'status',
       'grading_added',
       'expected_candidate_number',
       'evaluated_candidate_number',
       'school_branch_id',
       'exam_type_id',
       'reference_exam_id',
       'school_year',
       'semester_id',
       'level_id',
       'specialty_id',
       'grades_category_id'
    ];

    public $incrementing = 'false';
    public $table = 'resit_exams';
    public $keyType = 'string';

    public function resitMarks(): HasMany {
        return $this->hasMany(ResitMarks::class, 'resit_exam_id');
    }
    public function resitExamTimetable(): BelongsTo {
        return $this->belongsTo(Resitexamtimetable::class, 'resit_exam_id');
    }
    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    public function resitResults(): HasMany {
        return $this->hasMany(ResitResults::class, 'resit_exam_id');
    }
    public function resitCandidates(): HasMany {
        return $this->hasMany(ResitCandidates::class, 'resit_exam_id');
    }
    public function exam(): BelongsTo {
        return $this->belongsTo(Exams::class, 'reference_exam_id');
    }
    public function examType(): BelongsTo {
        return $this->belongsTo(Examtype::class, 'exam_type_id');
    }
    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function gradesCategory(): BelongsTo {
        return $this->belongsTo(GradesCategory::class, 'grades_category_id');
    }
    public function semester(): BelongsTo
    {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

}
