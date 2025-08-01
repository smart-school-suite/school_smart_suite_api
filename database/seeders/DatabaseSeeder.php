<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PermissionCategorySeeder::class);
        $this->call(RoleSeeder::class);
        $this->call(PermissionSeeder::class);
        $this->call(StarterPackSeeder::class);
        $this->call(StatTypeSeeder::class);
        $this->call(AssignPermissionToRoles::class);
        $this->call(InstallmentSeeder::class);
        $this->call(AnnouncementSeeder::class);
        $this->call(StudentBadgeSeeder::class);
    }
}
