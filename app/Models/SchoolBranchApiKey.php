<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBranchApiKey extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
        'school_branch_id',
        'api_key',
        'current_num_school_admins',
        'current_num_students',
        'current_num_parents',
        'current_number_teacher'
    ];

    public $incrementing = 'false';
    public $table = 'school_branch_api_keys';
    public $keyType = 'string';

    public function schoolBranch(): BelongsTo {
     return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
}
