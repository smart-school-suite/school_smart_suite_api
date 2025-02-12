<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Educationlevels extends Model
{
    use HasFactory;

    protected $fillable = [
       'name',
       'level',
       'status'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'education_levels';

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });

    }

    public function specialty() : HasMany {
       return $this->hasMany(Specialty::class);
    }

    public function mark() : HasMany {
      return $this->hasMany(Marks::class, 'level_id');
    }

    public function student(): HasMany {
       return $this->hasMany(Student::class, 'level_id');
    }
    public function courses(): HasMany {
       return $this->hasMany(Courses::class);
    }
    public function timetable(): HasMany {
       return $this->hasMany(Timetable::class);
    }
    public function exam(): HasMany {
       return $this->hasMany(Exams::class);
    }
    public function studentresit(): HasMany {
       return $this->hasMany(Studentresit::class);
    }
}
