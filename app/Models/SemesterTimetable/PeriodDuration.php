<?php

namespace App\Models\SemesterTimetable;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class PeriodDuration extends Model
{
    use HasUuids;
    protected $fillable = [
        'name',
        'minutes',
        'description',
        'status'
    ];

    public $incrementing = false;
    public $keyType = 'string';
    public $table = "period_durations";
}
