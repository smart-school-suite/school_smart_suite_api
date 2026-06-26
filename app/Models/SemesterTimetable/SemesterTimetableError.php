<?php

namespace App\Models\SemesterTimetable;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class SemesterTimetableError extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'semester_timetable_error';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'timetable_version_id',
        'school_semester_id',
        'school_branch_id',
        'request_payload',
        "errors"
    ];

    public function getTable()
    {
        return 'semester_timetable_error';
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
