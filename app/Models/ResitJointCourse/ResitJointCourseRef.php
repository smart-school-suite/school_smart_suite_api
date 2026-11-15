<?php

namespace App\Models\ResitJointCourse;

use App\Models\ResitExam;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Links a resit exam to a joint resit it takes part in. */
class ResitJointCourseRef extends Model
{
    use HasUuids;

    protected $fillable = ['school_branch_id', 'resit_exam_id', 'resit_jc_id'];

    public $incrementing = false;
    public $table = 'resit_jc_refs';
    public $keyType = 'string';

    public function resitExam(): BelongsTo
    {
        return $this->belongsTo(ResitExam::class, 'resit_exam_id');
    }

    public function jointCourse(): BelongsTo
    {
        return $this->belongsTo(ResitJointCourse::class, 'resit_jc_id');
    }
}
