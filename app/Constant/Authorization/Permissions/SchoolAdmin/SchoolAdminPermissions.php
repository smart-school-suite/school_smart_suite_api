<?php

namespace App\Constant\Authorization\Permissions\SchoolAdmin;

class SchoolAdminPermissions
{
   public const CREATE = "school_admin.create";
   public const UPDATE =  "school_admin.update";
   public const DELETE = "school_admin.delete";
   public const DEACTIVATE = "school_admin.deactivate";
   public const ACTIVATE = "school_admin.activate";
   public const ASSIGN_PERMISSION = "school_admin.assign_permission";
   public const REVOKE_PERMISSION = "school_admin.revoke_permission";
   public const ASSIGN_ROLE = "school_admin.assign_role";
   public const REVOKE_ROLE = "school_amdin.revoke_role";
   public const PROFILE_UPDATE = "school_admin.profile_update";
   public const AVATAR_DELETE = "school_admin.avatar_delete";
   public const AVATAR_UPLOAD = "school_admin.avatar_upload";
   public const CHANGE_PASSWORD = "school_admin.change_password";
   public static function all(): array {
      return  [
         self::CREATE,
         self::UPDATE,
         self::DELETE,
         self::ACTIVATE,
         self::DEACTIVATE,
         self::ASSIGN_PERMISSION,
         self::REVOKE_PERMISSION,
         self::ASSIGN_ROLE,
         self::REVOKE_ROLE,
         self::PROFILE_UPDATE,
         self::AVATAR_UPLOAD,
         self::AVATAR_DELETE,
         self::CHANGE_PASSWORD
      ];
   }
}
