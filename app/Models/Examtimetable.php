<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Examtimetable extends Model
{
    use HasFactory;

    protected $fillable = [
      'id',
      'school_branch_id',
      'exam_id',
      'course_id',
      'specialty_id',
      'date',
      'start_time',
      'level_id',
      'end_time',
      'duration',
      'school_year',
      'student_batch_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'duration' => 'string',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'examtimetable';


    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

    public function course(): BelongsTo {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function exam(): BelongsTo {
        return $this->belongsTo(Exams::class, 'exam_id');
    }
}
