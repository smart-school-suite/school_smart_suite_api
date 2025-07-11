<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\GeneratesUuid;
class Grades extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'letter_grade_id',
        'grade_points',
        'minimum_score',
        'grade_status',
        'resit_status',
        'maximum_score',
        'determinant',
        'grades_category_id'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'grades';

    public function exam() : BelongsTo {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function lettergrade(): BelongsTo {
        return $this->belongsTo(LetterGrade::class, 'letter_grade_id');
    }
}
