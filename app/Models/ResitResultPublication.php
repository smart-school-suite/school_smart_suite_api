<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** When a resit exam's results are (or will be) released to students. */
class ResitResultPublication extends Model
{
    use HasUuids;

    protected $fillable = [
        'resit_exam_id',
        'school_branch_id',
        'published_at',
        'authorable_id',
        'authorable_type',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public $incrementing = false;
    public $table = 'resit_result_publications';
    public $keyType = 'string';

    public function resitExam(): BelongsTo
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }
}
