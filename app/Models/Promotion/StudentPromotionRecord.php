<?php

namespace App\Models\Promotion;

use App\Models\Specialty;
use App\Models\Student;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StudentPromotionRecord extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'outcome',
        'status',
        'processed_at',
        'reviewed_at',
        'authorable_type',
        'authorable_id',
        'admin_note',
        'metadata',
        'promotion_run_id',
        'student_id',
        'from_specialty_id',
        'to_specialty_id',
        'school_branch_id'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'student_promotion_records';

    public function promotionRun(): BelongsTo
    {
        return $this->belongsTo(PromotionRun::class, 'promotion_run_id');
    }
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function fromSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'from_specialty_id');
    }
    public function toSpecialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'to_specialty_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo('author', 'authorable_type', 'authorable_id');
    }
}
