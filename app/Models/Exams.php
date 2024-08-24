<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Exams extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branches_id',
        'exam_name',
        'start_date',
        'end_date',
        'level_id',
        'weighted_mark',
        'semester'
    ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'exams';
   
    public function courses(): BelongsTo {
        return $this->belongsTo(Exams::class);
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

    public function student(): BelongsTo {
        return $this->belongsTo(Student::class);
    }

    public function grade(): HasMany {
        return $this->hasMany(Grades::class);
    }

    
}
