<?php

namespace App\Constant\Authorization\Permissions\Teacher;

class TeacherPermissions
{
   public const CREATE = "teacher.create";
   public const VIEW = "teacher.view";
   public const UPDATE =  "teacher.update";
   public const DELETE = "teacher.delete";
   public const DEACTIVATE = "teacher.deactivate";
   public const ACTIVATE = "teacher.activate";
   public const PROFILE_UPDATE = "teacher.profile_update";
   public const AVATAR_DELETE = "teacher.avatar_delete";
   public const AVATAR_UPLOAD = "teacher.avatar_upload";
   public const CHANGE_PASSWORD = "teacher.change_password";
   public static function all(): array {
      return  [
         self::CREATE,
         self::UPDATE,
         self::DELETE,
         self::ACTIVATE,
         self::DEACTIVATE,
         self::PROFILE_UPDATE,
         self::AVATAR_UPLOAD,
         self::AVATAR_DELETE,
         self::CHANGE_PASSWORD
      ];
   }
}
