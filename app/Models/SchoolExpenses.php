<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class SchoolExpenses extends Model
{
    use HasFactory;

    protected $fillable = [
        'id',
        'expenses_category_id',
        'school_branch_id',
        'date',
        'amount',
        'description'
    ];

    public $incrementing = 'false';
    public $keyType = 'string';
    public $table = 'school_expenses';

    public function schoolbranch(): BelongsTo {
        return $this->belongsTo(Schoolbranches::class, 'school_branch_id');
    }

    public function schoolexpensescategory(): BelongsTo {
        return $this->belongsTo(Schoolexpensescategory::class, 'expenses_category_id');
    }

}
