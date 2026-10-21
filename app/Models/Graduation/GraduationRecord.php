<?php

namespace App\Models\Graduation;

use App\Models\Promotion\Alumni;
use App\Models\Promotion\PromotionRun;
use App\Models\Schoolbranches;
use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GraduationRecord extends Model
{
    use HasUuids, HasFactory;
    protected $fillable = [
        'promotion_run_id',
        'student_id',
        'authorable_id',
        'authorable_type',
        'reviewed_at',
        'status',
        'eligibility_status',
        'clearance_status',
        'admin_note',
        'meta_data',
        'specialty_id',
        'school_branch_id',
        'batch_id'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime'
    ];
    public $incrementing = false;
    public $table = 'graduation_records';
    public $keyType = 'string';

    public function alumni(): HasMany
    {
        return $this->hasMany(Alumni::class, 'record_id');
    }
    public function schoolBranch(): BelongsTo
    {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    public function batch(): BelongsTo
    {
        return $this->belongsTo(GraduationBatch::class, 'batch_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function authorable(): MorphTo
    {
        return $this->morphTo('authorable', 'authorable_type', 'authorable_id');
    }
    public function promotionRun(): BelongsTo
    {
        return $this->belongsTo(PromotionRun::class, 'promotion_run_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
