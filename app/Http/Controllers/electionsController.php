<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class electionsController extends Controller
{
    //
    public function createElection(Request $request){
         $currentSchool = $request->attributes->get("currentSchool");
         $request->validate([
             'title' => 'required|string',
             'election_start_date' => 'required|date',
             'election_end_date' => 'required|date',
             'starting_time' => 'required|time',
             'endting_time' => 'required|time',
         ]);
    }
}
