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
        // Create the 'categories' table
        Schema::create('categories', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->timestamps();
        });

        // Create the 'tags' table
        Schema::create('tags', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('labels', function (Blueprint $table){
            $table->string('id')->primary();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create the 'announcements' table
        Schema::create('announcements', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('content');
            $table->enum('status', ['draft', 'scheduled', 'active', 'expired'])->default('draft');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('cascade');
            $table->timestamp('published_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });

        Schema::create('annoucement_author', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('authorable_id')->nullable();
            $table->string('authorable_type')->nullable();
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
        });

        Schema::create('announcement_tag', function (Blueprint $table) {
            $table->foreignId('announcement_id')->constrained('announcements')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->primary(['announcement_id', 'tag_id']);
        });

        Schema::create('announcement_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('school_announcement_settings', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('school_branch_id');
            $table->foreign('school_branch_id')->references('id')->on('school_branches')->onDelete('cascade');
            $table->foreignId('announcement_settings_id')->constrained('announcement_settings')->onDelete('cascade');
            $table->string('value')->nullable();
            $table->boolean('enabled')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement');
    }
};
