<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StudentDropout extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'level_id',
        'reason',
        'specialty_id',
        'school_branch_id',
        'student_batch_id',
        'department_id'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'student_dropout';

    public function student(): BelongsTo {
         return $this->belongsTo(Student::class, 'student_id');

    }

    public function level(): BelongsTo {
         return $this->belongsTo(Educationlevels::class, 'level_id');
    }

    public function specialty(): BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function schoolBranch(): BelongsTo {
         return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function studentBatch(): BelongsTo {
         return $this->belongsTo(StudentBatch::class, 'student_batch_id');
    }

    public function department(): BelongsTo {
         return $this->belongsTo(Department::class, 'department_id');
    }

    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 30);
         });

    }
}
