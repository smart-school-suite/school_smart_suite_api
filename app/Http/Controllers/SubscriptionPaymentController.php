<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubscriptionPayment;

class SubscriptionPaymentController extends Controller
{
    //
    public function delete_payment(Request $request, $transaction_id)
    {
        $find_transaction = SubscriptionPayment::find($transaction_id);
        if (!$find_transaction) {
            return response()->json([
                'status' => 'error',
                "message" => "No records found"
            ]);
        }

        $find_transaction->delete();
        return response()->json([
            "status" => "success",
            "message" => "Transaction deleted succefully"
        ]);
    }


    public function my_transactions(Request $request, $school_id)
    {
        $my_transactions = SubscriptionPayment::where("school_id", $school_id)->with(['schoolSubscription'])->get();
        if ($my_transactions->isEmpty()) {
            return response()->json([
                'status' => "ok",
                "message" => "transactions is empty"
            ], 404);
        }

        return response()->json([
            'status' => "ok",
            "message" => "transactions fetched sucessfully",
            "transactions" => $my_transactions
        ], 200);
    }


    public function get_all_transactions(Request $request)
    {
        $transactions = SubscriptionPayment::with('schoolSubscription');
        if ($transactions->isEmpty()) {
            return response()->json([
                'status' => "error",
                "message" => "Transactions is empty"
            ], 404);
        }

        return response()->json([
            'status' => "ok",
            "message" => "transactions fetched succefuly"
        ], 200);
    }
}
