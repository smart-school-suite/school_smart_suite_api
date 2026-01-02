<?php

namespace App\Constant\Authorization\Permissions\AdditionalFee;

class AdditionalFeePermission
{
   public const PAY = "additional_fee.pay";
   public const BILL = "additional_fee.bill";
   public const DELETE = "additional_fee.delete";
   public const VIEW = "additional_fee.view";
   public const UPDATE = "additional_fee.update";

   public  static function all():  array {
       return [
         self::PAY,
         self::BILL,
         self::DELETE,
         self::VIEW,
         self::UPDATE
       ];
   }
}
