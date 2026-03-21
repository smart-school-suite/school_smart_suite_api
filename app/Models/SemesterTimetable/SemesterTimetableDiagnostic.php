<?php

namespace App\Models\SemesterTimetable;

use MongoDB\Laravel\Eloquent\Model;
use MongoDB\Laravel\Eloquent\Builder;

class SemesterTimetableDiagnostic extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'timetable_diagnostics';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'timetable_version_id',
        'school_semester_id',
        'status',
        'summary',
        'violations',
        'constraint_modification_suggestions',
        'blocker_resolution_suggestions',
        'meta',
        'generated_at',
        'diagnostic_hash'
    ];

    protected $casts = [
        'generated_at' => 'datetime',
    ];

    public function getTable()
    {
        return 'timetable_diagnostics';
    }

    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }

    public function scopeForVersion($query, $versionId)
    {
        return $query->where('timetable_version_id', $versionId);
    }
    public function scopeForSemester($query, $schoolSemesterId)
    {
        return $query->where('school_semester_id', $schoolSemesterId);
    }
}
