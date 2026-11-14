<?php

namespace App\Models\ResitTimetable;

use App\Models\Hall;
use App\Models\Resitexamtimetable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResitSessionHall extends Model
{
    use HasUuids;

    protected $fillable = ['candidate_count', 'resit_slot_id', 'hall_id', 'school_branch_id'];

    protected $casts = ['candidate_count' => 'integer'];

    public $table = 'resit_session_halls';
    public $incrementing = false;
    public $keyType = 'string';

    public function slot(): BelongsTo
    {
        return $this->belongsTo(Resitexamtimetable::class, 'resit_slot_id');
    }

    public function hall(): BelongsTo
    {
        return $this->belongsTo(Hall::class, 'hall_id');
    }

    public function invigilators(): HasMany
    {
        return $this->hasMany(ResitSessionInvigilator::class, 'resit_session_hall_id');
    }
}
