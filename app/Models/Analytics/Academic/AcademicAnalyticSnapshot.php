<?php

namespace App\Models\Analytics\Academic;

use MongoDB\Laravel\Eloquent\Model;

class AcademicAnalyticSnapshot extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'academic_analytics_snapshots';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'dimensions' => 'array',
        'value' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function scopeForKpi($query, string $kpi)
    {
        return $query->where('kpi', $kpi);
    }

    public function getTable()
    {
        return 'academic_analytics_snapshots';
    }

    public function newEloquentBuilder($query)
    {
        return new \MongoDB\Laravel\Eloquent\Builder($query);
    }

    public function qualifyColumn($column)
    {
        return $column;
    }
}
