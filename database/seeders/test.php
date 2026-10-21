<?php

namespace Database\Seeders;

use App\Models\Promotion\StudentPromotionRecord;
use Illuminate\Database\Seeder;


class test extends Seeder
{
    public function run(): void
    {
      $promotionRecords = StudentPromotionRecord::with(['student'])->get();
      foreach($promotionRecords as $r){
         $r->update([
            'school_branch_id' => $r->student->school_branch_id
         ]);
      }
    }
}
