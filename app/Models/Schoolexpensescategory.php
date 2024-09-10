<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Schoolexpensescategory extends Model
{
    use HasFactory;

    protected $fillable = [
      'school_branch_id',
      'name'
    ];

    public $table = 'school_expenses_category';
    public $incrementing = 'false';
    public $keyType = 'string';

    protected static function boot()
    {
        parent::boot();
       
         static::creating(function ($user){
            $uuid = str_replace('-', '', Str::uuid()->toString());
            $user->id = substr($uuid, 0, 10);
         });
      
    }

    public function schoolexpenses(): BelongsTo {
      return $this->belongsTo(SchoolExpenses::class, 'expenses_category_id');
    }
}
