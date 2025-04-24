<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class ResitCandidates extends Model
{
    use HasFactory;

    protected $fillable = [
        'resit_exam_id',
        'student_id',
        'school_branch_id',
        'grades_submitted',
        'student_accessed',
    ];
    protected $casts = [
        'grades_submitted' => 'boolean',
        'student_accessed' => 'boolean',
    ];
    public function resitExam()
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }
}
