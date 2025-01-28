<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Seeder;

class assignPermissionToSuperAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Fetch the Super Admin role
        $superAdminRole = Role::where('name', 'schoolSuperAdmin')->first();

        if ($superAdminRole) {
            // Fetch all permissions and assign them to the Super Admin role
            $allPermissions = Permission::all();
            $superAdminRole->syncPermissions($allPermissions);
        } else {
            echo "Super Admin role does not exist.\n";
        }
    }
}
