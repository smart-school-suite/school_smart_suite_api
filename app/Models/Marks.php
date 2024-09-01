<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
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
        return $this->belongsTo(Courses::class, 'courses_id');
    }

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }
    
    public function exams(): BelongsTo {
        return $this->belongsTo(Exams::class, 'exam_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }
    
    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
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
