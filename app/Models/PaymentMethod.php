<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\GeneratesUuid;
class PaymentMethod extends Model
{
    use GeneratesUuid;
   protected $fillable = [
      'country_id',
      'category_id',
      'name',
      'status',
      'description',
      'max_deposit',
      'max_withdraw',
      'operator_img',
      'key'
   ];

   public $table = "payment_method";
   public $incrementing = false;
   public $keyType = "string";

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_id');
    }
    public function category(): BelongsTo {
         return $this->belongsTo(PaymentMethodCategory::class, 'category_id');
    }

    public function schoolTransaction(): HasMany {
         return $this->hasMany(SchoolTransaction::class);
    }

}
