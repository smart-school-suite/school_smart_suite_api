<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Studentbatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'status',
        'description',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'student_batch';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

    public function studentGradDates() : HasMany {
        return $this->hasMany(StudentBatchGradeDates::class);
   }
    public function studentDropout(): HasMany {
         return $this->hasMany(StudentDropout::class);
    }
    public function schoolBranch(): HasMany {
         return $this->hasMany(Schoolbranches::class);
    }
    public function schoolSemester(): HasMany {
         return $this->hasMany(SchoolSemester::class);
    }
    public function studentResults(): HasMany {
         return $this->hasMany(StudentResults::class);
    }

    public function exams() :  HasMany {
         return $this->hasMany(Exams::class);
    }
    public function student(): HasMany {
        return $this->hasMany(Student::class, 'student_batch_id');
    }
}
