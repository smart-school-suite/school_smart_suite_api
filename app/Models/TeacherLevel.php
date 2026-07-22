<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\Pivot;
class TeacherLevel extends Pivot
{
    use HasUuids;
    protected $fillable = [
          'teacher_id',
          'level_id',
          'school_branch_id'
    ];

    public $incrementing = false;
    public $table = 'teacher_levels';
    public $keyType = 'string';


}
