<?php

namespace App\Constant\Authorization\Roles;

class SchoolAdminRoles
{
   public const SCHOOL_ADMIN = "schoolAdmin";
   public const SCHOOL_SUPER_ADMIN = "schoolSuperAdmin";
   public static function all(): array {
       return [
           self::SCHOOL_ADMIN,
           self::SCHOOL_SUPER_ADMIN
       ];
   }
}
