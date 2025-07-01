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
        $this->command->info("Assigning Permission To School Super Admin Role.............................0%");
        $superAdminRole = Role::where('name', 'schoolSuperAdmin')->firstOrFail();
        $schoolAdminPermissions = Permission::where("guard_name", "schooladmin")
                                              ->pluck('name')->toArray();
        $superAdminRole->givePermissionTo($schoolAdminPermissions);
        $this->command->info("Permissions Assigned Successfully.............................100%");

        $this->command->info("Assigning Permission To Student Role.............................0%");
        $studentRole = Role::where('name', 'student')->firstOrFail();
        $studentPermissions = Permission::where("guard_name", "student")
                                           ->pluck("name")->toArray();
        $studentRole->givePermissionTo($studentPermissions);
        $this->command->info("Permissions Assigned Successfully.............................100%");

        $this->command->info("Assigning Permission To Teacher Role.............................0%");
        $teacherRole = Role::where("name", "teacher")->firstOrFail();
        $teacherPermissions = Permission::where("guard_name", "teacher")
                                          ->pluck("name")->toArray();
        $teacherRole->givePermissionTo($teacherPermissions);
        $this->command->info("Permissions Assigned Successfully....................................100%");
    }

    private function assignRoleToAdmin() {
        $this->command->info("Started Assigning Role to admin");
        $schoolAdmin = Schooladmin::where("email", "chongongprecious@gmail.com")->firstOrFail();
        $schoolAdmin->assignRole("schoolSuperAdmin");
        $this->command->info("Assigned School Super admin Role To school admin Successfully");
    }
}
