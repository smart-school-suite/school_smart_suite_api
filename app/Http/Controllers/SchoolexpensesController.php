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
            'date' => 'required|date',
            'amount' => 'required',
            'description' => 'sometimes|string'
        ]);

        $new_expenses_instance = new SchoolExpenses();

        $new_expenses_instance->expenses_category_id = $request->expenses_category_id;
        $new_expenses_instance->date = $request->date;
        $new_expenses_instance->amount = $request->amount;
        $new_expenses_instance->description = $request->description;
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
         $expenses_data = SchoolExpenses::where('school_branch_id', $currentSchool->id)->with(['schoolexpensescategory'])
                                          ->get();

         return response()->json([
            'status' => 'ok',
            'expenses_data' => $expenses_data
         ], 200);
    }

    public function expenses_details(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $expense_id = $request->route("expense_id");
         $find_expenses = SchoolExpenses::find($expense_id);
         if(!$find_expenses){
             return response()->json([
                 "status" => "error",
                 "message" => "expenses not found",
                 "id" => $expense_id
             ], 400);
         }

         $expenses_details = SchoolExpenses::where("school_branch_id", $currentSchool->id)
                                             ->where("id", $expense_id)
                                             ->with(['schoolexpensescategory'])
                                              ->get();
        return response()->json([
             "status" => "ok",
             "message" => "Expenses details fetched sucessfully",
             "expenses_details" => $expenses_details
        ], 201);
    }
}
