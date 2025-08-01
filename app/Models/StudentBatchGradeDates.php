<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class StudentBatchGradeDates extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'name',
        'graduation_date',
        'specailty_id',
        'level_id',
        'student_batch_id'
    ];

    public $incrementing = 'false';
    public $table = 'studentbatch_grad_dates';
    public $keyType = 'string';

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
}
