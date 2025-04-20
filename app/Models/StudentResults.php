<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class StudentResults extends Model
{
    use HasFactory;
    protected $fillable = [
        'gpa',
        'student_id',
        'school_branch_id',
        'specialty_id',
        'level_id',
        'exam_id',
        'student_batch_id',
        'score_details',
        'total_score',
        'scores'
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
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 30);
         });

    }

}
