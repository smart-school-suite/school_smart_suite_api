<?php

namespace App\Models\ExamTimetable;

use App\Models\Hall;
use App\Models\Specialty;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamSessionHall extends Model
{
    use HasUuids;
    protected $fillable = [
        'candidate_count',
        'exam_slot_id',
        'specialty_id',
        'hall_id',
        'school_branch_id'
    ];

    protected $casts = [
        'candidate_count' => "integer"
    ];
    public $incrementing = false;
    public $table = "exam_session_halls";
    public $keyType = 'string';

    public function slot(): BelongsTo
    {
        return $this->belongsTo(ExamTimetableSlot::class, 'exam_slot_id');
    }
    public function specialty(): BelongsTo
    {
        return $this->belongsTo(Specialty::class, 'specialty_id');
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

}
