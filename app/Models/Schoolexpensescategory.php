<?php

namespace App\Models;

use App\Traits\GeneratesUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schoolexpensescategory extends Model
{
    use HasFactory, GeneratesUuid;

    protected $fillable = [
      'school_branch_id',
      'name',
      'description'
    ];

    public $table = 'expense_categories';
    public $incrementing = 'false';
    public $keyType = 'string';


    public function schoolexpenses(): HasMany {
      return $this->hasMany(SchoolExpenses::class, 'expenses_category_id');
    }
}
