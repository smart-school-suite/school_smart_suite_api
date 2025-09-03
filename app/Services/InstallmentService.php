<?php

namespace App\Services;

use App\Models\Installment;

class InstallmentService
{
   public function createInstallment($installmentData){
       Installment::create($installmentData);
   }

   public function updateInstallment($installmentData, $installmentId){
      $installment = Installment::findOrFail($installmentId);
      $cleanedData = array_filter($installmentData);
      $installment->update($cleanedData);
      return $installment;
   }

   public function getInstallments(){
      return Installment::all();
   }

   public function deleteInstallment($installmentId){
     $installment = Installment::findOrFail($installmentId)->delete();
     return $installment;
   }

   public function deactivateInstallment($installmentId){
     $installment = Installment::findOrFail($installmentId);
     $installment->status = "inactive";
     $installment->save();
     return $installment;
   }

   public function activateInstallment($installmentId){
     $installment = Installment::findOrFail($installmentId);
     $installment->status = "active";
     $installment->save();
     return $installment;
   }

   public function getActiveFeeInstallment(){
      return  Installment::where("status", "active")->get();
   }

}
