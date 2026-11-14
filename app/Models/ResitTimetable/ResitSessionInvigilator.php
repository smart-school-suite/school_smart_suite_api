<?php

namespace App\Models\ResitTimetable;

use App\Models\Resitexamtimetable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ResitSessionInvigilator extends Model
{
    use HasUuids;

    protected $fillable = ['resit_slot_id', 'resit_session_hall_id', 'invigilator_id', 'school_branch_id'];

    public $table = 'resit_session_invigs';
    public $incrementing = false;
    public $keyType = 'string';

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Resitexamtimetable::class, 'resit_slot_id');
    }

    public function sessionHall(): BelongsTo
    {
        return $this->belongsTo(ResitSessionHall::class, 'resit_session_hall_id');
    }

    public function invigilator(): BelongsTo
    {
        return $this->belongsTo(ResitInvigilator::class, 'invigilator_id');
    }
}
