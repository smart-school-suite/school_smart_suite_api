<?php

//use Illuminate\Support\Facades\Broadcast;

//Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
   // return (int) $user->id === (int) $id;
//});

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('election.results.{schoolBranchId}.{electionId}', function ($user, $schoolBranchId, $electionId) {
    return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student', 'teacher', 'schooladmin']]);

Broadcast::channel('App.Models.Teacher.{id}', function ($user, $id) {
    return $user->id == $id;
}, ['guards' => ['teacher']]);

Broadcast::channel('App.Models.Student.{id}', function ($user, $id) {
    return $user->id == $id;
}, ['guards' => ['student']]);

Broadcast::channel('App.Models.Schooladmin.{id}', function ($user, $id) {
    return $user->id == $id;
}, ['guards' => ['schooladmin']]);

//school admin channels
BroadCast::channel('schoolBranch.{schoolBranchId}.schoolAdmin.{schoolAdminId}.actions', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);




