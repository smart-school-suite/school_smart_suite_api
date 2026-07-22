<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;
use App\Models\Schooladmin;
use Spatie\Permission\Models\Permission;

class AssignPermissionToRoles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $this->assignPermissionToRoles();

    }

    private function assignPermissionToRoles(){
        $superAdminRole = Role::where('name', 'schoolSuperAdmin')->firstOrFail();
        $schoolAdminPermissions = Permission::where("guard_name", "schooladmin")
                                              ->pluck('name')->toArray();
        $superAdminRole->givePermissionTo($schoolAdminPermissions);

        $studentRole = Role::where('name', 'student')->firstOrFail();
        $studentPermissions = Permission::where("guard_name", "student")
                                           ->pluck("name")->toArray();
        $studentRole->givePermissionTo($studentPermissions);

        $teacherRole = Role::where("name", "teacher")->firstOrFail();
        $teacherPermissions = Permission::where("guard_name", "teacher")
                                          ->pluck("name")->toArray();
        $teacherRole->givePermissionTo($teacherPermissions);
    }

}
