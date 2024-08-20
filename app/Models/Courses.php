<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Courses extends Model
{
    use HasFactory;

    protected $fillable = [
       'course_code',
       'course_title',
       'specialty_id',
       'department_id',
       'credit',
       'semester',
       'level'
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
        return $this->hasMany(Marks::class);
    }

    public function school(): BelongsTo {
        return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class);
    }

    public function specialty(): BelongsTo {
        return $this->belongsTo(Specialty::class);
    }

    public function teacher(): BelongsTo {
        return $this->belongsTo(Teacher::class);
    }
}
