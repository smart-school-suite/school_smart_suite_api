<?php

namespace App\Http\Controllers;

use App\Models\Feepayment;
use App\Models\Student;
use Illuminate\Http\Request;

class feepaymentController extends Controller
{
    //
    public function pay_school_fees(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'student_id' => 'required|string',
            'fee_name' => 'required|string',
            'amount' => 'required'
        ]);
         
        $student = Student::where('school_branch_id', $currentSchool->id)->with(['specialty'])->get();

        $student_debt = $student->unpaid_fees;
        if($student_debt == 0){
            return response()->json(['message' => "Student fees completed"], 200);
        }

        

        $new_fee_payment_instance = new Feepayment();

        $new_fee_payment_instance->fee_name = $request->fee_name;
        $new_fee_payment_instance->amount = $request->amount;
        $new_fee_payment_instance->student_id = $request->student_id;
        $new_fee_payment_instance->school_branch_id = $request->school_branch_id;

        $new_fee_payment_instance->save();
    }
}
