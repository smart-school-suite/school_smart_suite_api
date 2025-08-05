<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
        Schema::table('schools', function (Blueprint $table) {
            $table->string('country_id')->after('id');
            $table->foreign('country_id')->references('id')->on('country');
        });

        Schema::table('school_admin', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('teacher', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('department', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('specialty', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });


        Schema::table('marks', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('exams', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_type_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_type');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
            $table->string('grades_category_id')->nullable();
            $table->foreign('grades_category_id')->references('id')->on('grades_category');
            $table->boolean("grading_added")->default(false);
        });

        Schema::table('fee_payment_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student')->onDelete('cascade');
        });

        Schema::table('parents', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('school_branches', function (Blueprint $table) {
            $table->string('school_id');
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
        });

        Schema::table('courses', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });

        Schema::table('student', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department')->onDelete('cascade');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('guardian_id');
            $table->foreign('guardian_id')->references('id')->on('parents');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('timetables', function (Blueprint $table) {
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty')->onDelete('cascade');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses')->onDelete('cascade');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('school_semesters');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels')->onDelete('cascade');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('teacher_availability_slots', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('teacher_availability_id');
            $table->foreign('teacher_availability_id')->references('id')->on('teacher_availabilities');
        });

        Schema::table('teacher_availabilities', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels')->onDelete('cascade');
            $table->string('school_semester_id');
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('teacher_id');
            $table->foreign('teacher_id')->references('id')->on('teacher')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
        });

        Schema::table('grades', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('letter_grade_id');
            $table->foreign('letter_grade_id')->references('id')->on('letter_grade');
            $table->string("grades_category_id");
            $table->foreign('grades_category_id')->references('id')->on('grades_category');
        });

        Schema::table('examtimetable', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('exam_type', function (Blueprint $table) {
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
        });


        Schema::table('school_expenses', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('expenses_category_id');
            $table->foreign('expenses_category_id')->references('id')->on('school_expenses_category');
        });

        Schema::table('school_expenses_category', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_batch', function (Blueprint $table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_resit', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });


        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('rate_card_id');
            $table->foreign('rate_card_id')->references('id')->on('rate_cards');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('school_subscription_id');
            $table->foreign('school_subscription_id')->references('id')->on('school_subscriptions');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });


        Schema::table('election_application', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
        });

        Schema::table('elections', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_type');
        });

        Schema::table('election_roles', function (Blueprint $table) {
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_votes', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
        });

        Schema::table('past_election_winners', function (Blueprint $table) {
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('current_election_winners', function (Blueprint $table) {
            $table->string('election_type_id');
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_participants', function (Blueprint $table) {
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_type', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('elections_results', function (Blueprint $table) {
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('position_id');
            $table->foreign('position_id')->references('id')->on('election_roles');
            $table->string('candidate_id');
            $table->foreign('candidate_id')->references('id')->on('election_candidates');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('election_candidates', function (Blueprint $table) {
            $table->string('application_id');
            $table->foreign('application_id')->references('id')->on('election_application');
            $table->string('election_id');
            $table->foreign('election_id')->references('id')->on('elections');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('election_role_id');
            $table->foreign('election_role_id')->references('id')->on('election_roles');
        });

        Schema::table('teacher_specailty_preference', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string("teacher_id");
            $table->foreign('teacher_id')->references('id')->on('teacher');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
        });

        Schema::table('school_semesters', function (Blueprint $table) {
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('hod', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('department_id');
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('hos', function (Blueprint $table) {
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });

        Schema::table('fee_waiver', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
        });

        Schema::table("registration_fees", function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });


        Schema::table("additional_fees", function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('additionalfee_category_id');
            $table->foreign('additionalfee_category_id')->references('id')->on('additional_fee_category');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
        });

        Schema::table("additional_fee_category", function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table("tuition_fee_transactions", function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('tuition_id');
            $table->foreign('tuition_id')->references('id')->on('tuition_fees');
        });

        Schema::table("additional_fee_transactions", function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('fee_id');
            $table->foreign('fee_id')->references('id')->on('additional_fees');
        });

        Schema::table('registration_fee_transactions', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('registrationfee_id');
            $table->foreign('registrationfee_id')->references('id')->on('registration_fees');
        });

        Schema::table("resit_fee_transactions", function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('resitfee_id');
            $table->foreign('resitfee_id')->references('id')->on('student_resit');
        });

        Schema::table('schoolbranch_apikey', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('school_grades_config', function (Blueprint $table) {
            $table->string("grades_category_id");
            $table->foreign('grades_category_id')->references('id')->on('grades_category');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_results', function (Blueprint $table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('exam_id');
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });
        Schema::table('studentbatch_grad_dates', function ($table) {
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });


        Schema::table('resit_examtimetable', function ($table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('course_id');
            $table->foreign('course_id')->references('id')->on('courses');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
        });


        Schema::table('resit_marks', function ($table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('courses_id');
            $table->foreign('courses_id')->references('id')->on('courses');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
        });

        Schema::table('resit_results', function ($table) {
            $table->string('student_id');
            $table->foreign('student_id')->references('id')->on('student')->onDelete('cascade');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_batch_id');
            $table->foreign('student_batch_id')->references('id')->on('student_batch');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('resit_exam_id');
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('failed_exam_id');
            $table->foreign('failed_exam_id')->references('id')->on('exams');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('resit_exams', function ($table) {
            $table->string('school_branch_id')->after('id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_type_id');
            $table->foreign('exam_type_id')->references('id')->on('exam_type');
            $table->string('level_id');
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('reference_exam_id');
            $table->foreign('reference_exam_id')->references('id')->on('exams');
            $table->string('semester_id');
            $table->foreign('semester_id')->references('id')->on('semesters');
            $table->string('specialty_id');
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('grades_category_id')->nullable();
            $table->foreign('grades_category_id')->references('id')->on('grades_category');
        });

        Schema::table('school_exam_stats', function (Blueprint $table) {
            $table->string('school_branch_id')->nullable()->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('stat_type_id')->nullable()->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
        });

        Schema::table('school_ca_exam_stats', function (Blueprint $table) {
            $table->string('school_branch_id')->nullable()->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('stat_type_id')->nullable()->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
        });

        Schema::table('student_exam_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_id')->nullable()->index();
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
        });

        Schema::table('student_ca_exam_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('student_id')->nullable()->index();
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
        });

        Schema::table('additional_fee_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('additional_fee_category');
        });

        Schema::table('resit_fee_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('school_expenses_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('school_expenses_category');
        });

        Schema::table('tuition_fee_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('announcement_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('label_id')->nullable();
            $table->foreign('label_id')->references('id')->on('labels');
        });

        Schema::table('election_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('election_type_id')->nullable();
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('election_winner_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('election_type_id')->nullable();
            $table->foreign('election_type_id')->references('id')->on('election_type');
            $table->string('election_role_id')->nullable();
            $table->foreign('election_role_id')->references('id')->on('election_roles');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('student_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('progressive_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('teacher_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('class_timetable_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('exam_timetable_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('exam_candidates', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('exam_id')->nullable();
            $table->foreign('exam_id')->references('id')->on('exams');
            $table->string('student_id')->nullable()->index();
            $table->foreign('student_id')->references('id')->on('student');
        });

        Schema::table('department_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('specialty_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('course_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('registration_fee_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('additional_fee_trans_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('category_id')->nullable();
            $table->foreign('category_id')->references('id')->on('additional_fee_category');
        });

        Schema::table('election_vote_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
            $table->string('election_id')->nullable();
            $table->foreign('election_id')->references('id')->on('elections');
        });

        Schema::table("resit_fee_trans_stats", function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table("tuition_fee_trans_stats", function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('election_application_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
            $table->string('specialty_id')->nullable();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('department_id')->nullable();
            $table->foreign('department_id')->references('id')->on('department');
        });

        Schema::table('school_admin_stats', function (Blueprint $table) {
            $table->string('stat_type_id')->index();
            $table->foreign('stat_type_id')->references('id')->on('stat_types');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('resit_candidates', function (Blueprint $table) {
            $table->string('resit_exam_id')->index();
            $table->foreign('resit_exam_id')->references('id')->on('resit_exams');
            $table->string('student_id')->index();
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->string('permission_category_id')->index();
            $table->foreign('permission_category_id')->references('id')->on('permission_category');
        });

        Schema::table('audiences', function (Blueprint $table) {
            $table->string('school_set_audience_group_id')->index();
            $table->foreign('school_set_audience_group_id')->references('id')->on('school_set_audience_groups');
        });

        Schema::table('school_set_audience_groups', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('announcement_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->string('category_id')->nullable()->index();
            $table->foreign('category_id')->references('id')->on('announcement_categories')->onDelete('set null');
            $table->string('label_id')->index();
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');
            $table->string('tag_id')->index();
            $table->foreign('tag_id')->references('id')->on('tags')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('annoucement_author', function (Blueprint $table) {
            $table->string('announcement_id')->index();
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
        });

        Schema::table('school_announcement_settings', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('announcement_setting_id');
            $table->foreign('announcement_setting_id')->references('id')->on('announcement_settings')->onDelete('cascade');
        });

        Schema::table('target_groups', function (Blueprint $table) {
            $table->string('school_set_audience_group_id');
            $table->foreign('school_set_audience_group_id')->references('id')->on('school_set_audience_groups')->onDelete('cascade');
            $table->string('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
        Schema::table('target_preset_groups', function (Blueprint $table) {
            $table->string('preset_group_id');
            $table->foreign('preset_group_id')->references('id')->on('preset_audiences')->onDelete('cascade');
            $table->string('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });
        Schema::table('target_users', function (Blueprint $table) {
            $table->string('actorable_type')->index();
            $table->string('actorable_id')->index();
            $table->string('announcement_id');
            $table->foreign('announcement_id')->references('id')->on('announcements')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('event_tags', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_events', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('event_category_id')->index();
            $table->foreign("event_category_id")->references('id')->on("event_categories");
            $table->string('tag_id')->nullable();
            $table->foreign("tag_id")->references("id")->on("event_tags");
        });

        Schema::table('event_author', function (Blueprint $table) {
            $table->string('event_id')->index();
            $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_event_settings', function (Blueprint $table) {
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->string('event_setting_id')->index();
            $table->foreign('event_setting_id')->references('id')->on('event_settings');
        });

        Schema::table('ev_inv_custom_groups', function (Blueprint $table) {
            $table->string('school_set_audience_group_id')->index();
            $table->foreign('school_set_audience_group_id')->references('id')->on('school_set_audience_groups')->onDelete('cascade');
            $table->string('event_id')->index();
            $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('ev_inv_preset_groups', function (Blueprint $table) {
            $table->string('preset_group_id')->index();
            $table->foreign('preset_group_id')->references('id')->on('preset_audiences')->onDelete('cascade');
            $table->string('event_id')->index();
            $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('ev_inv_members', function (Blueprint $table) {
            $table->string('event_id')->index();
            $table->foreign('event_id')->references('id')->on('school_events')->onDelete('cascade');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('roles', function (Blueprint $table) {
            $table->string('school_branch_id')->nullable()->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('fee_schedules', function (Blueprint $table) {
            $table->string('specialty_id')->index();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('level_id')->index();
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('school_semester_id')->index();
            $table->foreign('school_semester_id')->references('id')->on('school_semesters');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('fee_schedule_slots', function (Blueprint $table) {
            $table->string('installment_id')->index();
            $table->foreign('installment_id')->references('id')->on('installments');
            $table->string('fee_schedule_id')->index();
            $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('student_fee_schedule', function (Blueprint $table) {
            $table->string('student_id')->index();
            $table->foreign('student_id')->references('id')->on('student');
            $table->string('level_id')->index();
            $table->foreign('level_id')->references('id')->on('education_levels');
            $table->string('specialty_id')->index();
            $table->foreign('specialty_id')->references('id')->on('specialty');
            $table->string('fee_schedule_slot_id')->index();
            $table->foreign('fee_schedule_slot_id')->references('id')->on('fee_schedule_slots')->onDelete('cascade');
            $table->string('fee_schedule_id')->index();
            $table->foreign('fee_schedule_id')->references('id')->on('fee_schedules');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
        });

        Schema::table('school_branch_app_settings', function (Blueprint $table) {
            $table->string('app_settings_id')->index();
            $table->foreign('app_settings_id')->references('id')->on('app_settings');
            $table->string('school_branch_id')->index();
            $table->foreign('school_branch_id')->references('id')->on('school_branches');
        });

        Schema::table('app_settings', function (Blueprint $table) {
            $table->string('setting_category_id');
            $table->foreign('setting_category_id')->references('id')->on('setting_categories');
        });

        Schema::table('user_batches', function (Blueprint $table) {
            $table->string('batch_id');
            $table->foreign('batch_id')->references('id')->on('batches')->onDelete('cascade');
        });
    }
    public function down(): void
    {
        Schema::table('user_batches', function (Blueprint $table) {
         $table->dropForeign(['batch_id']);
         $table->dropColumn('batch_id');
        });

        Schema::table('student_ca_exam_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['exam_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('student_id');
            $table->dropColumn('exam_id');
        });

        Schema::table('additional_fee_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
            $table->dropColumn('category_id');
        });

        Schema::table('resit_fee_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('tuition_fee_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('announcement_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['label_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('label_id');
        });

        Schema::table('election_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['election_type_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('election_type_id');
            $table->dropColumn('department_id');
        });

        Schema::table('election_winner_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['election_type_id']);
            $table->dropForeign(['election_role_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('election_type_id');
            $table->dropColumn('election_role_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('student_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('progressive_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('teacher_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('class_timetable_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });
        Schema::table('exam_timetable_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('exam_candidates', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn('school_branch_id');
            $table->dropColumn('exam_id');
            $table->dropColumn('student_id');
        });

        Schema::table('department_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('specialty_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('course_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('registration_fee_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });
        Schema::table('additional_fee_trans_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('election_vote_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['election_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
            $table->dropColumn('election_id');
        });

        Schema::table('resit_fee_trans_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('tuition_fee_trans_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('election_application_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
            $table->dropColumn('specialty_id');
            $table->dropColumn('department_id');
        });

        Schema::table('school_admin_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn('stat_type_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('resit_candidates', function (Blueprint $table) {
            $table->dropForeign(['resit_exam_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn('resit_exam_id');
            $table->dropColumn('student_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign(['permission_category_id']);
            $table->dropColumn('permission_category_id');
        });

        Schema::table('audiences', function (Blueprint $table) {
            $table->dropForeign(['school_set_audience_group_id']);
            $table->dropColumn('school_set_audience_group_id');
        });

        Schema::table('school_set_audience_groups', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });

        Schema::table('announcement_categories', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });

        Schema::table('tags', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });

        Schema::table('announcements', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn('category_id');

            $table->dropForeign(['label_id']);
            $table->dropColumn('label_id');

            $table->dropForeign(['tag_id']);
            $table->dropColumn('tag_id');

            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });
        Schema::table('annoucement_author', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropColumn('announcement_id');
        });

        Schema::table('school_announcement_settings', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['announcement_setting_id']);

            $table->dropColumn('school_branch_id');
            $table->dropColumn('announcement_setting_id');
        });
        Schema::table('target_groups', function (Blueprint $table) {
            $table->dropForeign(['school_set_audience_group_id']);
            $table->dropForeign(['announcement_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn('school_set_audience_group_id');
            $table->dropColumn('announcement_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('target_preset_groups', function (Blueprint $table) {
            $table->dropForeign(['preset_group_id']);
            $table->dropForeign(['announcement_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn('preset_group_id');
            $table->dropColumn('announcement_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('target_users', function (Blueprint $table) {
            $table->dropForeign(['announcement_id']);
            $table->dropForeign(['school_branch_id']);

            $table->dropColumn('actorable_type');
            $table->dropColumn('actorable_id');
            $table->dropColumn('announcement_id');
            $table->dropColumn('school_branch_id');
        });
        Schema::table('event_tags', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });

        Schema::table('event_categories', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });

        Schema::table('school_events', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['event_category_id']);
            $table->dropForeign(['tag_id']);

            $table->dropColumn('school_branch_id');
            $table->dropColumn('event_category_id');
            $table->dropColumn('tag_id');
        });
        Schema::table('event_author', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('event_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('school_event_settings', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['event_setting_id']);
            $table->dropColumn('school_branch_id');
            $table->dropColumn('event_setting_id');
        });

        Schema::table('ev_inv_custom_groups', function (Blueprint $table) {
            $table->dropForeign(['school_set_audience_group_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_set_audience_group_id');
            $table->dropColumn('event_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('ev_inv_preset_groups', function (Blueprint $table) {
            $table->dropForeign(['preset_group_id']);
            $table->dropForeign(['event_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('preset_group_id');
            $table->dropColumn('event_id');
            $table->dropColumn('school_branch_id');
        });

        Schema::table('ev_inv_members', function (Blueprint $table) {
            $table->dropForeign(['event_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('event_id');
            $table->dropColumn('school_branch_id');
        });
        Schema::table('roles', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn('school_branch_id');
        });
        Schema::table('fee_schedules', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['school_semester_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['specialty_id', 'level_id', 'school_semester_id', 'school_branch_id']);
        });
        Schema::table('fee_schedule_slots', function (Blueprint $table) {
            $table->dropForeign(['installment_id']);
            $table->dropForeign(['fee_schedule_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['installment_id', 'fee_schedule_id', 'school_branch_id']);
        });

        Schema::table('student_fee_schedule', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['fee_schedule_slot_id']);
            $table->dropForeign(['fee_schedule_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['student_id', 'level_id', 'specialty_id', 'fee_schedule_slot_id', 'fee_schedule_id', 'school_branch_id']);
        });

        Schema::table('school_branch_app_settings', function (Blueprint $table) {
            $table->dropForeign(['app_settings_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['app_settings_id', 'school_branch_id']);
        });

        Schema::table('app_settings', function (Blueprint $table) {
            $table->dropForeign(['setting_category_id']);
            $table->dropColumn(['setting_category_id']);
        });

        Schema::table('school_expenses_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['category_id']);
            $table->dropColumn(['stat_type_id', 'school_branch_id', 'category_id']);
        });

        Schema::table('student_exam_stats', function (Blueprint $table) {
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['stat_type_id', 'school_branch_id', 'specialty_id', 'student_id']);
        });

        // Dropping foreign keys and columns from school_ca_exam_stats
        Schema::table('school_ca_exam_stats', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['school_branch_id', 'stat_type_id', 'exam_id']);
        });

        // Dropping foreign keys and columns from school_exam_stats
        Schema::table('school_exam_stats', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['stat_type_id']);
            $table->dropForeign(['exam_id']);
            $table->dropColumn(['school_branch_id', 'stat_type_id', 'exam_id']);
        });

        // Dropping foreign keys and columns from resit_exams
        Schema::table('resit_exams', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['exam_type_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['reference_exam_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['grades_category_id']);
            $table->dropColumn(['school_branch_id', 'exam_type_id', 'level_id', 'reference_exam_id', 'semester_id', 'specialty_id', 'grades_category_id']);
        });

        // Dropping foreign keys and columns from resit_results
        Schema::table('resit_results', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['resit_exam_id']);
            $table->dropForeign(['failed_exam_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['student_id', 'specialty_id', 'student_batch_id', 'level_id', 'resit_exam_id', 'failed_exam_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from resit_marks
        Schema::table('resit_marks', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['courses_id']);
            $table->dropForeign(['resit_exam_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['school_branch_id', 'student_id', 'courses_id', 'resit_exam_id', 'level_id', 'specialty_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from resit_examtimetable
        Schema::table('resit_examtimetable', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['resit_exam_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['school_branch_id', 'resit_exam_id', 'course_id', 'specialty_id', 'student_batch_id', 'level_id']);
        });

        // Dropping foreign keys and columns from studentbatch_grad_dates
        Schema::table('studentbatch_grad_dates', function ($table) {
            $table->dropForeign(['level_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['level_id', 'specialty_id', 'student_batch_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from student_results
        Schema::table('student_results', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['student_id', 'specialty_id', 'student_batch_id', 'level_id', 'exam_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from school_grades_config
        Schema::table('school_grades_config', function (Blueprint $table) {
            $table->dropForeign(['grades_category_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['grades_category_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from schoolbranch_apikey
        Schema::table('schoolbranch_apikey', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from resit_fee_transactions
        Schema::table('resit_fee_transactions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['resitfee_id']);
            $table->dropColumn(['school_branch_id', 'resitfee_id']);
        });

        // Dropping foreign keys and columns from registration_fee_transactions
        Schema::table('registration_fee_transactions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['registrationfee_id']);
            $table->dropColumn(['school_branch_id', 'registrationfee_id']);
        });

        // Dropping foreign keys and columns from additional_fee_transactions
        Schema::table('additional_fee_transactions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['fee_id']);
            $table->dropColumn(['school_branch_id', 'fee_id']);
        });

        // Dropping foreign keys and columns from tuition_fee_transactions
        Schema::table('tuition_fee_transactions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['tuition_id']);
            $table->dropColumn(['school_branch_id', 'tuition_id']);
        });

        // Dropping foreign keys and columns from additional_fee_category
        Schema::table('additional_fee_category', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from additional_fees
        Schema::table('additional_fees', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['additionalfee_category_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['school_branch_id', 'specialty_id', 'level_id', 'additionalfee_category_id', 'student_id']);
        });

        // Dropping foreign keys and columns from registration_fees
        Schema::table('registration_fees', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['student_id', 'school_branch_id', 'specialty_id', 'level_id']);
        });

        // Dropping foreign keys and columns from fee_waiver
        Schema::table('fee_waiver', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['school_branch_id', 'specialty_id', 'level_id', 'student_id']);
        });

        // Dropping foreign keys and columns from tuition_fees
        Schema::table('tuition_fees', function (Blueprint $table) {
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['student_id', 'school_branch_id', 'specialty_id', 'level_id']);
        });

        // Dropping foreign keys and columns from hos
        Schema::table('hos', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['specialty_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from hod
        Schema::table('hod', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropColumn(['school_branch_id', 'department_id']);
        });

        // Dropping foreign keys and columns from school_semesters
        Schema::table('school_semesters', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['semester_id', 'specialty_id', 'school_branch_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from teacher_specailty_preference
        Schema::table('teacher_specailty_preference', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropColumn(['school_branch_id', 'teacher_id', 'specialty_id']);
        });

        // Dropping foreign keys and columns from election_candidates
        Schema::table('election_candidates', function (Blueprint $table) {
            $table->dropForeign(['application_id']);
            $table->dropForeign(['election_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['election_role_id']);
            $table->dropColumn(['application_id', 'election_id', 'school_branch_id', 'student_id', 'election_role_id']);
        });

        // Dropping foreign keys and columns from elections_results
        Schema::table('elections_results', function (Blueprint $table) {
            $table->dropForeign(['election_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['election_id', 'position_id', 'candidate_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from election_type
        Schema::table('election_type', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from election_participants
        Schema::table('election_participants', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['election_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['specialty_id', 'level_id', 'election_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from current_election_winners
        Schema::table('current_election_winners', function (Blueprint $table) {
            $table->dropForeign(['election_type_id']);
            $table->dropForeign(['election_role_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['election_type_id', 'election_role_id', 'student_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from past_election_winners
        Schema::table('past_election_winners', function (Blueprint $table) {
            $table->dropForeign(['election_type_id']);
            $table->dropForeign(['election_role_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['election_type_id', 'election_role_id', 'student_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from election_votes
        Schema::table('election_votes', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['election_id']);
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['position_id']);
            $table->dropColumn(['school_branch_id', 'election_id', 'candidate_id', 'student_id', 'position_id']);
        });

        // Dropping foreign keys and columns from election_roles
        Schema::table('election_roles', function (Blueprint $table) {
            $table->dropForeign(['election_type_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['election_type_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from elections
        Schema::table('elections', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['election_type_id']);
            $table->dropColumn(['school_branch_id', 'election_type_id']);
        });

        // Dropping foreign keys and columns from election_application
        Schema::table('election_application', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['election_id']);
            $table->dropForeign(['election_role_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['school_branch_id', 'election_id', 'election_role_id', 'student_id']);
        });

        // Dropping foreign keys and columns from payments
        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign(['school_subscription_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_subscription_id', 'school_branch_id']);
        });

        // Dropping foreign keys and columns from school_subscriptions
        Schema::table('school_subscriptions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['rate_card_id']);
            $table->dropColumn(['school_branch_id', 'rate_card_id']);
        });

        // Dropping foreign keys and columns from student_resit
        Schema::table('student_resit', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['school_branch_id', 'specialty_id', 'course_id', 'exam_id', 'level_id', 'student_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from student_batch
        Schema::table('student_batch', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from school_expenses_category
        Schema::table('school_expenses_category', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from school_expenses
        Schema::table('school_expenses', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['expenses_category_id']);
            $table->dropColumn(['school_branch_id', 'expenses_category_id']);
        });

        // Dropping foreign keys and columns from exam_type
        Schema::table('exam_type', function (Blueprint $table) {
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['semester_id']);
        });

        // Dropping foreign keys and columns from examtimetable
        Schema::table('examtimetable', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['school_branch_id', 'exam_id', 'course_id', 'specialty_id', 'level_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from grades
        Schema::table('grades', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['letter_grade_id']);
            $table->dropForeign(['grades_category_id']);
            $table->dropColumn(['school_branch_id', 'letter_grade_id', 'grades_category_id']);
        });

        // Dropping foreign keys and columns from teacher_availabilities
        Schema::table('teacher_availabilities', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['school_semester_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropColumn(['school_branch_id', 'level_id', 'school_semester_id', 'teacher_id', 'specialty_id']);
        });

        // Dropping foreign keys and columns from teacher_availability_slots
        Schema::table('teacher_availability_slots', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['school_semester_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['teacher_availability_id']);
            $table->dropColumn(['school_branch_id', 'level_id', 'school_semester_id', 'teacher_id', 'specialty_id', 'teacher_availability_id']);
        });

        // Dropping foreign keys and columns from timetables
        Schema::table('timetables', function (Blueprint $table) {
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['course_id']);
            $table->dropForeign(['teacher_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['specialty_id', 'course_id', 'teacher_id', 'semester_id', 'level_id', 'school_branch_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from student
        Schema::table('student', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['guardian_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['school_branch_id', 'specialty_id', 'department_id', 'level_id', 'guardian_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from courses
        Schema::table('courses', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['semester_id']);
            $table->dropColumn(['school_branch_id', 'specialty_id', 'department_id', 'level_id', 'semester_id']);
        });

        // Dropping foreign keys and columns from school_branches
        Schema::table('school_branches', function (Blueprint $table) {
            $table->dropForeign(['school_id']);
            $table->dropColumn(['school_id']);
        });

        // Dropping foreign keys and columns from parents
        Schema::table('parents', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from fee_payment_transactions
        Schema::table('fee_payment_transactions', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_id']);
            $table->dropColumn(['school_branch_id', 'student_id']);
        });

        // Dropping foreign keys and columns from exams
        Schema::table('exams', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['exam_type_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['semester_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropForeign(['grades_category_id']);
            $table->dropColumn(['school_branch_id', 'exam_type_id', 'level_id', 'semester_id', 'specialty_id', 'student_batch_id', 'grades_category_id', 'grading_added']);
        });

        // Dropping foreign keys and columns from marks
        Schema::table('marks', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['student_id']);
            $table->dropForeign(['courses_id']);
            $table->dropForeign(['exam_id']);
            $table->dropForeign(['level_id']);
            $table->dropForeign(['specialty_id']);
            $table->dropForeign(['student_batch_id']);
            $table->dropColumn(['school_branch_id', 'student_id', 'courses_id', 'exam_id', 'level_id', 'specialty_id', 'student_batch_id']);
        });

        // Dropping foreign keys and columns from specialty
        Schema::table('specialty', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropForeign(['department_id']);
            $table->dropForeign(['level_id']);
            $table->dropColumn(['school_branch_id', 'department_id', 'level_id']);
        });

        // Dropping foreign keys and columns from department
        Schema::table('department', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from teacher
        Schema::table('teacher', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from school_admin
        Schema::table('school_admin', function (Blueprint $table) {
            $table->dropForeign(['school_branch_id']);
            $table->dropColumn(['school_branch_id']);
        });

        // Dropping foreign keys and columns from schools
        Schema::table('schools', function (Blueprint $table) {
            $table->dropForeign(['country_id']);
            $table->dropColumn(['country_id']);
        });
    }
};
