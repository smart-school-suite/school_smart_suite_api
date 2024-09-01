<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Courses extends Model
{
    use HasFactory;

    protected $fillable = [
       'course_code',
       'course_title',
       'specialty_id',
       'department_id',
       'school_branch_id',
       'credit',
       'semester',
       'level_id'
    ];

    public $keyType = 'string';
    public $table = 'courses';
    public $incrementing = 'false';

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class);
    }

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }

    public function marks(): HasMany {
        return $this->hasMany(Marks::class, 'courses_id');
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function examtimetable(): BelongsTo {
        return $this->belongsTo(Examtimetable::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class);
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class);
    }

    public function timetable(): HasMany {
        return $this->hasMany(Timetable::class);
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
