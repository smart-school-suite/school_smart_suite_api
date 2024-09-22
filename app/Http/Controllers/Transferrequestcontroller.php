<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Reportcard;
use App\Models\Student;
use App\Models\Transferedstudents;
use App\Models\Transferrequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Transferrequestcontroller extends Controller
{
    //
    public function create_student_tranafer_request(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
        'target_school_id' => 'required|string',
        'target_school_name' => 'required|string',
        'specialty_name' => 'required|string',
        'specialty_id' => 'required|string',
        'level_id' => 'required|string',
        'level_name' => 'required|string',
        'department_id' => 'required|string',
        'department_name' => 'required|string',
        'student_id' => 'required|string',
        'parent_id' => 'required|string',
        'student_name' => 'required|string'
        ]);

        $new_student_transfer_request = new Transferrequest();

        $new_student_transfer_request->current_school_id = $currentSchool->id;
        $new_student_transfer_request->target_school_id = $request->target_school_id;
        $new_student_transfer_request->current_school_name = $currentSchool->branch_name;
        $new_student_transfer_request->target_school_name = $request->target_school_name;
        $new_student_transfer_request->specialty_name = $request->specialty_name;
        $new_student_transfer_request->specialty_id = $request->specialty_id;
        $new_student_transfer_request->level_id = $request->level_id;
        $new_student_transfer_request->level_name = $request->level_name;
        $new_student_transfer_request->department_id = $request->department_id;
        $new_student_transfer_request->department_name = $request->department_name;
        $new_student_transfer_request->student_id = $request->student_id;
        $new_student_transfer_request->student_name = $request->student_name;
        $new_student_transfer_request->parent_id = $request->parent_id;

        $new_student_transfer_request->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'student transfer request created sucessfully',
            'transfer_request' => $new_student_transfer_request
        ], 200);

    }

    public function request_student_records(Request $request, $current_school_id, $student_id){

        $current_school_id = $request->route('current_school_id');
        $find_transfer_request = Transferrequest::find($student_id);
        if(!$student_id){
            return response()->json([
                'status' => 'error',
                'message' => 'Transfer request for this student not found'
            ], 200);
        }
        
        $student_records_data = Reportcard::where('school_branch_id', $current_school_id)
                                ->where('student_id', $student_id)
                                ->get();
         if($student_records_data->isEmpty()){
            return response()->json([
                'status' => 'error',
                'message' => 'Student records not found'
            ], 409);
         }

         $pdf = PDF::loadView('student_transcript', compact($student_records_data));

         return $pdf->download('student_transcript.pdf');

    }

    public function delete_transfer_request(Request $request, $transfer_id){
         $currentSchool = $request->attributes->get('currentSchool');  
        $find_transfer_request = Transferrequest::where('current_school_id', $currentSchool->id)
                                    ->find($transfer_id);
        
        if(!$find_transfer_request){
            return response()->json([
                'status' => 'error',
                'message' => 'transfer request not found'
            ], 404);
        }

        $find_transfer_request->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Transfer request created succefully',
            'student_transfer' => $find_transfer_request
        ], 200);
    }

    public function get_transfer_request(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $transfer_request = Transferrequest::where('current_school_id',  $currentSchool->id)
                            ->get();

        if($transfer_request->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'No transfer request found'
            ], 409);
        }

        return response()->json([
            'status' => 'ok',
            'message' => 'student records fetched succefully',
            'transfer_request' => $transfer_request
        ], 200);
    }

    public function respond_to_transfer_request(Request $request, $transfer_id, $status){
        $target_school = $request->attributes->get('currentSchool');
        $status = $request->route('status');
        $find_transfer_request = Transferrequest::where('target_school_id', $target_school->id)
                                   ->find($transfer_id);
        if(!$find_transfer_request){
            return response()->json([
                'status' => 'error',
                'message' => 'Request not found it must have been deleted'
            ], 404);
        }
             
        if($status == 'Accept'){
            $this->handle_transfer_data(
                $find_transfer_request->current_school_id,
                 $find_transfer_request->target_school_id, 
                 $find_transfer_request->student_id
                );
            $this->transfer_parent_data(
                $find_transfer_request->current_school_id, 
                $find_transfer_request->target_school_id,
                 $find_transfer_request->parent_id
                );
            $this->register_student(
             $find_transfer_request->current_school_id,
             $find_transfer_request->target_school_id, 
             $find_transfer_request->specialty_id, 
             $find_transfer_request->department_id, 
             $find_transfer_request->level_id,
             $find_transfer_request->student_id
            );
            
            Transferedstudents::create([
                'school_branch_id' => $find_transfer_request->current_school_id,
                'student_name' => $find_transfer_request->student_name,
                'from' => $find_transfer_request->current_school_name,
                'to' => $find_transfer_request->target_school_name,
                'status' => $status,
                'level' => $find_transfer_request->level_name,
                'specialty' => $find_transfer_request->specialty_name,
                'department' => $find_transfer_request->department_name,
            ]);
            
            return response()->json([
                'status' => 'ok',
                'message' => 'Student tranferred succesfully'
            ], 200);
        }
        else{
            $find_transfer_request->status = 'Rejected';

            return response()->json([
                'status' => 'ok',
                'message' => 'Student transfer rejected'
            ], 200);
        }
    }

    private function handle_transfer_data($current_school_id, $target_school_id, $student_id, ){
        $student_records = Reportcard::where('school_branch_id', $current_school_id)
                           ->where('student_id', $student_id)
                            ->get();
          foreach ($student_records as $records){ 
              $records->school_branch_id = $target_school_id;
              $student_records->save();
          }            
    }

    private function transfer_parent_data($current_school_id, $target_school_id, $parent_id){
        $find_parents = Parents::where('school_branch_id', $current_school_id)
                        ->find($parent_id);

        $find_parents->school_branch_id = $target_school_id;

        $find_parents->save();
    }

    private function register_student($current_school_id, $target_school_id, 
    $specialty_id, $department_id, $level_id, $student_id){
          $find_student = Student::where('school_branch_id', $current_school_id)
                          ->find($student_id);
           $find_student->specialty_id = $specialty_id;
           $find_student->department_id = $department_id;
           $find_student->level_id = $level_id;
           $find_student->school_branch_id = $target_school_id;

           $find_student->save();
                                 
    }
}
