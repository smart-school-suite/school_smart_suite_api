<?php

namespace App\Constant\Authorization\Roles;

class StudentRoles
{
   public const STUDENT = "student";
   public static function all(): array {
       return [
         self::STUDENT
       ];
   }
}
