<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SchoolBranchApiKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_branch_id',
        'api_key',
        'current_num_school_admins',
        'current_num_students',
        'current_num_parents',
        'current_number_teacher'
    ];

    public $incrementing = 'false';
    public $table = 'schoolbranch_apikey';
    public $keyType = 'string';

    public function schoolBranch(): BelongsTo {
     return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }
    protected static function boot()
    {
        parent::boot();

         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 35);
         });

    }
}
