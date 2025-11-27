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

BroadCast::channel('schoolBranch.{schoolBranchId}.specialty', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.department', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.course', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.student', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.studentDropout', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.studentBatch', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.tuitionFeeShedule', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.tuitionFee', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.tuitionFeeTransaction', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.teacher', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.teacherSpecialtyPreference', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.teacherPreferedTeachingTime', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);

BroadCast::channel('schoolBranch.{schoolBranchId}.schoolSemester', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['schooladmin']]);
//


//student Channels
BroadCast::channel('student.{studentId}.studentAccount', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student']]);

BroadCast::channel('student.{studentId}.tuitionFee', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student']]);

BroadCast::channel('student.{studentId}.tuitionFeeSchedule', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student']]);

BroadCast::channel('student.{studentId}.tuitionFeeTransaction', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student']]);

BroadCast::channel('student.{studentId}.schoolSemester', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['student']]);

//teacher Channels
BroadCast::channel('teacher.{teacherId}.teacherAccount', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['teacher']]);

BroadCast::channel('teacher.{teacherId}.schoolSemester', function ($user, $schoolBranchId) {
      return (string) $user->school_branch_id === (string) $schoolBranchId;
}, ['guards' => ['teacher']]);



