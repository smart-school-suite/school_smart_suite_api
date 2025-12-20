<?php

namespace App\Models\Analytics;

use MongoDB\Laravel\Eloquent\Model;

class FinancialAnalyticsTimeSeries extends Model
{
    protected $connection = 'mongodb';
    protected $table = 'financial_analytics_timeseries';
    protected $primaryKey = '_id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = [];
    protected $casts = [
        'dimensions' => 'array',
        'value' => 'float',
        'updated_at' => 'datetime',
    ];

    public function scopeForKpi($query, string $kpi)
    {
        return $query->where('kpi', $kpi);
    }

    public function getTable()
    {
        return 'financial_analytics_timeseries';
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
