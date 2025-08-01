<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Courses extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
       'course_code',
       'course_title',
       'specialty_id',
       'department_id',
       'school_branch_id',
       'credit',
       'status',
       'description',
       'semester_id',
       'level_id',
    ];

    public $keyType = 'string';
    public $table = 'courses';
    public $incrementing = 'false';

    public function resitmarks(): HasMany {
        return $this->hasMany(ResitMarks::class, 'course_id');
    }
    public function student(): BelongsTo {
         return $this->belongsTo(Student::class);
    }

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function marks(): HasMany {
        return $this->hasMany(Marks::class, 'courses_id');
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function examtimetable(): HasMany {
        return $this->hasMany(Examtimetable::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class);
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class);
    }

    public function timetable(): HasMany {
        return $this->hasMany(Timetable::class);
    }

    public function level(): BelongsTo {
         return $this->belongsTo(Educationlevels::class);
    }

    public function semester(): BelongsTo {
         return $this->belongsTo(Semester::class);
    }
    public function studentresit() : HasMany {
        return $this->hasMany(Studentresit::class);
    }
}
