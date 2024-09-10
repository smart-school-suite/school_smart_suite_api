<?php

namespace App\Http\Controllers;

use App\Models\Schoolexpensescategory;
use Illuminate\Http\Request;

class Schoolexpensescategorycontroller extends Controller
{
    //
    public function create_category_expenses(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
          'name'
        ]);

        $new_category_instance = new Schoolexpensescategory();
        $new_category_instance->name = $request->name;
        $new_category_instance->school_branch_id = $currentSchool->id;

        $new_category_instance->save();

        return response()->json(['message' => 'Category created succefully'], 200);
    }

    public function update_category_expenses(Request $request, $category_expense_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $find_category_expense = Schoolexpensescategory::where('school_branch_id', $currentSchool->id)
                                  ->find($category_expense_id);
             
        if(!$find_category_expense){
            return response()->json(['message' => 'Category not found'], 404);
        }

        $fillable_data = $request->all();
        $filtered_data = array_filter($fillable_data);
        $find_category_expense->fill($filtered_data);
        $find_category_expense->save();

        return response()->json(['message' => 'Category updated succefully'], 200);
    }


    public function delete_category_expense(Request $request, $category_expense_id){
        $currentSchool = $request->attributes->get('currentSchool');
        $find_category_expense = Schoolexpensescategory::where('school_branch_id', $currentSchool->id)
                                  ->find($category_expense_id);
             
        if(!$find_category_expense){
            return response()->json(['message' => 'Category not found'], 404);
        }

        $find_category_expense->delete();

        return response()->json(['message' => 'Category expenses deleted succefully'], 200);
    }


    public function get_all_category_expenses(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $category_expense_data = Schoolexpensescategory::where('school_branch_id', $currentSchool->id)
                                                        ->with(['schoolexpensescategory'])
                                                       ->get();
        return response()->json(['category_expense_data' => $category_expense_data], 200);
    }
}
