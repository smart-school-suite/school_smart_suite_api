<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class LevelType extends Model
{
    use GeneratesUuid;
   protected $fillable = [
       'name',
       'description',
       'program_name'
   ];

    public $keyType = 'string';
    public $incrementing = 'false';
    public $table = 'level_types';

    public function level(): HasMany {
         return $this->hasMany(Educationlevels::class);
    }
}
