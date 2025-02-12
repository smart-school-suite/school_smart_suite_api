<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolSemester extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'school_year_start',
        'school_year_end',
        'semester_id',
        'specialty_id',
        'school_branch_id'
    ];

    public $incrementing = 'false';
    public $table = 'school_semesters';
    public $keyType = 'string';

    public function specailty(): BelongsTo {
         return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function semester(): BelongsTo {
        return $this->belongsTo(Semester::class, 'semester_id');
    }

    public function schoolBranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
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
