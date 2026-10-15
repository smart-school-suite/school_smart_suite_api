<?php

namespace App\Models\Exam;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ExamResultPublication extends Model
{
    use HasFactory, HasUuids;
    protected $fillable = [
        'exam_id',
        'school_branch_id',
        'published_at',
        'authorable_id',
        'authorable_type'
    ];

    public $incrementing = false;
    public $table = 'exam_result_publications';
    public $keyType = 'string';

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class, 'exam_id');
    }

    public function authorable(): MorphTo
    {
        return $this->morphTo(
            name: 'author',
            type: 'authorable_type',
            id: 'authorable_id'
        );
    }
}
