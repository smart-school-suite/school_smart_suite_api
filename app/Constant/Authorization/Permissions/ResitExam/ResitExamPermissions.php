<?php

namespace App\Constant\Authorization\Permissions\ResitExam;

class ResitExamPermissions
{
   public const VIEW = "resit_exam.view";
   public const UPDATE = "resit_exam.update";
   public const DELETE = "resit_exam.delete";

   public static function all(): array {
       return [
          self::VIEW,
          self::UPDATE,
          self::DELETE
       ];
   }
}
