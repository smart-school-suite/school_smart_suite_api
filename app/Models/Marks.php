<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marks extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'courses_id',
        'exam_id',
        'level_id',
        'score',
        'specialty_id',
        'school_branch_id',
        'grade'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'marks';

    public function course(): BelongsTo {
        return $this->belongsTo(Courses::class);
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }
    
    public function exams(): BelongsTo {
        return $this->belongsTo(Exams::class);
    }
    
}
