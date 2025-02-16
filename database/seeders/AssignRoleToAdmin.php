<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Schooladmin;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Seeder;

class AssignRoleToAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        try {
            $role = Role::where("name", "schoolSuperAdmin")->get();
            $schooladmin = Schooladmin::find("4dcf27a33ff77eb03bcc88612");

            Log::info('Starting role assignment for school admins...'); // Log the start of the process
            Log::info($role);
            $schooladmin->assignRole("schoolSuperAdmin");

            Log::info('Successfully assigned "schoolSuperAdmin" role to all school admins.'); // Log successful completion
        } catch (\Exception $e) {
            Log::error('Error assigning "schoolSuperAdmin" role to school admins: ' . $e->getMessage()); // Log any errors
        }
    }
}
