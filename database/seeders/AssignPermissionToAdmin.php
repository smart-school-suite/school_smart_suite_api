<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Log;
use App\Models\Schooladmin;
use Illuminate\Database\Seeder;

class AssignPermissionToAdmin extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //

        Log::info('Starting role assignment for school admins...');
        $allPermissionNames = Permission::pluck('name')->toArray();
        $schooladmin = Schooladmin::find("4dcf27a33ff77eb03bcc88612");
        $schooladmin->syncPermissions($allPermissionNames);

    }
}
