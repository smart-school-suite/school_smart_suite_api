<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Database\Seeder;

class FeeTransactionsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('Fee Payment Transactions seeder has started.');
        $timestamp = now();
        $filePath = public_path("data/fee_payment_transactions.csv");
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("CSV file not found or not readable!");
        }
    
        if (($handle = fopen($filePath, 'r')) !== false) {
            $header = fgetcsv($handle);
            Log::info('CSV Header: ', $header);
            $school_branches = DB::table('school_branches')->pluck('id')->toArray();
            
            $fee_payment_transactions = []; 
            
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                Log::info('Current Row Data: ', $data);
                $uuid = Str::uuid()->toString();
                $id = substr(md5($uuid), 0, 15); 
                $randomSchoolBranchesId = Arr::random($school_branches);
                $student = DB::table('student')->where('school_branch_id', $randomSchoolBranchesId)->pluck('id')->toArray();
                if(!$student){
                    Log::warning('No student found for school branch id' . $randomSchoolBranchesId);
                }
                $randomStudent = Arr::random($student);
                if (count($data) >= 2) {
                    $fee_payment_transactions[] = [
                        'id' => $id,
                        'school_branch_id' => $randomSchoolBranchesId, 
                        'fee_name' => $data[1], 
                        'amount' => $data[2], 
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                        'student_id' => $randomStudent
                    ];
                }
            }
    
            fclose($handle);
            
            Log::info('School Fee Payment Transactions: ', $fee_payment_transactions);
    
            if (!empty($fee_payment_transactions)) {
                DB::table('fee_payment_transactions')->insert($fee_payment_transactions);
                Log::info('Inserted fee payment transactions: ' . count($fee_payment_transactions) . ' entries.');
            } else {
                Log::warning('No fee payment transactions to insert.');
            }
        }
    }
}
