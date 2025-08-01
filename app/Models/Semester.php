<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Semester extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'count',
        'program_name'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'semesters';

    public function exams(): HasMany {
        return $this->hasMany(Exams::class);
    }

    public function resitExam(): HasMany {
        return $this->hasMany(ResitExam::class);
    }
    public function examtype(): HasMany {
        return $this->hasMany(Examtype::class);
    }

    public function timetable(): BelongsTo {
        return $this->belongsTo(Timetable::class, 'semeter_id');
    }
    public function courses(): HasMany {
         return $this->hasMany(Courses::class);
    }
}
