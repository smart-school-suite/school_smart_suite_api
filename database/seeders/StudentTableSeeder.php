<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StudentTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timestamp = now();
        $filePath = public_path('data/student.csv');
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
    
            // Fetch all relevant IDs
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            $education_level = DB::table('education_levels')->pluck('id')->toArray();
            $specialty = DB::table('specialty')->pluck('id')->toArray();
            $studentbatch = DB::table('student_batch')->pluck('id')->toArray();
            $students = []; 
    
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                
                $schoolBranchId = Arr::random($school_branches);
                $guardians = DB::table('parents')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if (!$guardians) {
                    Log::warning('No guardians found for school_branch_id: ' . $schoolBranchId);
                    continue; 
                }
                $specialty = DB::table('specialty')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if(!$specialty){
                    Log::warning('No specialty found for school branch id' . $schoolBranchId);
                }
                $department = DB::table('department')->where('school_branch_id', $schoolBranchId)->pluck('id')->toArray();
                if(!$department){
                    Log::warning('No department found for school branch id' . $schoolBranchId);
                }
                $randomGuardainId = Arr::random($guardians);
                $randomGuardainIdTwo = Arr::random($guardians);
                $randomSpecialtyId = Arr::random($specialty);
                $specialtyDetails = DB::table('specialty')->where('id', $randomSpecialtyId)->first();
                $schoolFee = $specialtyDetails ? $specialtyDetails->school_fee : 0; 
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 25);
                
                if (count($data) >= 2) {
                    $students[] = [
                        'id' => $id, 
                        'school_branch_id' => $schoolBranchId, 
                        'name' => $data[1], 
                        'first_name' => $data[2], 
                        'last_name' => $data[3], 
                        'DOB' => $data[4], 
                        'gender' => $data[5], 
                        'total_fee_debt' => $schoolFee, // Use the school fee of the selected specialty
                        'phone_one' => $data[6], 
                        'phone_two' => $data[7], 
                        'religion' => $data[8], 
                        'email' => $data[9], 
                        'password' => Hash::make($data[10]), 
                        'last_login_at' => $data[11], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'specialty_id' => $randomSpecialtyId,
                        'department_id' => Arr::random($department),
                        'level_id' => Arr::random($education_level),
                        'guadian_one_id' => $randomGuardainId,
                        'guadian_two_id' => $randomGuardainIdTwo,
                        'student_batch_id' => Arr::random($studentbatch),
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('Student Array: ', $students);
        
            if (!empty($students)) {
                DB::table('student')->insert($students);
                Log::info('Inserted Students: ' . count($students) . ' entries.');
            } else {
                Log::warning('No Students to insert.');
            }
        }
    }
}
