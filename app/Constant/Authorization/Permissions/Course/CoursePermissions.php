<?php

namespace App\Constant\Authorization\Permissions\Course;

class CoursePermissions
{
   public const CREATE = "course.create";
   public const UPDATE = "course.update";
   public const DELETE = "course.delete";
   public const VIEW = "course.view";
   public const ACTIVATE = "course.activate";
   public const DEACTIVATE = "course.deactivate";

   public static function all(): array {
       return  [
           self::CREATE,
           self::UPDATE,
           self::DELETE,
           self::VIEW,
           self::ACTIVATE,
           self::DEACTIVATE
       ];
   }
}
