<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Timetable extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'school_branch_id',
        'specialty_id',
        'level_id',
        'course_id',
        'teacher_id',
        'day_of_week',
        'start_time',
        'end_time',
        'semester_id',
        'student_batch_id'
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'timetables';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function course(): BelongsTo {
        return $this->belongsTo(Courses::class, 'course_id');
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class, 'teacher_id');
    }

    public function level(): BelongsTo {
        return $this->belongsTo(Educationlevels::class, 'level_id');
    }

   public function semester(): HasMany {
        return $this->hasMany(Semester::class, 'semeter_id');
   }

}
