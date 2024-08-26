<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialty extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id',
        'specialty_name',
        'registration_fee',
        'school_fee',
        'level',
        'school_branch_id',
    ];

    public $keyType = 'string';
    public $table = 'specialty';
    public $incrementing = 'false';

    public function department(): BelongsTo {
        return $this->belongsTo(Department::class);
    }
   
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
}
