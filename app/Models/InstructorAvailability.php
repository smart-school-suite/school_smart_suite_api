<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class InstructorAvailability extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'school_branch_id',
        'teacher_id',
        'level_id',
        'school_semester_id',
        'specialty_id',
        'status'
    ];

    public $incrementing = 'false';

    public $keyType = 'string';
    public $table = 'teacher_availabilities';

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function schoolSemester(): BelongsTo {
        return $this->belongsTo(SchoolSemester::class, 'school_semester_id');
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function instructorAvailabilitySlot(): HasMany {
        return $this->hasMany(InstructorAvailabilitySlot::class);
    }
}
