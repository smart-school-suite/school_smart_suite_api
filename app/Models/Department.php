<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;

class Department extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
      'id',
      'school_branch_id',
      'department_name',
      'description',
      'status',
    ];

    public $keyType = 'string';
    public $table = 'departments';
    public $incrementing = 'false';

    public function courses(): HasMany {
       return $this->hasMany(Courses::class);
    }

    public function exams(): HasMany {
      return $this->hasMany(Exams::class);
    }

    public function school(): BelongsTo {
      return $this->belongsTo(School::class);
    }

    public function schoolbranches(): BelongsTo {
      return $this->belongsTo(Schoolbranches::class);
    }

    public function specialty(): HasMany {
      return $this->hasMany(Specialty::class);
    }

    public function students(): HasManyThrough {
         return $this->hasManyThrough(Student::class, Specialty::class, 'department_id', 'specialty_id', 'id', 'id');
    }

    public function teacher(): HasMany {
      return $this->hasMany(Teacher::class);
    }

    public function getTeachersAttribute(): Collection
    {
        $this->loadMissing('specialties.teachers');

        return $this->specialties
            ->flatMap->teachers
            ->unique('id')
            ->values();
    }

}
