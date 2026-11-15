<?php

namespace App\Models\ResitTimetable;

use App\Models\ResitExam;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** A teacher or school admin who invigilates a resit exam. */
class ResitInvigilator extends Model
{
    use HasUuids;

    protected $fillable = [
        'school_branch_id',
        'resit_exam_id',
        'actorable_id',
        'actorable_type',
    ];

    public $table = 'resit_invigilators';
    public $incrementing = false;
    public $keyType = 'string';

    public function resitExam(): BelongsTo
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }

    public function invigilatable(): MorphTo
    {
        return $this->morphTo('invigilatable', 'actorable_type', 'actorable_id');
    }

    public function sessionInvigilators(): HasMany
    {
        return $this->hasMany(ResitSessionInvigilator::class, 'invigilator_id');
    }
}
