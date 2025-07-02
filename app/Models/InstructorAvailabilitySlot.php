<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class InstructorAvailabilitySlot extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'level_id',
        'school_semester_id',
        'specialty_id',
        'teacher_availability_id'
    ];

    public $incrementing = 'false';
    public $table = 'teacher_availability_slots';
    public $keyType = 'string';

    public function schoolSemester(): BelongsTo {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }

    public function teacher() : BelongsTo {
        return $this->belongsTo(Teacher::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

   public function teacherAvailability(): BelongsTo {
       return $this->belongsTo(InstructorAvailability::class, 'teacher_availability_id');
   }
}
