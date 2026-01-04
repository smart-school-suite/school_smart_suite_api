<?php

namespace App\Constant\System;

class Guards
{
   public const SCHOOL_ADMIN = "schoolAdmin";
   public const STUDENT = "student";
   public const TEACHER = "teacher";
   public const APP_ADMIN = "appAdmin";

   public static function all(): array {
       return [
          self::SCHOOL_ADMIN,
          self::STUDENT,
          self::TEACHER
       ];
   }
}
