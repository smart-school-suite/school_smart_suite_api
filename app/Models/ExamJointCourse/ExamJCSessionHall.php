<?php

namespace App\Models\ExamJointCourse;

use App\Models\Hall;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamJCSessionHall extends Model
{
    use HasUuids;
    protected $fillable = [
        'school_branch_id',
        'hall_id',
        'specialty_id',
        'exam_jc_slot_id',
        'candidate_count'
    ];

    protected $casts = [
        'candidate_count' => 'integer'
    ];
    public $incrementing = false;
    public $keyType = 'string';
    public $table = 'exam_jc_session_halls';

    public function examJCSessionInvig(): HasMany
    {
        return $this->hasMany(ExamJCSessionInvig::class);
    }
    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }
    public function examJcSlot(): BelongsTo
    {
        return $this->belongsTo(ExamJointCourseSlot::class, 'exam_jc_slot_id');
    }
}
