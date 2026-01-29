<?php

namespace App\Models\AcademicYear;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Model;
use MongoDB\Laravel\Relations\HasMany;

class SystemAcademicYear extends Model
{
    use GeneratesUuid;
    protected $fillable = [
        'name',
        'year_start',
        'year_end',
    ];

    protected $table = 'system_academic_years';
    public $incrementing = false;
    public $keyType = 'string';

    public function schoolAcademicYear(): HasMany
    {
        return $this->hasMany(SchoolAcademicYear::class, 'school_year_id');
    }
}
