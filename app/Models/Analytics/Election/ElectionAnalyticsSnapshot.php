<?php

namespace App\Models\Analytics\Election;

use MongoDB\Laravel\Eloquent\Model;

class ElectionAnalyticsSnapshot extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'election_analytics_snapshot';
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
        return 'election_analytics_snapshot';
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
