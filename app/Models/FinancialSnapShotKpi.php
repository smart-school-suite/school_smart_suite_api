<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialSnapShotKpi extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'events';

    protected $fillable = [
         'school_branch_id',
         'kpi',
         'updated_at'
    ];

    protected $casts = [
         'updated_at' => 'datetime'
    ];
}
