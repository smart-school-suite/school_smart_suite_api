<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentResults extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
        'gpa',
        'student_id',
        'school_branch_id',
        'specialty_id',
        'level_id',
        'exam_id',
        'student_batch_id',
        'score_details',
        'exam_status',
        'total_score',
        'scores'
    ];

    protected $cast = [
         'gpa' => 'decimal:2',
         'score_details' => 'json',
         'total_score' => 'decimal:2'
    ];
    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'student_results';
    public function student(){
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function specialty(){
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function level() {
        return $this->belongsTo(Educationlevels::class , 'level_id');
    }

    public function exam() {
        return $this->belongsTo(Exams::class , 'exam_id');
    }
    public function studentBatch() {
        return $this->belongsTo(StudentBatch::class , 'student_batch_id');
    }
}
