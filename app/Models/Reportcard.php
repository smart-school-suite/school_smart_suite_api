<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Reportcard extends Model
{
    use HasFactory;

    protected $fillable = [
       'school_branch_id',
       'student_id',
       'exam_id',
       'specialty_id',
       'gpa',
       'total_score',
       'department_id',
       'level_id',
       'student_records'
    ];

    public $table = 'report_card';
    public $incrementing = 'false';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function student() : BelongsTo {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function level(): HasMany {
        return $this->hasMany(Educationlevels::class, 'level_id');
    }

    public function specialty(): HasMany {
        return $this->hasMany(Specialty::class, 'specialty_id');
    }

    public function department(): HasMany {
        return $this->hasMany(Department::class, 'department_id');
    }
    
}
