<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SchoolSemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'start_date',
        'end_date',
        'school_year',
        'semester_id',
        'specialty_id',
        'status',
        'timetable_published',
        'school_branch_id',
        'student_batch_id'
    ];

    public $incrementing = 'false';
    public $table = 'school_semesters';
    public $keyType = 'string';

     public function instructorAvailability(): HasMany {
        return $this->hasMany(InstructorAvailability::class);
    }
    public function feeSchedule(): HasMany {
        return $this->hasMany(FeeSchedule::class);
    }
    public function specialty(): BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function semester(): BelongsTo {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function teacherAvailabilitySlot(): HasMany {
        return $this->hasMany(InstructorAvailabilitySlot::class, 'school_semester_id');
    }
    public function studentBatch(): BelongsTo {
         return $this->belongsTo(Studentbatch::class, 'student_batch_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

}
