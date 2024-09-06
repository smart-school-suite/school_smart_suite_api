<?php

namespace App\Http\Controllers;

use App\Models\Parents;
use App\Models\Student;
use App\Models\StudentRecords;
use App\Models\Transferedstudents;
use App\Models\Transferrequest;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class Transferrequestcontroller extends Controller
{
    //
    public function create_student_tranafer_request(Request $request){
        $request->validate([
        'current_school_id' => 'required|string',
        'target_school_id' => 'required|string',
        'current_school_name' => 'required|string',
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

        $new_student_transfer_request->current_school_id = $request->current_school_id;
        $new_student_transfer_request->target_school_id = $request->target_school_id;
        $new_student_transfer_request->current_school_name = $request->current_school_name;
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

    }

    public function request_student_records(Request $request, $current_school_id, $student_id){

        $current_school_id = $request->route('current_school_id');
        $find_transfer_request = Transferrequest::find($student_id);
        if(!$student_id){
            return response()->json(['message' => 'Transfer request for this student not found'], 200);
        }
        
        $student_records_data = StudentRecords::where('school_branch_id', $current_school_id)
                                ->where('student_id', $student_id)
                                ->get();

         $pdf = PDF::loadView('student_transcript', compact($student_records_data));

         return $pdf->download('student_transcript.pdf');

    }

    public function delete_transfer_request(Request $request, $transfer_id){
         $currentSchool = $request->attributes->get('currentSchool');  
        $find_transfer_request = Transferrequest::where('current_school_id', $currentSchool->id)
                                    ->find($transfer_id);
        
        if(!$find_transfer_request){
            return response()->json(['message' => 'transfer request not found'], 404);
        }

        $find_transfer_request->delete();

        return response()->json(['message' => 'Transfer request created succefully'], 200);
    }

    public function get_transfer_request(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $transfer_request = Transferrequest::where('current_school_id',  $currentSchool->id)
                            ->get();

        return response()->json(['transfer_request' => $transfer_request], 200);
    }

    public function respond_to_transfer_request(Request $request, $transfer_id, $status){
        $target_school = $request->attributes->get('currentSchool');
        $status = $request->route('status');
        $find_transfer_request = Transferrequest::where('target_school_id', $target_school->id)
                                   ->find($transfer_id);
        if(!$find_transfer_request){
            return response()->json(['message' => 'Request not found it must have been deleted'], 404);
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
            
            return response()->json(['message' => 'Student tranferred succesfully'], 200);
        }
        else{
            $find_transfer_request->status = 'Rejected';

            return response()->json(['message' => 'Student transfer rejected'], 200);
        }
    }

    private function handle_transfer_data($current_school_id, $target_school_id, $student_id, ){
        $student_records = StudentRecords::where('school_branch_id', $current_school_id)
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

    private function register_student($current_school_id, $target_school_id, $specialty_id, $department_id, $level_id, $student_id){
          $find_student = Student::where('school_branch_id', $current_school_id)
                          ->find($student_id);
           $find_student->specialty_id = $specialty_id;
           $find_student->department_id = $department_id;
           $find_student->level_id = $level_id;
           $find_student->school_branch_id = $target_school_id;

           $find_student->save();
                                 
    }
}
