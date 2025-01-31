<?php

namespace App\Http\Controllers;

use App\Models\Feepayment;
use App\Models\Student;
use Illuminate\Http\Request;

class feepaymentController extends Controller
{
    //
    public function pay_school_fees(Request $request) {
        $currentSchool = $request->attributes->get('currentSchool');

        // Validate the incoming request data
        $request->validate([
            'student_id' => 'required|string',
            'fee_name' => 'required|string',
            'amount' => 'required|numeric', // Ensure that amount is numeric
        ]);

        // Find the student in the specified school branch
        $student = Student::where('school_branch_id', $currentSchool->id)->find($request->student_id);

        // Check if the student exists
        if (!$student) {
            return response()->json([
                'status' => 'error',
                'message' => 'Student not found',
            ], 404);
        }

        // Validate if total fee debt is zero
        if ($student->total_fee_debt == 0) {
            return response()->json([
                'status' => 'ok',
                'message' => "Student fees completed",
            ], 200);
        }

        // Check if the amount being paid exceeds the debt
        if ($student->total_fee_debt < $request->amount) {
            return response()->json([
                'status' => 'error',
                'message' => 'The amount being paid is greater than the student fee debt',
                'student_fee_debt' => $student->total_fee_debt,
                'amount_paid' => $request->amount,
            ], 400); // Change to 400 Bad Request
        }

        // Create a new fee payment record
        $new_fee_payment_instance = new Feepayment();
        $new_fee_payment_instance->fee_name = $request->fee_name;
        $new_fee_payment_instance->amount = $request->amount;
        $new_fee_payment_instance->student_id = $request->student_id;
        $new_fee_payment_instance->school_branch_id = $currentSchool->id;
        $new_fee_payment_instance->save();

        // Update the student's fee debt
        $student->total_fee_debt -= $request->amount;

        // Check if the student's fee debt has become zero after the payment
        if ($student->total_fee_debt == 0) {
            $student->fee_status = 'completed'; // Update fee status to completed
        }

        // Save the updated student record
        $student->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Payment successful',
            'remaining_fee_debt' => $student->total_fee_debt,
            'fee_status' => $student->fee_status,
        ], 200);
    }

    public function get_all_fees_paid(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $fee_payment_data = Feepayment::where('school_branch_id', $currentSchool->id)
                                        ->with(['student.level'])
                                        ->with(['student.specialty'])
                                        ->get();
        if($fee_payment_data->isEmpty()){
              return response()->json([
                'status' => 'ok',
                'message' => 'There are no records found'
              ], 200);
        }
        $result = [];
        foreach($fee_payment_data as $fee_payment){
            $result[] = [
                "id" => $fee_payment->id,
                "fee_name" => $fee_payment->fee_name,
                "amount" => $fee_payment->amount,
                "student_name" => $fee_payment->student->name,
                "specailty_name" => $fee_payment->student->specialty->specialty_name,
                "level" => $fee_payment->student->level->name,
            ];
        }
        return response()->json([
            'status' => 'ok',
            'message' => 'fee payment records fetched successfully',
            'fee_payment_records' => $result
        ], 200);
    }

    public function update_student_fee_payment(Request $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $find_fee_payment = Feepayment::where('school_branch_id', $currentSchool->id)
                                        ->find($fee_id);
        if(!$find_fee_payment){
            return response()->json([
                'status' => 'ok',
                'message' => 'This record was not found'
            ], 400);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_fee_payment->fill($filtered_data);
        $find_fee_payment->save();

        return response()->json([
           'status' => 'ok',
           'message' => 'record updated succefully'
        ], 200);
    }

    public function delete_fee_payment_record(Request $request, $fee_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $find_fee_payment = Feepayment::where('school_branch_id', $currentSchool->id)
                                        ->find($fee_id);
        if(!$find_fee_payment){
            return response()->json([
                'status' => 'ok',
                'message' => 'This record was not found'
            ], 400);
        }

        $find_fee_payment->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'record deleted successfully'
         ], 200);
    }

    public function get_all_student_deptors(Request $requst){
        $currentSchool = $requst->attributes->get('currentSchool');

        $fee_debtors = Student::where('school_branch_id', $currentSchool->id)
                                 ->where('total_fee_debt', '>', 0)
                                 ->with(['specialty', 'level'])
                                 ->get();
        $result = [];
       foreach($fee_debtors as $fee_debtor){
          $result [] = [
              'id' => $fee_debtor->id,
              'specialty_name' => $fee_debtor->specialty->specialty_name,
              'level' => $fee_debtor->level->name,
              'student_name' => $fee_debtor->name,
              'total_fee_debt' => $fee_debtor->total_fee_debt,
              'fee_status' => $fee_debtor->fee_status,
          ];
       }

       return response()->json([
          'status' => 'ok',
          'message' => 'student debtors fetched succefully',
          'fee_debtors' => $result
       ], 200);
    }
}
