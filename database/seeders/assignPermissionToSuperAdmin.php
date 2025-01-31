<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Role;
use Illuminate\Support\Facades\Log;
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
        Log::info( $superAdminRole);
        if ($superAdminRole) {
            // Fetch all permissions and assign them to the Super Admin role
            $allPermissionNames = Permission::pluck('name')->toArray();

            $superAdminRole->givePermissionTo($allPermissionNames);

        } else {
            echo "Super Admin role does not exist.\n";
        }
    }
}
