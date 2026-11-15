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
        'version_id',
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

      public function course(): BelongsTo {
          return $this->belongsTo(Courses::class, 'course_id');
      }

      /** The generated version these sessions come from (only the active version has slots). */
      public function version(): BelongsTo
      {
          return $this->belongsTo(\App\Models\ResitTimetable\ResitTimetableVersion::class, 'version_id');
      }

      public function sessionHalls(): HasMany
      {
          return $this->hasMany(\App\Models\ResitTimetable\ResitSessionHall::class, 'resit_slot_id');
      }

      public function sessionInvigilators(): HasMany
      {
          return $this->hasMany(\App\Models\ResitTimetable\ResitSessionInvigilator::class, 'resit_slot_id');
      }

      public function exam(): BelongsTo {
          return $this->belongsTo(Exams::class, 'exam_id');
      }
}
