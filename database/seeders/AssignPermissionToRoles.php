<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use App\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignPermissionToRoles extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->assignPermissionToRoles();
    }

    private function assignPermissionToRoles(): void
    {
        $superAdminRole = Role::where('name', 'schoolSuperAdmin')->firstOrFail();
        $schoolAdminPermissions = Permission::where('guard_name', 'schooladmin')
                                            ->pluck('name')->toArray();
        $superAdminRole->syncPermissions($schoolAdminPermissions);

        $studentRole = Role::where('name', 'student')->firstOrFail();
        $studentPermissions = Permission::where('guard_name', 'student')
                                        ->pluck('name')->toArray();
        $studentRole->syncPermissions($studentPermissions);

        $teacherRole = Role::where('name', 'teacher')->firstOrFail();
        $teacherPermissions = Permission::where('guard_name', 'teacher')
                                        ->pluck('name')->toArray();
        $teacherRole->syncPermissions($teacherPermissions);
    }
}
