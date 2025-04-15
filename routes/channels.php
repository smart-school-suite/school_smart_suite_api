<?php

//use Illuminate\Support\Facades\Broadcast;

//Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
   // return (int) $user->id === (int) $id;
//});

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('election.results.{schoolBranchId}.{electionId}', function ($user, $schoolBranchId, $electionId) {
    return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student', 'teacher', 'schoolAdmin']]);

