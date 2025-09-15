<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class AccessedStudent extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
         'student_id',
         'exam_id',
         'school_branch_id',
         'grades_submitted',
         'student_accessed',
         'level_id',
         'specialty_id'
    ];

    public $table = 'exam_candidates';
    public $keyType = 'string';
    public $incrementing = 'false';

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');
    }

    public function exam(): BelongsTo {
         return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function specialty(): BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }

}
