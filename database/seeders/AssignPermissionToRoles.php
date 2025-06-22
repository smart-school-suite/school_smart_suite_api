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
        $this->assignRoleToAdmin();

    }

    private function assignPermissionToRoles(){
        $this->command->info("Started Assigning Permission To Super Admin Role");
        $superAdminRole = Role::where('name', 'schoolSuperAdmin')->firstOrFail();
        $schoolAdminPermissions = Permission::where("guard_name", "schooladmin")
                                              ->pluck('name')->toArray();
        $superAdminRole->givePermissionTo($schoolAdminPermissions);
        $this->command->info("Assigned Permissions To Super Admin Role Successfully");

        $this->command->info("Started Assigning Permission To Students");
        $studentRole = Role::where('name', 'student')->firstOrFail();
        $studentPermissions = Permission::where("guard_name", "student")
                                           ->pluck("name")->toArray();
        $studentRole->givePermissionTo($studentPermissions);
        $this->command->info("Assigned Permissions To Student Role Successfully");

        $this->command->info("Started Assigning Permissions to Teachers");
        $teacherRole = Role::where("name", "teacher")->firstOrFail();
        $teacherPermissions = Permission::where("guard_name", "teacher")
                                          ->pluck("name")->toArray();
        $teacherRole->givePermissionTo($teacherPermissions);
        $this->command->info("Assigned Permissions To teacher Successfully");
    }

    private function assignRoleToAdmin() {
        $this->command->info("Started Assigning Role to admin");
        $schoolAdmin = Schooladmin::where("email", "chongongprecious@gmail.com")->firstOrFail();
        $schoolAdmin->assignRole("schoolSuperAdmin");
        $this->command->info("Assigned School Super admin Role To school admin Successfully");
    }
}
