<?php

namespace App\Services\Analytics\Academic\Aggregates\Resit;

use Illuminate\Support\Collection;

class ResitTotalAggregate
{
     public static function calculate(Collection $query){
         $resitTotal = $query->sum("value");
         return $resitTotal;
     }
}
