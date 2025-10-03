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
        // Create the 'announcement_categories' table
        Schema::create('announcement_categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        // Create the 'tags' table
        Schema::create('tags', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->boolean('status')->default(true);
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->string('icon');
            $table->json('color');
            $table->timestamps();
        });

        Schema::create('announcements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('notification_sent_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('tags');
            $table->json('audience');
            $table->timestamps();
        });

        Schema::create('annoucement_authors', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->timestamps();
        });

        Schema::create('announcement_engagement_stats', function(Blueprint $table) {
           $table->string('id')->primary();
           $table->integer('total_reciepient')->default(0);
           $table->integer('total_student')->default(0);
           $table->integer('total_school_admin')->default(0);
           $table->integer('total_teacher')->default(0);
           $table->integer('total_seen')->default(0);
           $table->integer('total_unseen')->default(0);
           $table->timestamps();
        });


        Schema::create('student_announcements', function(Blueprint $table){
           $table->string('id')->primary();
           $table->timestamp('seen_at')->nullable();
           $table->enum('status', ['unseen', 'seen'])->default('unseen');
           $table->timestamps();
        });

        Schema::create('teacher_announcements', function(Blueprint $table) {
           $table->string('id')->primary();
           $table->timestamp('seen_at')->nullable();
           $table->enum('status', ['unseen', 'seen'])->default('unseen');
           $table->timestamps();
        });

        Schema::create('school_admin_announcements', function(Blueprint $table){
           $table->string('id')->primary();
           $table->timestamp('seen_at')->nullable();
           $table->enum('status', ['unseen', 'seen'])->default('unseen');
           $table->timestamps();
        });




    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('annoucement_author');
        Schema::dropIfExists('announcements');
        Schema::dropIfExists('labels');
        Schema::dropIfExists('tags');
        Schema::dropIfExists('announcement_categories');
    }
};
