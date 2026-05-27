<?php

namespace App\Models\SemesterTimetable;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class SemesterTimetable extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'semester_timetable';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_version_id',
        'school_semester_id',
        'school_branch_id',
        'status',
        'timetable_slots',
        'request_payload',
        'raw_diagnostics',
        'parsed_diagnostics',
        'options_pool',
        'raw_suggestions',
        'parsed_suggestions'
    ];

    public function getTable()
    {
        return 'semester_timetable';
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }

    public function scopeForVersion(object $query, string $versionId)
    {
        return $query->where('timetable_version_id', $versionId);
    }
    public function scopeForSemester(object $query, string $schoolSemesterId)
    {
        return $query->where('school_semester_id', $schoolSemesterId);
    }
}
