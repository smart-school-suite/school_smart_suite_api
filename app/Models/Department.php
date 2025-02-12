<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;


class Department extends Model
{
    use HasFactory;

    protected $fillable = [
      'school_branch_id',
      'department_name',
      'description',
      'status',
    ];

    public $keyType = 'string';
    public $table = 'department';
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

    public function student(): HasMany {
      return $this->hasMany(Student::class);
    }

    public function teacher(): HasMany {
      return $this->hasMany(Teacher::class);
    }

    public function events(): HasMany {
      return $this->hasMany(Events::class);
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
