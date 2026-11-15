<?php

namespace App\Models\ResitJointCourse;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The one time a joint resit is sat. Times are read raw ("HH:MM:SS"). */
class ResitJointCourseSlot extends Model
{
    use HasUuids;

    protected $fillable = ['school_branch_id', 'resit_jc_id', 'date', 'start_time', 'end_time'];

    protected $casts = ['date' => 'date'];

    public $incrementing = false;
    public $table = 'resit_jc_slots';
    public $keyType = 'string';

    public function jointCourse(): BelongsTo
    {
        return $this->belongsTo(ResitJointCourse::class, 'resit_jc_id');
    }
}
