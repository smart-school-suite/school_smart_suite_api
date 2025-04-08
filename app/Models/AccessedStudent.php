<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccessedStudent extends Model
{
    use HasFactory;

    protected $fillable = [
         'student_id',
         'exam_id',
         'school_branch_id',
         'grades_submitted',
         'student_accessed'
    ];

    public $table = 'accessed_resit_student';
    public $keyType = 'string';
    public $incrementing = 'false';

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');
    }

    public function exam(): BelongsTo {
         return $this->belongsTo(Exams::class, 'exam_id');
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
