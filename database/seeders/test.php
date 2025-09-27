<?php

namespace Database\Seeders;

use App\Models\InstructorAvailability;
use App\Models\InstructorAvailabilitySlot;
use App\Models\RegistrationFee;
use App\Models\Schooladmin;
use App\Models\SchoolSemester;
use App\Models\Specialty;
use App\Models\TeacherSpecailtyPreference;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Jobs\NotificationJobs\SendAdminResitExamCreatedNotificationJob;
use App\Models\Exams;
use App\Models\AccessedStudent;
use App\Models\Schoolbranches;
use App\Models\Examtype;
use Faker\Factory as Faker;
use App\Models\Hall;

class test extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schoolBranch = Schoolbranches::first();

        if (!$schoolBranch) {
            $this->command->info('No school branches found. Please seed the school_branches table first.');
            return;
        }

        $faker = Faker::create();

        foreach (range(1, 100) as $index) {
            Hall::create([
                'name' => 'Hall ' . chr(65 + ($index - 1) % 26) . (($index - 1) < 26 ? '' : floor(($index - 1) / 26)),
                'capacity' => $faker->numberBetween(50, 300),
                'status' => 'available',
                'location' => $faker->address,
                'school_branch_id' => $schoolBranch->id,
            ]);
        }

        $this->command->info('100 halls seeded successfully!');
    }
}
