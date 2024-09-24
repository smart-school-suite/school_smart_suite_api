<?php

namespace App\Http\Controllers;

use App\Models\SchoolExpenses;
use Illuminate\Http\Request;

class SchoolexpensesController extends Controller
{
    //
    public function add_new_expense(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
        $request->validate([
            'expenses_category_id' => 'required|string',
            'date' => 'required',
            'amount' => 'required'
        ]);

        $new_expenses_instance = new SchoolExpenses();
        
        $new_expenses_instance->expenses_category_id = $request->expenses_category_id;
        $new_expenses_instance->date = $request->date;
        $new_expenses_instance->amount = $request->amount;
        $new_expenses_instance->school_branch_id = $currentSchool->id;

        $new_expenses_instance->save();

        return response()->json([
            'status' => 'ok',
            'message' => 'Expense created sucessfully',
            'new_expense_instance' => $new_expenses_instance
        ], 200);
    }

    public function delete_expense(Request $request, $expense_id){
        $currentSchool = $request->attributes->get('currentSchool');
         $find_expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expense_id);
         if(!$find_expenses){
            return response()->json([
                'status' => 'ok',
                'message' => 'expenses not found'
            ], 409);
         }
        
        $find_expenses->delete();

        return response()->json([
            'status' => 'ok',
            'message' => 'Expenses deleted succesfully',
            'deleted_expenses' => $find_expenses
        ], 200);
    }

    public function update_expense(Request $request, $expense_id){
        $currentSchool = $request->attributes->get('currentSchool');
         $find_expenses = SchoolExpenses::where('school_branch_id', $currentSchool->id)->find($expense_id);
         if(!$find_expenses){
            return response()->json([
                'status' => 'ok',
                'message' => 'expenses not found'
            ], 409);
         }

         $fillable_data = $request->all();
         $filtered_data = array_filter($fillable_data);
         $find_expenses->fill($filtered_data);
         $find_expenses->save();

         return response()->json([
            'status' => 'ok',
            'message' => 'expense updated succefully',
            'updated_expenses' => $find_expenses
         ], 200);
    }

    public function get_all_expenses(Request $request){
        $currentSchool = $request->attributes->get('currentSchool');
         $expenses_data = SchoolExpenses::where('school_branch_id', $currentSchool->id)
                                          ->get();
         if($expenses_data->isEmpty()){
            return response()->json([
                'status' => 'ok',
                'message' => 'Expenses records is empty'
            ], 409);
         }
         return response()->json([
            'status' => 'ok',
            'expenses_data' => $expenses_data
         ], 200);
    }
}
