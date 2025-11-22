<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Resitexamtimetable extends Model
{
    use HasFactory, GeneratesUuid;
    protected $fillable = [
        'id',
        'school_branch_id',
        'resit_exam_id',
        'course_id',
        'specialty_id',
        'date',
        'start_time',
        'end_time',
        'duration',
        'level_id',
      ];

      protected $casts = [
          'start_time' => 'datetime',
          'end_time' => 'datetime',
          'duration' => 'string',
          'date' => 'date'
      ];

      public $keyType = 'string';
      public $incrementing = 'false';
      public $table = 'resit_exam_timetable_slots';

      public function resitExam()
      {
          return $this->belongsTo(ResitExam::class, 'resit_exam_id');
      }

      public function course(): HasMany {
          return $this->hasMany(Courses::class, 'course_id');
      }

      public function exam(): BelongsTo {
          return $this->belongsTo(Exams::class, 'exam_id');
      }
}
