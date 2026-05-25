<?php

namespace App\Models\ExamJointCourse;

use App\Models\ExamTimetable\ExamInvigilator;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamJCSessionInvig extends Model
{
    use HasUuids;
    protected $fillable = [
        'exam_jc_session_hall_id',
        'invigilator_id'
    ];

    public $incrementing = false;
    public $table = "exam_session_jc_invigs";
    public $keyType = 'string';

    public function examInvigilator(): BelongsTo
    {
        return $this->belongsTo(ExamInvigilator::class, 'invigilator_id');
    }
    public function examJCSessionHall(): BelongsTo
    {
        return $this->belongsTo(ExamJCSessionHall::class, 'exam_jc_session_hall_id');
    }
}
