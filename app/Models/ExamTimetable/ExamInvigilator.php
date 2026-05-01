<?php

namespace App\Models\ExamTimetable;

use App\Models\Exams;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamInvigilator extends Model
{
    use HasUuids;
    protected $fillable = [
        'invigilator_id',
        'school_branch_id',
        'exam_id'
    ];

    public $table = "exam_invigs";
    public $incrementing = false;
    public $keyType = 'string';

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(Invigilator::class, 'invigilator_id');
    }

    public function examSessionInvig(): HasMany
    {
        return $this->hasMany(ExamSessionInvigilator::class);
    }

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exams::class, 'exam_id');
    }
}
