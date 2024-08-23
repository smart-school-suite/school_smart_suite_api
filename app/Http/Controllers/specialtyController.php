<?php

namespace App\Http\Controllers;
use App\Models\Specialty;
use App\Models\Tenantspecialty;
use Illuminate\Http\Request;

class specialtyController extends Controller
{
    //

    public function create_school_speciality(Request $request){
        $request->validate([
            'department_id' => 'required|string',
            'specialty_name' => 'required|string',
            'registration_fee' => 'required|decimal:0,1000000',
            'school_fee' => 'required|decimal:0, 1000000',
            'level' => 'required|string'
        ]);

        $specialty = new Specialty();

        $specialty->department_id = $request->department_id;
        $specialty->specialty_name = $request->specialty_name;
        $specialty->registration_fee = $request->registration_fee;
        $specialty->school_fee = $request->school_fee;
        $specialty->level = $request->level;

        $specialty->save();

        return response()->json(['message' => 'specialty created sucessfully'], 200);

    }


    public function delete_school_specialty(Request $request, $specialty_id){
          $specialty = Specialty::find($specialty_id);
          if(!$specialty){
             return response()->json(['message' => 'specialty not found'], 404);
          }

          $specialty->delete();

          return response()->json(['message' => 'Specialty deleted successfully'], 200);
    }

    public function update_school_specialty(Request $request, $specialty_id){
          $specialty = Specialty::find($specialty_id);
          if(!$specialty){
             return response()->json(['message' => 'specialty not found'], 404);
          }

          $specialty_data = $request->all();
          $specialty_data = array_filter($specialty_data);
          $specialty->fill($specialty_data);
          
          $specialty->save();
          return response()->json(['message' => 'specialty updated sucessfully'], 200);
    }

    public function get_all_school_specailty_with_all_relations(Request $request){
        $specialty = Specialty::with('exams', 'courses', 'department'); 
        return response()->json(['specialty' => $specialty], 200);
    }

    public function get_all_school_specialty(Request $request){
         $specialty_data = Specialty::all();
         return response()->json(['specialty' => $specialty_data], 200);
    }

    public function create_tenant_school_specailty(Request $request){
        $request->validate([
            'school_branches_id' => 'required|string',
            'specialty_id' => 'required|string',
            'school_fee' => 'decimal:1,1000000',
            'department_id' => 'required|string',
            'registration_fee' => 'decimal:1,1000000'
         ]);

         $tenant_school_specailty_instance = new Tenantspecialty();
         $tenant_school_specailty_instance->school_branches_id = $request->school_branches_id;
         $tenant_school_specailty_instance->specialty_id = $request->specialty_id;
         $tenant_school_specailty_instance->school_fee = $request->school_fee;
         $tenant_school_specailty_instance->department_id = $request->department_id;
         $tenant_school_specailty_instance->registration_fee = $request->registration_fee;

         $tenant_school_specailty_instance->save();

         return response()->json(['message' => 'Specialty created sucessfully'], 200);

    }

    public function get_all_tenant_School_specailty_scoped(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $specialty_data = Tenantspecialty::Where('school_id', $currentSchool->id)->get();
        return response()->json(['specialties', $specialty_data], 200);
    }

    
}
